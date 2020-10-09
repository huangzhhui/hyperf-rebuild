<?php

namespace Rebuild\HttpServer;


use FastRoute\Dispatcher;
use Rebuild\HttpServer\Router\DispatherFactory;
use Hyperf\Utils\Context;
use Psr\Http\Message\ServerRequestInterface;
use Rebuild\HttpServer\Contract\CoreMiddlewareInterface;
use Rebuild\HttpServer\Router\Dispatched;

class CoreMiddleware implements CoreMiddlewareInterface
{

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    public function __construct(DispatherFactory $dispatcherFactory)
    {
        $this->dispatcher = $dispatcherFactory->getDispathcer('http');
    }

    public function dispatch(ServerRequestInterface $request): ServerRequestInterface
    {
        $httpMethod = $request->getMethod();
        $uri = $request->getUri()->getPath();

        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);
        $dispatched = new Dispatched($routeInfo);

        $request = Context::set(ServerRequestInterface::class, $request->withAttribute(Dispatched::class, $dispatched));
        return $request;
    }

}