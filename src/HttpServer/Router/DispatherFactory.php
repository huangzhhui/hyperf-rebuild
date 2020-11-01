<?php

namespace Rebuild\HttpServer\Router;


use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Hyperf\HttpServer\Router\Router;
use Rebuild\HttpServer\MiddlewareManager;
use function FastRoute\simpleDispatcher;

class DispatherFactory
{

    /**
     * @var string[]
     */
    protected $routeFiles = [BASE_PATH . '/config/routes.php'];

    /**
     * @var Dispatcher[]
     */
    protected $dispatchers = [];

    /**
     * @var array
     */
    protected $routes = [];

    public function __construct()
    {
        $this->initConfigRoute();
    }

    public function getDispathcer(string $serverName): Dispatcher
    {
        if (! isset($this->dispatchers[$serverName])) {
            $this->dispatchers[$serverName] = simpleDispatcher(function (RouteCollector $r) {
                foreach ($this->routes as $route) {
                    [$httpMethod, $path, $handler] = $route;
                    if (isset($route[3])) {
                        $options = $route[3];
                    }
                    $r->addRoute($httpMethod, $path, $handler);
                    if (isset($options['middlewares']) && is_array($options['middlewares'])) {
                        MiddlewareManager::addMiddlewares($path, $httpMethod, $options['middlewares']);
                    }
                }
            });
        }
        return $this->dispatchers[$serverName];
    }

    public function initConfigRoute()
    {
        foreach ($this->routeFiles as $file) {
            if (file_exists($file)) {
                $routes = require_once $file;
                $this->routes = array_merge_recursive($this->routes, $routes);
            }
        }
    }

}