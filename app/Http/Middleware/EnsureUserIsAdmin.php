<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request. Allow only authenticated users with role === 'admin'.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Forbidden. Store Manager (Admin) access is required.',
                ], 403);
            }

            abort(403, 'Forbidden. Store Manager (Admin) access is required.');
        }

        return $next($request);
    }
}
