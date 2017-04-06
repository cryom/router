<?php


namespace vivace\router\Rule\Method;


use Psr\Http\Message\ServerRequestInterface;
use vivace\router\Exception\NotApplied;
use vivace\router\Rule\Method;

class Equal implements Method
{
    /**
     * @var \string[]
     */
    private $method;

    public function __construct(string $method)
    {
        $this->method = $method;
    }

    public function apply(ServerRequestInterface $request): ServerRequestInterface
    {
        if (mb_strtoupper($request->getMethod()) !== mb_strtoupper($this->method)) {
            throw new NotApplied("request method not equal $this->method");
        }
        return $request;
    }
}