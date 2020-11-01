<?php

namespace Rebuild\HttpServer;


class MiddlewareManager
{

    protected static $middlewares = [];

    public static function addMiddlewares(string $path, string $method, array $middlewares)
    {
        $method = strtoupper($method);
        foreach ($middlewares as $middleware) {
            static::$middlewares[$path][$method][] = $middleware;
        }
    }

    public static function get(string $path, string $method): array
    {
        return static::$middlewares[$path][$method] ?? [];
    }

}