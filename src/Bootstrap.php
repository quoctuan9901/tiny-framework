<?php

namespace App;

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Routing\Router;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Routing\Contracts\CallableDispatcher as CallableDispatcherContract;
use Illuminate\Routing\CallableDispatcher;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Routing\Pipeline;
use Illuminate\Support\Facades\Facade;
use Illuminate\Http\Request as IlluminateRequest;

class Bootstrap
{
    public $container;
    public $events;
    public $router;
    public $config;

    public function boot()
    {
        $this->container = new Container;
        $this->initContainer();

        // Get Router from container 
        $this->router = $this->container->make(Router::class); 

        $this->loadMiddleware();
        $this->bootDatabase();

        // Dispatch the request
        $request = $this->container->make('Illuminate\Http\Request');
        $this->handle($request); 
    }

    private function initContainer() 
    {
        $this->container->singleton(CallableDispatcherContract::class, function ($container) {
            return new CallableDispatcher($container);
        });

        $this->container->singleton('Illuminate\Contracts\Events\Dispatcher', function ($container) {
            return new Dispatcher($container);
        });

        $this->events = $this->container->make('Illuminate\Contracts\Events\Dispatcher');

        $this->container->singleton(Router::class, function ($container) {
            $router = new Router($this->events, $container);
            require_once __DIR__ . '/../routes.php';
            return $router;
        });

        $this->container->instance('Illuminate\Http\Request', Request::capture());

        $this->container->singleton('redirect', function ($container) {
            $url = $container->make('Illuminate\Routing\UrlGenerator');
            return new Redirector($url);
        });

        $this->container->singleton('Illuminate\Routing\UrlGenerator', function ($container) {
            $request = $container->make('Illuminate\Http\Request');
            return new UrlGenerator($this->router->getRoutes(), $request);
        });

        $this->container->singleton('request', function ($container) {
            return IlluminateRequest::capture();
        });
        
        Facade::setFacadeApplication($this->container);
    }

    protected function loadMiddleware() 
    { 
        $globalMiddleware = require __DIR__ . '/../config/middleware.php';
        $routeMiddleware = $globalMiddleware['routeMiddleware'];
        unset($globalMiddleware['routeMiddleware']);

        foreach ($globalMiddleware as $middleware) {
            $this->router->middleware($middleware);
        }

        foreach ($routeMiddleware as $key => $middleware) {
            $this->router->aliasMiddleware($key, $middleware);
        }
    }

    protected function bootDatabase() {
        $capsule = new Capsule;
        $capsule->addConnection(config('database', 'pgsql'), 'default');
        $capsule->setEventDispatcher(new Dispatcher(new Container));
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

    private function handle(Request $request)
    {
        $response = (new Pipeline($this->container))
            ->send($request)
            ->through($this->getGlobalMiddleware())
            ->then(function (Request $request) {
                return $this->router->dispatch($request);
            });
        $response->send(); 
    }

    private function getGlobalMiddleware() 
    {
        $middleware = require __DIR__ . '/../config/middleware.php';
        unset($middleware['routeMiddleware']);
        return $middleware;
    }
}