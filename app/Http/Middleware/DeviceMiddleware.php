<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\TrustedDevice;
use Illuminate\Support\Facades\Auth;

class DeviceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $deviceId = $request->cookie('device_id');
            $user = Auth::user();

            if ($deviceId) {
                $device = TrustedDevice::where('device_id', $deviceId)
                    ->where('user_id', $user->id)
                    ->whereNull('revoked_at')
                    ->first();

                if ($device) {
                    $device->update([
                        'last_seen_at' => now(),
                        'last_ip' => $request->ip(),
                        'user_agent' => $request->userAgent()
                    ]);
                    
                    // Add device to request context
                    $request->merge(['trusted_device' => $device]);
                }
            }
        }

        return $next($request);
    }
}
