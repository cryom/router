<?php
/**
 * Created by PhpStorm.
 * User: bert
 * Date: 31.03.17
 * Time: 13:50
 */

namespace vivace\router;


use Psr\Http\Message\ServerRequestInterface;
use vivace\router\Exception\NotApplied;

/**
 * Interface Rule
 * @package vivace\router
 */
interface Rule
{
    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     * @throws NotApplied
     */
    public function apply(ServerRequestInterface $request): ServerRequestInterface;


}