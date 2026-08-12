<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Untrusted sessions (see LoginController - the "Remember this device"
 * checkbox) are assumed to be on a shared shop-floor tablet and get logged
 * out after a short idle period. Trusted (personal-device) sessions are
 * exempt, matching Laravel's own remember-me cookie behavior.
 */
class EnforceIdleTimeout
{
    private const IDLE_LIMIT_MINUTES = 15;

    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check() || $request->session()->get('device_trusted')) {
            $this->touch($request);

            return $next($request);
        }

        $lastActivity = $request->session()->get('last_activity_at');

        if ($lastActivity !== null && now()->diffInMinutes($lastActivity) > self::IDLE_LIMIT_MINUTES) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'You were signed out after a period of inactivity on this device.'], 401);
            }

            return redirect()->route('login')->with('error', 'You were signed out after a period of inactivity on this device.');
        }

        $this->touch($request);

        return $next($request);
    }

    private function touch(Request $request): void
    {
        if (Auth::check()) {
            $request->session()->put('last_activity_at', now());
        }
    }
}
