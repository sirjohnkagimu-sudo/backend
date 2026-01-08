<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Facades\Tenancy;

class InitializeTenancyByUser
{
    public function handle(Request $request, Closure $next)
    {
        // Handle OPTIONS requests (CORS preflight) without authentication
        if ($request->isMethod('OPTIONS')) {
            return $next($request);
        }

        $user = $request->user();

        if (!$user || !$user->tenant_id) {
            return response()->json([
                'message' => 'Tenant not resolved'
            ], 401);
        }

        // Initialize tenant
        Tenancy::initialize($user->tenant_id);

        // SECURITY: Ensure user belongs to tenant
        if (Tenancy::$tenant->id !== $user->tenant_id) {
            return response()->json([
                'message' => 'Access denied'
            ], 403);
        }

        return $next($request);
    }
}
