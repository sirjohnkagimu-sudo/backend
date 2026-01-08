<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserBelongsToTenant
{
    public function handle(Request $request, Closure $next)
    {
        // Handle OPTIONS requests (CORS preflight) without authentication
        if ($request->isMethod('OPTIONS')) {
            return $next($request);
        }

        $tenant = tenancy()->tenant;
        $user = $request->user();

        if (!$tenant || !$user || $tenant->id !== $user->tenant_id) {
            return response()->json([
                'message' => 'Access denied'
            ], 403);
        }

        return $next($request);
    }
}
