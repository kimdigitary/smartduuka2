<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveRegister
{
    public function handle(Request $request, Closure $next): Response
    {
        $authUser = $request->user();

        if (!$authUser) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $tenant = tenant('id') ?? tenantId();

        $openRegister = $authUser instanceof User && tenancy()->initialized
            ? $authUser->openRegister()
            : ($tenant ? tenantContext(function () use ($authUser) {
                $email = $authUser->email ?? null;

                if (!$email) {
                    return null;
                }

                return User::where('email', $email)->first()?->openRegister();
            }, $tenant) : null);

        if (!$openRegister) {
            return response()->json([
                'message' => 'You do not have any open registers.',
            ], 409);
        }

        return $next($request);
    }
}
