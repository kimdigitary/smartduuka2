<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBranchIdHeader
{
    public function handle(Request $request, Closure $next, string ...$methods): Response
    {
        if ($methods !== [] && ! in_array($request->method(), array_map('strtoupper', $methods), true)) {
            return $next($request);
        }

        $branchId = $request->header('X-BranchId');

        if ($branchId === null) {
            return $this->deny('Selected Branch is invalid.');
        }

        $branchId = trim((string)$branchId);

        if ($branchId === '') {
            return $this->deny('Selected Branch is invalid.');
        }

        if (!ctype_digit($branchId)) {
            return $this->deny('Selected Branch is invalid.');
        }

        $request->headers->set('X-BranchId', $branchId);
        $request->merge([
            'branch_id' => branchId(),
        ]);

        return $next($request);
    }

    private function deny(string $message): Response
    {
        return response()->json([
            'success' => false,
            'status'  => 400,
            'message' => $message,
        ], 400);
    }
}
