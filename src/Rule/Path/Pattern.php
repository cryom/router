<?php


namespace vivace\router\Rule\Path;


use Psr\Http\Message\ServerRequestInterface;
use vivace\router\Exception\NotApplied;
use vivace\router\Rule\Path;

class Pattern implements Path
{
    /**
     * @var string
     */
    private $pattern;

    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     * @throws NotApplied
     */
    public function apply(ServerRequestInterface $request): ServerRequestInterface
    {
        $args = [];
        $names = [];
        $i = 0;
        $pattern = preg_replace_callback('~:([^\/]+)~', function ($matches) use (&$i, &$args, &$names) {
            $marker = "\t\t$i\t\t";
            $args[$marker] = '([^\/]+)';
            $names[] = $matches[1];
            $i++;
            return $marker;
        }, $this->pattern);
        $pattern = preg_quote($pattern, '~');
        $pattern = strtr($pattern, $args);

        if (preg_match("~^$pattern$~", $request->getUri()->getPath(), $matches)) {
            array_shift($matches);
            $vars = array_combine($names, $matches);
            foreach ($vars as $n => $v) {
                $request = $request->withAttribute($n, $v);
            }
            return $request;
        }
    }
}