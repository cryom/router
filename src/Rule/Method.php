<?php
/**
 * Created by PhpStorm.
 * User: bert
 * Date: 31.03.17
 * Time: 14:14
 */

namespace vivace\router\Rule;


use Psr\Http\Message\ServerRequestInterface;
use vivace\router\Exception\NotApplied;
use vivace\router\Rule;

class Method implements Rule
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