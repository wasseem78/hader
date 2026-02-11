<?php

// =============================================================================
// Middleware: Block access to sensitive files
// Prevents direct access to .env, artisan, composer.json, etc.
// This works regardless of web server (.htaccess may not work on OLS)
// =============================================================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockSensitiveFiles
{
    /**
     * Patterns that should NEVER be accessible via HTTP.
     */
    private array $blockedPatterns = [
        '/^\.env/',           // .env, .env.backup, .env.staging
        '/^artisan$/',        // artisan CLI
        '/^composer\.(json|lock)$/', // composer files
        '/^package(-lock)?\.json$/', // npm files
        '/^phpunit\.xml/',    // test config
        '/^\.(git|svn|hg)/',  // VCS directories
        '/^(webpack|vite|tailwind)\./',  // build config
        '/^Makefile$/',       // Makefile
        '/^docker/',          // docker files
        '/^storage\//',       // storage dir
        '/^vendor\//',        // vendor dir
        '/^database\//',      // database dir
        '/^config\//',        // config dir
        '/^app\//',           // app dir
        '/^resources\//',     // resources dir
        '/^bootstrap\//',     // bootstrap dir
        '/^tests\//',         // tests dir
        '/^routes\//',        // routes dir
        '/^lang\//',          // lang dir
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $path = ltrim($request->path(), '/');

        foreach ($this->blockedPatterns as $pattern) {
            if (preg_match($pattern, $path)) {
                abort(403, 'Access denied.');
            }
        }

        return $next($request);
    }
}
