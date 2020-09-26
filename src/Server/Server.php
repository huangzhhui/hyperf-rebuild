<?php

namespace Rebuild\Server;


use Swoole\Coroutine\Server as SwooleCoServer;
use Swoole\Server as SwooleServer;
use Swoole\Http\Server as SwooelHttpServer;

class Server implements ServerInterface
{

    /**
     * @var SwooleServer
     */
    protected $server;

    /**
     * @var array
     */
    protected $onRequestCallbacks = [];

    public function init(array $config): ServerInterface
    {
        foreach ($config['servers'] as $server) {
            $this->server = new SwooelHttpServer($server['host'], $server['port'], $server['type'], $server['sock_type']);
            $this->registerSwooleEvents($server['callbacks']);

            break;
        }
        return $this;
    }

    public function start()
    {
        $this->getServer()->start();
    }

    public function getServer()
    {
        return $this->server;
    }

    protected function registerSwooleEvents(array $callbacks)
    {
        foreach ($callbacks as $swolleEvent => $callback) {
            [$class, $method] = $callback;
            $instance = new $class();
            $this->server->on($swolleEvent, [$instance, $method]);
        }
    }
}