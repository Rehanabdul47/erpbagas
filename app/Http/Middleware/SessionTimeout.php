<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;

class SessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $lastActivity = session('last_activity');
            // Timeout in minutes, default 120
            $timeoutMinutes = (int) (Setting::where('key', 'session_timeout')->value('value') ?? 120);

            if ($lastActivity && (time() - $lastActivity) > ($timeoutMinutes * 60)) {
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();
                return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir karena tidak ada aktivitas.');
            }

            session(['last_activity' => time()]);
        }

        return $next($request);
    }
}
