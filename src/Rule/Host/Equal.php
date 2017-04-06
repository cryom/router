<?php


namespace vivace\router\Rule\Host;


use Psr\Http\Message\ServerRequestInterface;
use vivace\router\Exception\NotApplied;
use vivace\router\Rule\Host;

class Equal implements Host
{
    /**
     * @var string
     */
    private $host;

    public function __construct(string $host)
    {

        $this->host = $host;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     * @throws NotApplied
     */
    public function apply(ServerRequestInterface $request): ServerRequestInterface
    {
        if ($request->getUri()->getHost() !== $this->host) {
            throw new NotApplied();
        }
        return $request;
    }
}