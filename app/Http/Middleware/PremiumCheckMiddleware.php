<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PremiumCheckMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'This content requires a premium membership');
        }

        // Admin and Superadmin bypass premium check
        if (in_array($user->role, ['ADMIN', 'SUPERADMIN'])) {
            return $next($request);
        }

        $isPremium = $user->membership_tier === 'PREMIUM'
            && $user->membership_status === 'ACTIVE'
            && (is_null($user->membership_expiry) || $user->membership_expiry > now());

        if (!$isPremium) {
            abort(403, 'This content requires a premium membership');
        }

        return $next($request);
    }
}