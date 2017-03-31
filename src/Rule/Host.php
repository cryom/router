<?php


namespace vivace\router\Rule;


use Psr\Http\Message\ServerRequestInterface;
use vivace\router\Exception\NotApplied;
use vivace\router\Rule;

class Host implements Rule
{

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     * @throws NotApplied
     */
    public function apply(ServerRequestInterface $request): ServerRequestInterface
    {
        // TODO: Implement apply() method.
    }
}