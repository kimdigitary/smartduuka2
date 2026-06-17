<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DynamicSanctumConfiguration
{
    public function handle(Request $request, Closure $next): Response
    {
        $isTenantRequest = tenancy()->initialized;

        config([
            'auth.guards.sanctum.provider' => $isTenantRequest ? 'users' : 'central_users',
            'sanctum.guard' => $request->bearerToken()
                ? []
                : [$isTenantRequest ? 'web' : 'central'],
        ]);

        return $next($request);
    }
}
