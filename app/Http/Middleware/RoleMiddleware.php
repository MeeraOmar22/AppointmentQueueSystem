<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $roles)
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }

        // Support multiple roles separated by pipe (e.g., 'staff|developer')
        $allowedRoles = array_map('trim', explode('|', $roles));
        $userRole = Auth::user()->role;
        
        // Debug log
        Log::info('RoleMiddleware check', [
            'user' => Auth::user()->email,
            'user_role' => $userRole,
            'allowed_roles' => $allowedRoles,
            'path' => $request->path(),
            'in_array' => in_array($userRole, $allowedRoles),
        ]);
        
        if (!in_array($userRole, $allowedRoles)) {
            Log::error('RoleMiddleware DENIED', [
                'user' => Auth::user()->email,
                'user_role' => $userRole,
                'allowed_roles' => $allowedRoles,
                'path' => $request->path(),
            ]);
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}

