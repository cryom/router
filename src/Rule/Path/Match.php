<?php
/**
 * Created by PhpStorm.
 * User: bert
 * Date: 31.03.17
 * Time: 14:00
 */

namespace vivace\router\Rule\Path;


use Psr\Http\Message\ServerRequestInterface;
use vivace\router\Exception\NotApplied;
use vivace\router\Rule;

class Match implements Rule
{
    /**
     * @var string
     */
    private $expression;

    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }

    public function apply(ServerRequestInterface $request): ServerRequestInterface
    {
        $valid = preg_match("#$this->expression#", $request->getRequestTarget(), $matches);
        if (false === $valid) {
            throw new NotApplied("Request path not matched $this->expression");
        }
        array_shift($matches);
        foreach ($matches as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }
        return $request;
    }
}