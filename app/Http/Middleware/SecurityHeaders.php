<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Skip security headers for ICLOCK/ADMS device endpoints
        // ZKTeco devices have limited HTTP buffer and don't need CSP/HSTS
        if (str_starts_with($request->path(), 'iclock')) {
            return $response;
        }

        // Content Security Policy
        // Allow scripts from self, unsafe-inline (for now, ideally remove), and specific CDNs (Google Fonts, Stripe, Pusher)
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://js.stripe.com https://js.pusher.com; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net; " .
               "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net data:; " .
               "img-src 'self' data: https:; " .
               "connect-src 'self' https://api.stripe.com wss://ws-mt1.pusher.com https://sockjs-mt1.pusher.com; " .
               "frame-src 'self' https://js.stripe.com https://hooks.stripe.com;";

        $response->headers->set('Content-Security-Policy', $csp);
        
        // HSTS (HTTP Strict Transport Security) - 1 Year
        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // X-Frame-Options (Prevent Clickjacking)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // X-Content-Type-Options (Prevent MIME sniffing)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy (Limit browser features)
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
