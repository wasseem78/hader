<?php

// =============================================================================
// Webhook Controller - Device Push and Stripe Webhooks
// =============================================================================

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

class WebhookController extends Controller
{
    /**
     * Handle attendance push from device.
     *
     * POST /api/webhooks/device/attendance
     */
    public function deviceAttendance(Request $request): JsonResponse
    {
        // Token validation is handled by middleware
        $token = $request->attributes->get('api_token');

        if (!$token instanceof ApiToken) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $device = $token->device;

        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }

        // Validate payload
        $validated = $request->validate([
            'records' => 'required|array',
            'records.*.user_id' => 'required|integer',
            'records.*.timestamp' => 'required|date',
            'records.*.type' => 'in:in,out,break_start,break_end',
            'records.*.verification' => 'in:fingerprint,face,card,password',
            'records.*.record_id' => 'nullable|string',
        ]);

        $imported = 0;
        $skipped = 0;

        foreach ($validated['records'] as $record) {
            // Check for duplicate
            if (!empty($record['record_id'])) {
                $exists = AttendanceRecord::where('device_id', $device->id)
                    ->where('device_record_id', $record['record_id'])
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }
            }

            // Find user by device_user_id
            $user = $device->company->users()
                ->where('device_user_id', $record['user_id'])
                ->first();

            if (!$user) {
                Log::warning('Unknown user in attendance webhook', [
                    'device_id' => $device->id,
                    'user_id' => $record['user_id'],
                ]);
                $skipped++;
                continue;
            }

            $punchedAt = new \DateTime($record['timestamp']);

            AttendanceRecord::create([
                'company_id' => $device->company_id,
                'user_id' => $user->id,
                'device_id' => $device->id,
                'punched_at' => $punchedAt,
                'punch_date' => $punchedAt->format('Y-m-d'),
                'punch_time' => $punchedAt->format('H:i:s'),
                'type' => $record['type'] ?? 'in',
                'verification_type' => $record['verification'] ?? null,
                'device_record_id' => $record['record_id'] ?? null,
                'status' => 'pending',
                'raw_data' => $record,
            ]);

            $imported++;
        }

        // Update device last seen
        $device->markOnline();

        // Log token usage
        $token->recordUsage($request->ip());

        return response()->json([
            'message' => 'Attendance records processed',
            'imported' => $imported,
            'skipped' => $skipped,
        ]);
    }

    /**
     * Handle device heartbeat/status.
     *
     * POST /api/webhooks/device/heartbeat
     */
    public function deviceHeartbeat(Request $request): JsonResponse
    {
        $token = $request->attributes->get('api_token');

        if (!$token instanceof ApiToken || !$token->device) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $device = $token->device;

        // Update device status
        $device->update([
            'status' => 'online',
            'last_seen' => now(),
            'total_users' => $request->input('total_users', $device->total_users),
            'total_fingerprints' => $request->input('total_fingerprints', $device->total_fingerprints),
        ]);

        $token->recordUsage($request->ip());

        return response()->json([
            'message' => 'Heartbeat received',
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /**
     * Handle Stripe webhooks.
     *
     * POST /api/webhooks/stripe
     */
    public function stripe(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\Exception $e) {
            Log::error('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        Log::info('Stripe webhook received', [
            'type' => $event->type,
            'id' => $event->id,
        ]);

        switch ($event->type) {
            case 'invoice.paid':
                $this->handleInvoicePaid($event->data->object);
                break;

            case 'invoice.payment_failed':
                $this->handleInvoicePaymentFailed($event->data->object);
                break;

            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event->data->object);
                break;

            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event->data->object);
                break;

            default:
                Log::info('Unhandled Stripe webhook type: ' . $event->type);
        }

        return response()->json(['received' => true]);
    }

    /**
     * Handle invoice.paid event.
     */
    protected function handleInvoicePaid($stripeInvoice): void
    {
        $company = Company::where('stripe_customer_id', $stripeInvoice->customer)->first();

        if (!$company) {
            Log::warning('Company not found for Stripe customer', [
                'customer_id' => $stripeInvoice->customer,
            ]);
            return;
        }

        // Create or update invoice record
        Invoice::updateOrCreate(
            ['stripe_invoice_id' => $stripeInvoice->id],
            [
                'company_id' => $company->id,
                'number' => $stripeInvoice->number,
                'invoice_date' => date('Y-m-d', $stripeInvoice->created),
                'due_date' => $stripeInvoice->due_date ? date('Y-m-d', $stripeInvoice->due_date) : null,
                'paid_date' => now(),
                'currency' => strtoupper($stripeInvoice->currency),
                'subtotal' => $stripeInvoice->subtotal / 100,
                'tax' => $stripeInvoice->tax / 100,
                'total' => $stripeInvoice->total / 100,
                'status' => 'paid',
                'receipt_url' => $stripeInvoice->hosted_invoice_url,
                'pdf_url' => $stripeInvoice->invoice_pdf,
                'period_start' => $stripeInvoice->period_start ? date('Y-m-d', $stripeInvoice->period_start) : null,
                'period_end' => $stripeInvoice->period_end ? date('Y-m-d', $stripeInvoice->period_end) : null,
            ]
        );

        Log::info('Invoice marked as paid', [
            'company_id' => $company->id,
            'invoice_id' => $stripeInvoice->id,
        ]);
    }

    /**
     * Handle invoice.payment_failed event.
     */
    protected function handleInvoicePaymentFailed($stripeInvoice): void
    {
        $company = Company::where('stripe_customer_id', $stripeInvoice->customer)->first();

        if ($company) {
            Invoice::updateOrCreate(
                ['stripe_invoice_id' => $stripeInvoice->id],
                [
                    'company_id' => $company->id,
                    'status' => 'failed',
                ]
            );

            // TODO: Send payment failed notification
        }
    }

    /**
     * Handle customer.subscription.updated event.
     */
    protected function handleSubscriptionUpdated($subscription): void
    {
        $company = Company::where('stripe_customer_id', $subscription->customer)->first();

        if (!$company) {
            return;
        }

        $company->update([
            'stripe_subscription_id' => $subscription->id,
            'stripe_subscription_status' => $subscription->status,
            'subscription_ends_at' => $subscription->current_period_end
                ? date('Y-m-d H:i:s', $subscription->current_period_end)
                : null,
        ]);

        Log::info('Subscription updated', [
            'company_id' => $company->id,
            'status' => $subscription->status,
        ]);
    }

    /**
     * Handle customer.subscription.deleted event.
     */
    protected function handleSubscriptionDeleted($subscription): void
    {
        $company = Company::where('stripe_subscription_id', $subscription->id)->first();

        if ($company) {
            $company->update([
                'stripe_subscription_status' => 'cancelled',
                'subscription_ends_at' => now(),
            ]);

            // TODO: Send subscription cancelled notification
        }
    }
}
