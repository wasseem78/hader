<?php

// =============================================================================
// CheckPlanLimits Middleware - Verify tenant is within plan limits
// =============================================================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanLimits
{
    /**
     * Resource types and their limit methods.
     */
    protected array $limitChecks = [
        'devices' => 'canAddDevice',
        'employees' => 'canAddEmployee',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $resource = null): Response
    {
        $user = $request->user();

        if (!$user || !$user->company) {
            return $next($request);
        }

        $company = $user->company;

        // Check subscription status
        // Check subscription status
        if (!$company->hasActiveSubscription()) {
            // Prevent redirect loop - don't redirect if already on billing page
            if ($request->routeIs('billing.*')) {
                return $next($request);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('messages.subscription_expired'),
                    'error' => 'subscription_expired',
                    'data' => [
                        'trial_ended' => !$company->onTrial(),
                        'subscription_status' => $company->stripe_subscription_status,
                    ],
                ], 403);
            }

            return redirect()->route('billing.index')->with('error', __('messages.subscription_expired'));
        }

        // Check specific resource limit if provided
        if ($resource && isset($this->limitChecks[$resource])) {
            $method = $this->limitChecks[$resource];

            // Only check on create operations
            if (in_array($request->method(), ['POST', 'PUT'])) {
                if (!$company->$method()) {
                    return response()->json([
                        'message' => __('messages.plan_limit_reached', [
                            'resource' => $resource,
                        ]),
                        'error' => 'plan_limit_exceeded',
                        'data' => [
                            'resource' => $resource,
                            'current_plan' => $company->plan?->name,
                            'limit' => $company->{"max_" . $resource},
                        ],
                    ], 403);
                }
            }
        }

        return $next($request);
    }
}
