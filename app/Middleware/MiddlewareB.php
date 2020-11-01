<?php

namespace App\Middleware;


use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MiddlewareB implements MiddlewareInterface
{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        var_dump(__CLASS__);
        $response = $handler->handle($request);
        return $response
            ->withBody(
                new SwooleStream($response->getBody()->getContents() . '--')
            );
    }
}