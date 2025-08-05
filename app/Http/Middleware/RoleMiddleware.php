<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $roles
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Support multiple roles separated by comma
        $allowedRoles = explode(',', $roles);
        $user = auth()->user();

        $hasAccess = false;
        foreach ($allowedRoles as $role) {
            $trimmedRole = trim($role);
            if ($user->hasRole($trimmedRole)) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            abort(403, 'Unauthorized. You do not have the required role.');
        }

        return $next($request);
    }
}
