<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        // Step 1: Get the currently authenticated user
        $user = $request->user();

        // Step 2: Allow Super Admin to access all tenants
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        // Step 3: Check whether the user belongs to a school
        if (!$user->tenant_id) {
            return response()->json([
                'message' => 'You are not assigned to any school.'
            ], 403);
        }

        // Step 4: Get the user's school
        $tenant = $user->tenant;

        // Step 5: Check whether the school exists and is active
        if (!$tenant || $tenant->status !== 'active') {
            return response()->json([
                'message' => 'Your school account is inactive or unavailable.'
            ], 403);
        }

        // Step 6: Allow the request to continue
        return $next($request);
    }
}