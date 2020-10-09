<?php

namespace Rebuild\HttpServer;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Hyperf\Utils\Context;
use Hyperf\Utils\Str;
use Psr\Http\Message\ServerRequestInterface;
use Rebuild\HttpServer\Router\DispatherFactory;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Hyperf\HttpMessage\Server\Request as Psr7Request;
use function FastRoute\simpleDispatcher;


class Server
{

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var \Rebuild\HttpServer\Contract\CoreMiddlewareInterface
     */
    protected $coreMiddleware;

    public function __construct(DispatherFactory $dispatcherFactory)
    {
        $this->dispatcher = $dispatcherFactory->getDispathcer('http');
        $this->coreMiddleware = new CoreMiddleware($dispatcherFactory);
    }

    public function onRequest(SwooleRequest $request, SwooleResponse $response): void
    {
        /** @var \Psr\Http\Message\RequestInterface $psr7Request */
        /** @var \Psr\Http\Message\ResponseInterface $psr7Response */
        [$psr7Request, $psr7Response] = $this->initRequestAndResponse($request, $response);

        $httpMethod = $psr7Request->getMethod();
        $uri = $psr7Request->getUri()->getPath();

        $psr7Request = $this->coreMiddleware->dispatch($psr7Request);

        $middlewareA = function ($value, $middleware) {
            $value = $value + 1;
            return $middleware($value, function ($value) {
                $value = $value + 2;
                return $value;
            });
        };
        $value = 1;
        $value = $middlewareA($value, function ($value, $middleware) {
            $value = $value + 2;
            // 执行下一个匿名函数之前
            $response = $middleware($value);
            $response = $response->withBody();
            return $response;
        });


        $response->end($value);
    }

    protected function initRequestAndResponse(SwooleRequest $request, SwooleResponse $response): array
    {
        // Initialize PSR-7 Request and Response objects.
        Context::set(ResponseInterface::class, $psr7Response = new Psr7Response());
        Context::set(ServerRequestInterface::class, $psr7Request = Psr7Request::loadFromSwooleRequest($request));
        return [$psr7Request, $psr7Response];
    }

}