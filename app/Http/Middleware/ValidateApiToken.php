<?php

// =============================================================================
// ValidateApiToken Middleware - Authenticate device API tokens
// =============================================================================

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $ability = null): Response
    {
        $token = $this->extractToken($request);

        if (!$token) {
            return response()->json([
                'message' => 'API token required.',
                'error' => 'missing_token',
            ], 401);
        }

        // Find token by hash
        $hashedToken = hash('sha256', $token);
        $apiToken = ApiToken::where('token', $hashedToken)->first();

        if (!$apiToken || !$apiToken->isValid()) {
            return response()->json([
                'message' => 'Invalid or expired API token.',
                'error' => 'invalid_token',
            ], 401);
        }

        // Check ability if specified
        if ($ability && !$apiToken->hasAbility($ability)) {
            return response()->json([
                'message' => 'Token does not have required ability.',
                'error' => 'insufficient_permissions',
            ], 403);
        }

        // Attach token to request for later use
        $request->attributes->set('api_token', $apiToken);

        return $next($request);
    }

    /**
     * Extract token from request.
     */
    protected function extractToken(Request $request): ?string
    {
        // Check Authorization header (Bearer token)
        $authHeader = $request->header('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }

        // Check X-API-Token header
        if ($request->hasHeader('X-API-Token')) {
            return $request->header('X-API-Token');
        }

        // Check query parameter (for webhooks)
        if ($request->has('api_token')) {
            return $request->query('api_token');
        }

        return null;
    }
}
