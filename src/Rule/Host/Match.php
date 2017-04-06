<?php


namespace vivace\router\Rule\Host;


use Psr\Http\Message\ServerRequestInterface;
use vivace\router\Exception\NotApplied;
use vivace\router\Rule;
use vivace\router\Rule\Host;

class Match implements Host
{
    /**
     * @var string
     */
    private $expression;

    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     * @throws NotApplied
     */
    public function apply(ServerRequestInterface $request): ServerRequestInterface
    {
        if (!preg_match("#$this->expression#", $request->getUri()->getHost())) {
            throw new NotApplied();
        }

        return $request;
    }
}