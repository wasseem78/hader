<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            try {
                if (Auth::guard($guard)->check()) {
                    if ($guard === 'central') {
                        return redirect()->route('super-admin.dashboard');
                    }
                    return redirect(RouteServiceProvider::HOME);
                }
            } catch (\Illuminate\Database\QueryException $e) {
                // Tenant DB connection not configured — treat as guest
                Auth::guard($guard)->forgetUser();
                continue;
            }
        }

        return $next($request);
    }
}
