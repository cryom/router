<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 15.05.17
 * Time: 1:21
 */

namespace vivace\router;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Nested
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response);
}