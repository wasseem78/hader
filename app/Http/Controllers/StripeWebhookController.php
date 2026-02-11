<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        }

        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event->data->object);
                break;
            case 'invoice.paid':
                $this->handleInvoicePaid($event->data->object);
                break;
            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event->data->object);
                break;
        }

        return response('Handled', 200);
    }

    protected function handleCheckoutSessionCompleted($session)
    {
        $companyId = $session->metadata->company_id ?? null;
        if (!$companyId) return;

        $company = Company::find($companyId);
        if ($company) {
            $company->update([
                'stripe_customer_id' => $session->customer,
                'stripe_subscription_id' => $session->subscription,
                'stripe_subscription_status' => 'active',
            ]);
        }
    }

    protected function handleInvoicePaid($invoice)
    {
        $company = Company::where('stripe_customer_id', $invoice->customer)->first();
        if (!$company) return;

        // Create local invoice record
        Invoice::create([
            'company_id' => $company->id,
            'stripe_invoice_id' => $invoice->id,
            'amount' => $invoice->amount_paid / 100,
            'currency' => $invoice->currency,
            'status' => 'paid',
            'paid_at' => now(),
            'pdf_url' => $invoice->invoice_pdf,
        ]);

        // Update subscription end date
        $company->update([
            'subscription_ends_at' => \Carbon\Carbon::createFromTimestamp($invoice->lines->data[0]->period->end),
            'stripe_subscription_status' => 'active',
        ]);
    }

    protected function handleSubscriptionDeleted($subscription)
    {
        $company = Company::where('stripe_subscription_id', $subscription->id)->first();
        if ($company) {
            $company->update([
                'stripe_subscription_status' => 'canceled',
                'subscription_ends_at' => now(),
            ]);
        }
    }
}
