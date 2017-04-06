<?php


namespace vivace\router\Rule\Host;


use Psr\Http\Message\ServerRequestInterface;
use vivace\router\Exception\NotApplied;
use vivace\router\Rule;
use vivace\router\Rule\Host;

class Pattern implements Host
{
    /**
     * @var string
     */
    private $pattern;

    /**
     *
     * @param string $pattern
     * examples
     * *.site.com ~ my.site.com, other.site.com
     * {app}.site.com ~ my.site.com, other.site.com with capture as request attribute
     */
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
        $pattern = explode('.', $this->pattern);
        $host = explode('.', $request->getUri()->getHost());
        if (count($pattern) !== count($host)) {
            throw new NotApplied('Mot applied');
        }
        foreach ($host as $pos => $value) {
            $act = $pattern[$pos];
            if ($act === '*') {
                continue;
            } elseif ($act[0] == '{' && mb_substr($act, -1, 1) == '}') {
                $request = $request->withAttribute(mb_substr($act, 1, - 1), $value);
            } elseif ($act !== $value) {
                throw new NotApplied('Not applied');
            }
        }
        return $request;
    }
}