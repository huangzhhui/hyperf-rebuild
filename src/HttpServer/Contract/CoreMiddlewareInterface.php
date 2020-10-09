<?php


namespace Rebuild\HttpServer\Contract;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

interface CoreMiddlewareInterface
{
    public function dispatch(ServerRequestInterface $request): ServerRequestInterface;
}