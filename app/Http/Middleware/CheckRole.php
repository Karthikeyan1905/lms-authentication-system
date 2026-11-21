<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Step 1: Get the authenticated user
        $user = $request->user();

        // Step 2: Check whether the user is logged in
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        // Step 3: Check whether the user's role is allowed
        if (!in_array($user->role, $roles, true)) {
            return response()->json([
                'message' => 'You do not have permission to access this resource.'
            ], 403);
        }

        // Step 4: Allow the request
        return $next($request);
    }
}
