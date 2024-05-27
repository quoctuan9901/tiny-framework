<?php

namespace App\Middleware;

use ApiResponse;
use Closure;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (!$request->header('X-Session-Token')) {
            return ApiResponse::error("Error Authenticate.");
        }

        return $next($request);
    }
}