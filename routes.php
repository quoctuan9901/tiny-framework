<?php

use App\Controllers\LogsController;
use App\Controllers\ExampleController;
use Scaleflex\Commons\ApiResponse;

$router->prefix('api')->group(function () use ($router) {
    $router->get('demo/{param}/{paramDefault?}', [ExampleController::class, 'exampleDemoAction']);

    $router->get('keys/invalidation-check', [LogsController::class, 'invalidationCheck']);
});

$router->fallback(function () {
    return ApiResponse::error("Route not found", [], 404);
});