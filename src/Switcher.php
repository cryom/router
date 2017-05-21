<?php

namespace vivace\router;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use vivace\router\Exception\NotFound;

class Switcher implements Nested
{
    protected const VERSION = 1;

    protected const NODE_T_METHOD = 1;
    protected const NODE_T_HOST = 1 << 1;
    protected const NODE_T_PATH = 1 << 2;

    protected const NODE_PROP_META = 0;
    protected const NODE_PROP_META_MASK = 0;
    protected const NODE_PROP_META_NESTED = 1;

    protected const NODE_PROP_MAP = 1;
    protected const NODE_PROP_HANDLER_ID = 2;
    protected const NODE_PROP_CAPTURE_NAMES = 3;
    protected const NODE_PROP_CAPTURE_VALUES = 4;
    protected const NODE_PROP_PARTS = 5;

    protected const NODE_SPEC_ANY = '*';
    protected const NODE_SPEC_NESTED = "&";
    protected const NODE_SPEC_EXPR = "~";

    protected const NODE_TOKEN_PREFIX_METHOD = '|';
    protected const NODE_TOKEN_PREFIX_HOST = '.';
    protected const NODE_TOKEN_PREFIX_PATH = '/';

    protected const PATTERN_PREFIX_CAPTURE = ':';
    protected const PATTERN_PATH_SEPARATOR = '/';
    protected const PATTERN_HOST_SEPARATOR = '.';


    private $routes = [];
    private $tree;
    private $default;

    public function __construct(iterable $cases = null)
    {
        if ($cases) {
            foreach ($cases as $pattern => $handler) {
                $this->case($pattern, $handler);
            }
        }

    }

    /**
     * Add route handler
     * @param string $pattern
     * @param callable $handler
     * @return $this
     */
    public function case(string $pattern, callable $handler)
    {
        $this->tree = null;
        $this->routes[] = [$pattern, $handler];
        return $this;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->switch($request, $response);
    }

    public function switch(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $method = $request->getMethod();
        $host = $request->getUri()->getHost();
        $path = $request->getUri()->getPath();
        $parts = static::extractParts($method, $host, $path);

        [$tree, $handlers] = $this->getTree();

        if (!$matches = static::find($tree, $parts)) {
            if (!$this->default) {
                throw new NotFound("{$request->getMethod()}//{$request->getUri()->getHost()}{$request->getUri()->getPath()}");
            }
            $handler = $this->default;
        } else {
            if ($matches[static::NODE_PROP_CAPTURE_NAMES]) {
                foreach ($matches[static::NODE_PROP_CAPTURE_NAMES] as $name => $pos) {
                    $request->withAttribute($name, $matches[static::NODE_PROP_CAPTURE_VALUES][$pos]);
                }
            }

            $handler = $handlers[$matches[static::NODE_PROP_HANDLER_ID]];
        }
        if ($matches[static::NODE_PROP_META][static::NODE_PROP_META_NESTED]) {
            $parts = $matches[static::NODE_PROP_PARTS];
            $request = $request->withUri($request->getUri()->withPath('/'));

            foreach ($parts as [$type, $keys]) {
                switch ($type) {
                    case static::NODE_T_PATH:
                        $request = $request->withUri($request->getUri()->withPath(implode('', $keys)));
                        break(2);
                    case static::NODE_T_HOST:
                        $request = $request->withUri($request->getUri()->withHost(ltrim(implode('', $keys), '.')));
                        break;
                }
            }
        }
        $result = call_user_func($handler, $request, $response);

        if (!$result instanceof ResponseInterface) {
            $response->getBody()->write($result);
            $result = $response;
        }

        return $result;
    }

    protected static function extractParts(?string $method, ?string $host, ?string $path): array
    {
        $path = trim($path, '/');
        $parts = [];
        if ($method) {
            $parts[] = [static::NODE_T_METHOD, [static::NODE_TOKEN_PREFIX_METHOD . $method]];
        }
        if ($host) {
            $parts[] = [
                static::NODE_T_HOST,
                array_map(
                    function ($item) {
                        return static::NODE_TOKEN_PREFIX_HOST . $item;
                    },
                    explode(static::PATTERN_HOST_SEPARATOR, $host)
                )
            ];
        }

        if ($path) {
            $parts[] = [static::NODE_T_PATH, array_map(function ($item) {
                return static::NODE_TOKEN_PREFIX_PATH . $item;
            }, explode(static::PATTERN_PATH_SEPARATOR, $path))];
        } else {
            $parts[] = [static::NODE_T_PATH, ['/']];
        }

        return $parts;
    }

    public function getTree()
    {
        return $this->tree ?? $this->tree = static::build($this->routes);
    }

    protected static function build(array $routes)
    {
        $tree = [];
        $handlers = [];
        foreach ($routes as [$pattern, $handler]) {
            $captureNames = [];
            $captureOffset = 0;
            $current =& $tree;


            $parts = static::extractParts(...static::parsePattern($pattern));

            foreach ($parts as [$mask, $units]) {
                foreach ($units as $unit) {
                    if ($unit[1] == static::PATTERN_PREFIX_CAPTURE) {
                        $captureNames[substr($unit, 2)] = $captureOffset++;
                        $unit = $unit[0] . static::NODE_SPEC_ANY;
                    } elseif ($unit[1] == static::NODE_SPEC_ANY) {
                        $captureOffset++;
                    }
                    if (!($current[static::NODE_PROP_META][static::NODE_PROP_META_MASK] & $mask)) {
                        $current[static::NODE_PROP_META][static::NODE_PROP_META_MASK] += $mask;
                    }
                    if (!isset($current[static::NODE_PROP_MAP][$unit])) {
                        $current[static::NODE_PROP_MAP][$unit] = [
                            static::NODE_PROP_META => [static::NODE_PROP_META_MASK => 0]
                        ];
                    }

                    $current =& $current[static::NODE_PROP_MAP][$unit];
                }
            }
            $handlers[] = $handler;
            $current = [
                static::NODE_PROP_META => [
                    static::NODE_PROP_META_MASK => 0,
                    static::NODE_PROP_META_NESTED => $handler instanceof Nested,
                ],
                static::NODE_PROP_MAP => [],
                static::NODE_PROP_HANDLER_ID => count($handlers) - 1,
                static::NODE_PROP_CAPTURE_NAMES => $captureNames,
            ];
        }
        return [$tree, $handlers];
    }

    protected static function parsePattern(string $pattern): array
    {
        $method = $host = $path = null;
        if (($pos = strpos($pattern, '//')) !== false) {
            $method = $pos ? mb_substr($pattern, 0, $pos) : null;
            $host = mb_substr($pattern, $pos + 2);
            [$host, $path] = explode('/', $host, 2) + [1 => null];
        } elseif (($pos = strpos($pattern, '/')) !== false) {
            $method = $pos ? mb_substr($pattern, 0, $pos) : null;
            $host = null;
            $path = mb_substr($pattern, $pos + 1);
        }
        $method = trim($method) ?: null;
        $host = trim($host) ?: null;
        $path = trim($path, '/ ') ?: null;

        return [$method, $host, $path];
    }

    protected static function find(array $tree, array $parts): ?array
    {
        $matches = null;
        $typeMask = $tree[static::NODE_PROP_META][static::NODE_PROP_META_MASK];
        while ($part = array_shift($parts)) {
            $partType = $part[0];
            $keys =& $part[1];
            if (!($partType & $typeMask)) {
                continue;
            }
            while ($key = array_shift($keys)) {
                $subParts = $keys ? array_merge([[$partType, $keys]], $parts) : $parts;
                $tokenPrefix = $key[0];
                if (isset($tree[static::NODE_PROP_MAP][$key])) {
                    if (
                        $subParts
                        && (
                            !isset($tree[static::NODE_PROP_MAP][$key][static::NODE_PROP_META][static::NODE_PROP_META_NESTED])
                            ||
                            !$tree[static::NODE_PROP_MAP][$key][static::NODE_PROP_META][static::NODE_PROP_META_NESTED]
                        )
                    ) {
                        $matches = static::find($tree[static::NODE_PROP_MAP][$key], $subParts);
                    } else {
                        $matches = $tree[static::NODE_PROP_MAP][$key];
                    }
                    if ($matches) {
                        $matches[static::NODE_PROP_PARTS] = $subParts;
                        break(2);
                    }
                }
                $anyToken = $tokenPrefix . static::NODE_SPEC_ANY;
                if (isset($tree[static::NODE_PROP_MAP][$anyToken])) {
                    if (
                        $subParts
                        && (
                            !isset($tree[static::NODE_PROP_MAP][$anyToken][static::NODE_PROP_META][static::NODE_PROP_META_NESTED])
                            ||
                            !$tree[static::NODE_PROP_MAP][$anyToken][static::NODE_PROP_META][static::NODE_PROP_META_NESTED]
                        )
                    ) {
                        $matches = static::find($tree[static::NODE_PROP_MAP][$anyToken], $subParts);
                    } else {
                        $matches = $tree[static::NODE_PROP_MAP][$anyToken];
                    }
                    if ($matches) {
                        if (!isset($matches[static::NODE_PROP_CAPTURE_VALUES])) {
                            $matches[static::NODE_PROP_CAPTURE_VALUES] = [];
                        }
                        array_unshift($matches[static::NODE_PROP_CAPTURE_VALUES], substr($key, 1));
                        $matches[static::NODE_PROP_PARTS] = $subParts;
                        break(2);
                    }
                }
            }
        };

        return $matches;
    }

    /**
     * Default handler in case the route is not found
     * @param callable $handler
     * @return $this
     */
    public function default(callable $handler)
    {
        $this->default = $handler;
        return $this;
    }
}