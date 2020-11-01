<?php

namespace Rebuild\HttpServer;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Hyperf\Utils\Context;
use Hyperf\Utils\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rebuild\Config\ConfigFactory;
use Rebuild\Dispatcher\HttpRequestHandler;
use Rebuild\HttpServer\Router\Dispatched;
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
    protected $globalMiddlewares;

    /**
     * @var DispatherFactory
     */
    protected $dispatcherFactory;

    public function __construct(DispatherFactory $dispatcherFactory)
    {
        $this->dispatcherFactory = $dispatcherFactory;
        $this->dispatcher = $this->dispatcherFactory->getDispathcer('http');
    }

    public function initCoreMiddleware()
    {
        $config = (new ConfigFactory())();
        $this->globalMiddlewares = $config->get('middlewares');
        $this->coreMiddleware = new CoreMiddleware($this->dispatcherFactory);
    }

    public function onRequest(SwooleRequest $request, SwooleResponse $response): void
    {
        /** @var \Psr\Http\Message\RequestInterface $psr7Request */
        /** @var \Psr\Http\Message\ResponseInterface $psr7Response */
        [$psr7Request, $psr7Response] = $this->initRequestAndResponse($request, $response);

        $psr7Request = $this->coreMiddleware->dispatch($psr7Request);

        $httpMethod = $psr7Request->getMethod();
        $path = $psr7Request->getUri()->getPath();

        $middlewares = $this->globalMiddlewares ?? [];

        $dispatched = $psr7Request->getAttribute(Dispatched::class);
        if ($dispatched instanceof Dispatched && $dispatched->isFound()) {
            $registeredMiddlewares = MiddlewareManager::get($path, $httpMethod) ?? [];
            $middlewares = array_merge($middlewares, $registeredMiddlewares);
        }

        $requestHandler = new HttpRequestHandler($middlewares, $this->coreMiddleware);
        $psr7Response = $requestHandler->handle($psr7Request);

        /*
         * Headers
         */
        foreach ($psr7Response->getHeaders() as $key => $value) {
            $response->header($key, implode(';', $value));
        }

        /*
         * Status code
         */
        $response->status($psr7Response->getStatusCode());
        $response->end($psr7Response->getBody()->getContents());
        var_dump('response end');
    }

    protected function initRequestAndResponse(SwooleRequest $request, SwooleResponse $response): array
    {
        // Initialize PSR-7 Request and Response objects.
        Context::set(ResponseInterface::class, $psr7Response = new Psr7Response());
        Context::set(ServerRequestInterface::class, $psr7Request = Psr7Request::loadFromSwooleRequest($request));
        return [$psr7Request, $psr7Response];
    }

}