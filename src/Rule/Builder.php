<?php


namespace vivace\router\Rule;


use Psr\Http\Message\ServerRequestInterface;
use vivace\router\Exception\NotApplied;
use vivace\router\Rule\Path\Equal;
use vivace\router\Rule\Path\Match;

class Builder extends AllOf
{
    private $path;
    private $method;
    private $host;

    public function path(string $path): Builder
    {
        $this->path = [Equal::class, $path];
        return $this;
    }

    public function match(string $expression): Builder
    {
        $this->path = [Match::class, $expression];
        return $this;
    }

    public function method(string ...$method): Builder
    {
        $this->method = $method;
        return $this;
    }

    public function host(string $host): Builder
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     * @throws NotApplied
     */
    public function apply(ServerRequestInterface $request): ServerRequestInterface
    {
        $this->rules = [];
        if ($this->host) {
            $this->rules[] = new Host($this->host);
        }
        if ($this->method) {
            $methods = [];
            foreach ($this->method as $item) {
                $methods[] = new Method($item);
            }
            $this->rules[] = new OneOf(...$methods);
        }
        if ($this->path) {
            [$className, $value] = $this->path;
            $this->rules[] = new $className($value);
        }
        return parent::apply($request);

    }
}