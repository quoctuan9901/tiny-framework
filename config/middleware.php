<?php 

return [
    // Global middleware
    \App\Middleware\StartSession::class,

    // Route middleware
    'routeMiddleware' => [
        'auth' => \App\Middleware\Authenticate::class,
    ]
];

?>