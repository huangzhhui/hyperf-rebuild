<?php

use App\Controller\HelloController;
use App\Middleware\MiddlewareB;

return [
    ['GET', '/hello/index', [HelloController::class, 'index'], [
        'middlewares' => [
            MiddlewareB::class,
        ],
    ]],
    ['GET', '/hello/hyperf', [HelloController::class, 'hyperf']],
];