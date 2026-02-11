<?php

namespace App\Services\Payment;

use App\Models\Company;
use App\Models\Plan;
use Stripe\StripeClient;

class StripeGateway implements PaymentGateway
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function createCheckoutSession(Company $company, Plan $plan): string
    {
        $priceId = $plan->stripe_price_monthly_id; // Default to monthly for now, logic can be added for yearly

        $session = $this->stripe->checkout->sessions->create([
            'customer' => $company->stripe_customer_id,
            'mode' => 'subscription',
            'line_items' => [
                [
                    'price' => $priceId,
                    'quantity' => 1,
                ],
            ],
            'success_url' => route('billing.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('billing.cancel'),
            'subscription_data' => [
                'metadata' => [
                    'company_id' => $company->id,
                    'plan_id' => $plan->id,
                ],
            ],
        ]);

        return $session->url;
    }

    public function createPortalSession(Company $company): string
    {
        $session = $this->stripe->billingPortal->sessions->create([
            'customer' => $company->stripe_customer_id,
            'return_url' => route('dashboard'),
        ]);

        return $session->url;
    }

    public function swapSubscription(Company $company, Plan $newPlan): void
    {
        if (!$company->stripe_subscription_id) {
            throw new \Exception("No active subscription to swap.");
        }

        $subscription = $this->stripe->subscriptions->retrieve($company->stripe_subscription_id);
        $itemId = $subscription->items->data[0]->id;

        $this->stripe->subscriptions->update($company->stripe_subscription_id, [
            'items' => [
                [
                    'id' => $itemId,
                    'price' => $newPlan->stripe_price_monthly_id,
                ],
            ],
            'proration_behavior' => 'always_invoice',
        ]);
        
        $company->update(['plan_id' => $newPlan->id]);
    }

    public function cancelSubscription(Company $company): void
    {
        if (!$company->stripe_subscription_id) {
            return;
        }

        $this->stripe->subscriptions->update($company->stripe_subscription_id, [
            'cancel_at_period_end' => true,
        ]);
    }

    public function syncSubscription(Company $company): void
    {
        if (!$company->stripe_subscription_id) {
            return;
        }

        $sub = $this->stripe->subscriptions->retrieve($company->stripe_subscription_id);

        $company->update([
            'stripe_subscription_status' => $sub->status,
            'subscription_ends_at' => \Carbon\Carbon::createFromTimestamp($sub->current_period_end),
        ]);
    }
}
