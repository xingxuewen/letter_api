<?php
namespace App\Http\Middleware;

use Closure;

class BeforeMiddleware
{
    public function handle($request, Closure $next)
    {
        logInfo('request_input', [
            'get' => $_GET,
            'post' => $_POST,
        ]);

        return $next($request);
    }
}