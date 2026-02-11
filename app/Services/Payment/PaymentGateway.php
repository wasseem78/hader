<?php

namespace App\Services\Payment;

use App\Models\Company;
use App\Models\Plan;

interface PaymentGateway
{
    /**
     * Create a checkout session for a subscription.
     */
    public function createCheckoutSession(Company $company, Plan $plan): string;

    /**
     * Create a portal session for managing subscription.
     */
    public function createPortalSession(Company $company): string;

    /**
     * Swap the current subscription to a new plan.
     */
    public function swapSubscription(Company $company, Plan $newPlan): void;

    /**
     * Cancel the current subscription.
     */
    public function cancelSubscription(Company $company): void;

    /**
     * Sync subscription status from provider.
     */
    public function syncSubscription(Company $company): void;
}
