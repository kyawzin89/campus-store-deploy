<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Treat every /api request as if it sent "Accept: application/json".
 *
 * Without this, a request with no Accept header that fails authentication
 * tries to redirect to a "login" web page that this API does not have.
 */
class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
