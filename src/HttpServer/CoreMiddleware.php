<?php

namespace Rebuild\HttpServer;


use FastRoute\Dispatcher;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\Contracts\Jsonable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
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

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $dispatched = $request->getAttribute(Dispatched::class);
        if (! $dispatched instanceof Dispatched) {
            throw new \InvalidArgumentException('Route not found');
        }
        switch ($dispatched->status) {
            case Dispatcher::NOT_FOUND:
                $response = $this->handleNotFound($request);
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $response = $this->handleMethodNotAllow($request);
                break;
            case Dispatcher::FOUND:
                $response = $this->handleFound($request, $dispatched);
                break;
        }
        if (! $response instanceof ResponseInterface) {
            $response = $this->transferToResponse($response);
        }
        return $response;
    }

    protected function handleNotFound(ServerRequestInterface $request): ResponseInterface
    {
        /** @var ResponseInterface $response */
        return $response->withStatus(404)->withBody(new SwooleStream('Not found'));
    }

    protected function handleMethodNotAllow(ServerRequestInterface $request)
    {
        /** @var ResponseInterface $response */
        return $response->withStatus(405)->withBody(new SwooleStream('Method not allow'));
    }

    protected function handleFound(ServerRequestInterface $request, Dispatched $dispatched)
    {
        [$controller, $action] = $dispatched->handler;
        if (! class_exists($controller)) {
            throw new \InvalidArgumentException('Controller not exist');
        }
        if (! method_exists($controller, $action)) {
            throw new \InvalidArgumentException('Action of Controller not exist');
        }
        $parameters = [];
        $controllerInstance = new $controller();
        return $controllerInstance->{$action}(...$parameters);
    }

    protected function transferToResponse($response): ResponseInterface
    {
        if (is_string($response)) {
            return $this->response()
                ->withAddedHeader('Content-Type', 'text/plain')
                ->withBody(new SwooleStream((string) $response));
        } elseif (is_array($response) || $response instanceof Arrayable) {
            return $this->response()
                ->withAddedHeader('Content-Type', 'application/json')
                ->withBody(new SwooleStream(Json::encode($response)));
        } elseif ($response instanceof Jsonable) {
            return $this->response()
                ->withAddedHeader('Content-Type', 'application/json')
                ->withBody(new SwooleStream((string) $response));
        }
        return $response;
    }

    protected function response(): ResponseInterface
    {
        return Context::get(ResponseInterface::class);
    }
}