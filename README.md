<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://assets-global.website-files.com/623086c828f7c9787009cf20/65d708d34f30f4ccb58b5ea2_logo-scaleflex.svg" width="400"></a></p>

## About Tiny Framework

A in-house PHP Tiny Framework, supposed to be used for the replacement for api-filerobot, shared-api, etc..
Tiny Framework was created with the purpose of being a Framework used to create APIs with a super simple structure and easy to use and maintain. Tiny Framework only uses 3 main components: Router, Model, Controller and Middleware.

## Installing & Config
To use Tiny Framework, we must be able to use PHP 8.2 or higher and have Composer.

- **[Herd](https://herd.laravel.com/)** : This is the software I use to switch between PHP versions quickly if you are working on a lower PHP version
- **[Composer](https://getcomposer.org/download/)**: Install composer

After install PHP and Composer. Next step, you can clone and install source
```bash
git clone git@code.scaleflex.cloud:scaleflex-sandbox/t-frame-php.git
cd t-frame-php
composer install
```
Then we need to update Database connection configuration in
```bash
./config/database.php
```

Finally, start webserver
```bash
php -S localhost:8000 -t public
```

## Create a new endpoint

* **Step 1:** Create a new controller in ./src/Controller/ExampleController.php
```php
namespace App\Controllers;
use App\Queries\SqlQuery;
use ApiResponse;

class ExampleController extends BaseController {

    public function exampleDemoAction ($param, $paramDefault = null) {
        try {
            $demo_data = SqlQuery::getDemo();

            $result = [
                'data' => 'This is ExampleController - exampleDemoAction',
                'parameter: ' => $param . " | " . $paramDefault,
                'demo_result' => $demo_data
            ] ;

            return ApiResponse::success($result, "Demo API");
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage());
        }
    }
}
```
* **Step 2:** Create a new router in ./routes.php
```php
use App\Controllers\ExampleController;

$router->prefix('api')->group(function () use ($router) {
    $router->get('demo/{param}/{paramDefault?}', [ExampleController::class, 'exampleDemoAction']);
});
```

* **Step 3:** Run curl for testing
```curl
curl --location 'http://localhost:8000/api/demo/test/scaleflex'
```

## Create a middleware
* **Step 1:** Create a new middleware in ./src/Middleware/Authenticate.php
```php
<?php

namespace App\Middleware;

use ApiResponse;
use Closure;

class Authenticate
{
    public function handle($request, Closure $next, $guard = null)
    {
        if (!$request->header('X-Session-Token')) {
            return ApiResponse::error("Error Authenticate.");
        }

        return $next($request);
    }
}
```

* **Step 2:** Declare middleware in ./config/middleware.php
```php
return [
    // Global middleware
    \App\Middleware\StartSession::class,

    // Route middleware
    'routeMiddleware' => [
        'auth' => \App\Middleware\Authenticate::class,
    ]
];
```

* **Step 3:** Set middleware in group a router or each route
```php
$router->prefix('api')->group(function () use ($router) {
    $router->get('demo/{param}/{paramDefault?}', [ExampleController::class, 'exampleDemoAction'])->middleware('auth');

    $router->get('keys/invalidation-check', [LogsController::class, 'invalidationCheck']);
});
```

or

```php
$router->prefix('api')->middleware('auth')->group(function () use ($router) {
    $router->get('demo/{param}/{paramDefault?}', [ExampleController::class, 'exampleDemoAction']);

    $router->get('keys/invalidation-check', [LogsController::class, 'invalidationCheck']);
});
```

## Debug endpoint
If you need debug a endpoint, please add a query parameter in url (debug=8022)
```curl
curl --location 'http://localhost:8000/api/demo/test/scaleflex?debug=8022'
```

## License

The Tiny framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
