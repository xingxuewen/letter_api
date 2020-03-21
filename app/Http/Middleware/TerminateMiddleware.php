<?php

namespace App\Http\Middleware;

use Closure;

/**
 * @author hefan
 */
class TerminateMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response)
    {
        switch (true) {
            case $response instanceof \Illuminate\Http\JsonResponse:
                logInfo('request_out', [
                    'status_code' => $response->getStatusCode(),
                    'format' => 'json',
                    'content' => $response->getData(true),
                ]);
                break;
            default:
                logInfo('request_out', [
                    'status_code' => $response->getStatusCode(),
                    'format' => 'text',
                    //'content' => $response->getContent(),
                    'content' => 'only print JSON content',
                ]);
                break;
        }
    }

}
