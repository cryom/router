<?php
/**
 * Created by PhpStorm.
 * User: bert
 * Date: 31.03.17
 * Time: 13:55
 */

namespace vivace\router\Rule\Path;


use Psr\Http\Message\ServerRequestInterface;
use vivace\router\Exception\NotApplied;
use vivace\router\Rule;

class Equal implements Rule
{
    /**
     * @var string
     */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function apply(ServerRequestInterface $request): ServerRequestInterface
    {
        if ($request->getRequestTarget() !== $this->path) {
            throw new NotApplied("{$this->path} not equal");
        }
        return $request;
    }
}