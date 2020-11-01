<?php

namespace App\Middleware;


use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MiddlewareA implements MiddlewareInterface
{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        defer(function () {
            var_dump('defer');
        });
        var_dump(__CLASS__);
        $path = $request->getUri()->getPath();
        if ($path === '/hello/hyperf') {
            return Context::get(ResponseInterface::class)->withStatus(401)->withBody(new SwooleStream('Not allow'));
        }
        // $handler->handle() 其实是 MiddlewareB
        $response = $handler->handle($request);
        return $response
            ->withBody(
                new SwooleStream($response->getBody()->getContents() . '++')
            );
    }
}