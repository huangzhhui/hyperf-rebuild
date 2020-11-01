<?php

namespace Rebuild\Dispatcher;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpRequestHandler extends AbstractRequestHandler
{

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->handleRequest($request);
    }
}