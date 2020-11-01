<?php

namespace Rebuild\Dispatcher;


use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class AbstractRequestHandler implements RequestHandlerInterface
{

    /**
     * @var \Psr\Http\Server\MiddlewareInterface
     */
    protected $coreHandler;

    protected $middlewares = [];

    protected $offset = 0;

    /**
     * @param array $middlewares
     */
    public function __construct(array $middlewares, MiddlewareInterface $coreHandler)
    {
        $this->middlewares = $middlewares;
        $this->coreHandler = $coreHandler;
    }

    protected function handleRequest($request)
    {
        if (! isset($this->middlewares[$this->offset]) && ! empty($this->coreHandler)) {
            $handler = $this->coreHandler;
        } else {
            $handler = $this->middlewares[$this->offset];
            // todo
            is_string($handler) && $handler = new $handler();
        }
        if (! method_exists($handler, 'process')) {
            throw new InvalidArgumentException(sprintf('Invalid middleware, it has to provide a process() method.'));
        }
        return $handler->process($request, $this->next());
    }

    /**
     * @return $this
     */
    protected function next()
    {
        ++$this->offset;
        return $this;
    }
}