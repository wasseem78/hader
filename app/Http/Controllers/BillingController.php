<?php

// =============================================================================
// Billing Controller - Subscription Management (Production-Ready)
// Supports both Stripe and standalone manual subscription management
// =============================================================================

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BillingController extends Controller
{
    /**
     * Show subscription management dashboard.
     */
    public function index()
    {
        $company = $this->getCompany();
        if (!$company) {
            return redirect()->route('dashboard')->with('error', __('messages.no_company'));
        }

        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        $currentPlan = $company->plan;

        // Calculate subscription info
        $subscriptionInfo = $this->getSubscriptionInfo($company);

        // Calculate usage stats from tenant DB (Company model relationships
        // would use central company_id which doesn't match tenant data)
        $usageStats = $this->getUsageStats($company);

        return view('billing.index', [
            'company' => $company,
            'plans' => $plans,
            'currentPlan' => $currentPlan,
            'subscriptionInfo' => $subscriptionInfo,
            'usageStats' => $usageStats,
        ]);
    }

    /**
     * Subscribe to a plan (manual / standalone).
     */
    public function subscribe(Request $request, Plan $plan)
    {
        $company = $this->getCompany();
        if (!$company) {
            return redirect()->route('dashboard')->with('error', __('messages.no_company'));
        }

        // Validate downgrade: ensure current usage doesn't exceed new plan limits
        if (!$plan->isFree()) {
            $usageStats = $this->getUsageStats($company);
            if ($usageStats['devices_count'] > $plan->max_devices) {
                return back()->with('error', __('messages.downgrade_too_many_devices', [
                    'current' => $usageStats['devices_count'],
                    'limit' => $plan->max_devices,
                ]));
            }
            if ($usageStats['employees_count'] > $plan->max_employees) {
                return back()->with('error', __('messages.downgrade_too_many_employees', [
                    'current' => $usageStats['employees_count'],
                    'limit' => $plan->max_employees,
                ]));
            }
        }

        $billingCycle = $request->input('billing_cycle', 'monthly');

        try {
            // Use central DB connection for transaction (Company & Invoice live there)
            DB::connection('mysql')->beginTransaction();

            $now = now();
            $endsAt = $billingCycle === 'yearly' ? $now->copy()->addYear() : $now->copy()->addMonth();
            $price = $billingCycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;

            // Update company subscription
            $company->update([
                'plan_id' => $plan->id,
                'stripe_subscription_status' => $plan->isFree() ? 'free' : 'active',
                'subscription_ends_at' => $plan->isFree() ? null : $endsAt,
                'max_devices' => $plan->max_devices,
                'max_employees' => $plan->max_employees,
                'trial_ends_at' => null, // Clear trial when subscribing
            ]);

            // Sync Tenant model
            $this->syncTenant($company, $plan);

            // Create invoice record (for paid plans)
            if (!$plan->isFree()) {
                $invoiceNumber = 'INV-' . strtoupper(Str::random(8));
                Invoice::create([
                    'company_id' => $company->id,
                    'plan_id' => $plan->id,
                    'number' => $invoiceNumber,
                    'invoice_date' => $now,
                    'due_date' => $now,
                    'paid_date' => $now,
                    'currency' => $plan->currency ?? 'USD',
                    'subtotal' => $price,
                    'tax' => 0,
                    'discount' => 0,
                    'total' => $price,
                    'status' => 'paid',
                    'payment_method' => 'manual',
                    'period_start' => $now,
                    'period_end' => $endsAt,
                    'line_items' => [
                        [
                            'description' => $plan->name . ' Plan (' . ucfirst($billingCycle) . ')',
                            'quantity' => 1,
                            'unit_price' => $price,
                            'total' => $price,
                        ]
                    ],
                ]);
            }

            DB::connection('mysql')->commit();

            return redirect()->route('billing.index')
                ->with('success', __('messages.subscription_updated'));

        } catch (\Exception $e) {
            DB::connection('mysql')->rollBack();
            Log::error('Subscription error: ' . $e->getMessage());
            return back()->with('error', __('messages.subscription_error'));
        }
    }

    /**
     * Renew current subscription.
     */
    public function renew(Request $request)
    {
        $company = $this->getCompany();
        if (!$company) {
            return redirect()->route('dashboard')->with('error', __('messages.no_company'));
        }

        $plan = $company->plan;
        if (!$plan || $plan->isFree()) {
            return redirect()->route('billing.index')->with('error', __('messages.no_plan_to_renew'));
        }

        $billingCycle = $request->input('billing_cycle', 'monthly');

        try {
            DB::connection('mysql')->beginTransaction();

            $now = now();
            // If subscription hasn't expired yet, extend from end date
            $startFrom = ($company->subscription_ends_at && $company->subscription_ends_at->isFuture())
                ? $company->subscription_ends_at
                : $now;

            $endsAt = $billingCycle === 'yearly'
                ? $startFrom->copy()->addYear()
                : $startFrom->copy()->addMonth();

            $price = $billingCycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;

            $company->update([
                'stripe_subscription_status' => 'active',
                'subscription_ends_at' => $endsAt,
                'trial_ends_at' => null,
            ]);

            // Sync Tenant model
            $this->syncTenant($company, $plan);

            // Create invoice
            $invoiceNumber = 'INV-' . strtoupper(Str::random(8));
            Invoice::create([
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'number' => $invoiceNumber,
                'invoice_date' => $now,
                'due_date' => $now,
                'paid_date' => $now,
                'currency' => $plan->currency ?? 'USD',
                'subtotal' => $price,
                'tax' => 0,
                'discount' => 0,
                'total' => $price,
                'status' => 'paid',
                'payment_method' => 'manual',
                'period_start' => $startFrom,
                'period_end' => $endsAt,
                'line_items' => [
                    [
                        'description' => $plan->name . ' Plan Renewal (' . ucfirst($billingCycle) . ')',
                        'quantity' => 1,
                        'unit_price' => $price,
                        'total' => $price,
                    ]
                ],
            ]);

            DB::connection('mysql')->commit();

            return redirect()->route('billing.index')
                ->with('success', __('messages.subscription_renewed'));

        } catch (\Exception $e) {
            DB::connection('mysql')->rollBack();
            Log::error('Renewal error: ' . $e->getMessage());
            return back()->with('error', __('messages.subscription_error'));
        }
    }

    /**
     * Cancel current subscription.
     */
    public function cancelSubscription(Request $request)
    {
        $company = $this->getCompany();
        if (!$company) {
            return redirect()->route('dashboard')->with('error', __('messages.no_company'));
        }

        $plan = $company->plan;
        if (!$plan || $plan->isFree()) {
            return redirect()->route('billing.index')->with('error', __('messages.cannot_cancel_free'));
        }

        try {
            $company->update([
                'stripe_subscription_status' => 'cancelled',
                // Keep subscription_ends_at so they can still use until period ends
            ]);

            // Sync Tenant model
            $this->syncTenant($company, $plan);

            return redirect()->route('billing.index')
                ->with('success', __('messages.subscription_cancelled'));

        } catch (\Exception $e) {
            Log::error('Cancellation error: ' . $e->getMessage());
            return back()->with('error', __('messages.subscription_error'));
        }
    }

    /**
     * Show invoice history.
     */
    public function invoices()
    {
        $company = $this->getCompany();
        if (!$company) {
            return redirect()->route('dashboard')->with('error', __('messages.no_company'));
        }

        $invoices = Invoice::where('company_id', $company->id)
            ->orderBy('invoice_date', 'desc')
            ->paginate(15);

        return view('billing.invoices', [
            'company' => $company,
            'invoices' => $invoices,
            'currentPlan' => $company->plan,
        ]);
    }

    /**
     * Show single invoice detail.
     */
    public function showInvoice(Invoice $invoice)
    {
        $company = $this->getCompany();
        if (!$company || $invoice->company_id !== $company->id) {
            abort(403);
        }

        return view('billing.invoice-detail', [
            'invoice' => $invoice,
            'company' => $company,
        ]);
    }

    /**
     * Checkout redirect (for Stripe — kept for future use).
     */
    public function checkout(Request $request, Plan $plan)
    {
        // If Stripe is configured, redirect to Stripe checkout
        if (config('services.stripe.secret')) {
            try {
                $gateway = app(\App\Services\Payment\PaymentGateway::class);
                $url = $gateway->createCheckoutSession($this->getCompany(), $plan);
                return redirect($url);
            } catch (\Exception $e) {
                // Fall through to manual subscribe
            }
        }

        // Otherwise, use manual subscription
        return $this->subscribe($request, $plan);
    }

    /**
     * Stripe portal (for Stripe — kept for future use).
     */
    public function portal()
    {
        if (config('services.stripe.secret')) {
            try {
                $gateway = app(\App\Services\Payment\PaymentGateway::class);
                $url = $gateway->createPortalSession($this->getCompany());
                return redirect($url);
            } catch (\Exception $e) {
                // Fall through
            }
        }

        return redirect()->route('billing.index');
    }

    /**
     * Stripe success callback.
     */
    public function success(Request $request)
    {
        return redirect()->route('billing.index')
            ->with('success', __('messages.subscription_updated'));
    }

    /**
     * Stripe cancel callback.
     */
    public function cancel()
    {
        return redirect()->route('billing.index')
            ->with('info', __('messages.checkout_cancelled'));
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Get usage statistics from the TENANT database.
     *
     * The Company model relationships (devices(), users()) can't be used
     * because Company is in the central DB with id=X, while tenant DB
     * tables reference their own local company_id (usually 1).
     * We query the tenant DB directly to get accurate counts.
     */
    private function getUsageStats(Company $company): array
    {
        $deviceCount = 0;
        $employeeCount = 0;

        try {
            // The tenant connection is configured by ResolveTenantFromSession middleware
            $deviceCount = DB::connection('tenant')->table('devices')
                ->whereNull('deleted_at')
                ->count();

            $employeeCount = DB::connection('tenant')->table('users')
                ->whereNull('deleted_at')
                ->count();
        } catch (\Exception $e) {
            Log::warning('Could not fetch tenant usage stats: ' . $e->getMessage());
        }

        $maxDevices = $company->max_devices ?: 1;
        $maxEmployees = $company->max_employees ?: 1;

        return [
            'devices_count' => $deviceCount,
            'max_devices' => $company->max_devices,
            'devices_percent' => $maxDevices > 0 ? min(100, (int)(($deviceCount / $maxDevices) * 100)) : 0,
            'employees_count' => $employeeCount,
            'max_employees' => $company->max_employees,
            'employees_percent' => $maxEmployees > 0 ? min(100, (int)(($employeeCount / $maxEmployees) * 100)) : 0,
        ];
    }

    /**
     * Sync the Tenant model with Company subscription state.
     *
     * The Tenant model (central DB `tenants` table) has its own plan_id
     * and status fields that must stay in sync with the Company record.
     */
    private function syncTenant(Company $company, ?Plan $plan = null): void
    {
        try {
            $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

            if (!$tenant) {
                $tenantId = session('tenant_id');
                if ($tenantId) {
                    $tenant = Tenant::on('mysql')->find($tenantId);
                }
            }

            if (!$tenant) {
                $tenant = Tenant::on('mysql')->where('subdomain', $company->subdomain)->first();
            }

            if ($tenant) {
                $updateData = [
                    'plan_id' => $plan ? $plan->id : $company->plan_id,
                    'status' => $company->stripe_subscription_status === 'cancelled'
                        ? 'cancelled'
                        : 'active',
                ];

                // Clear trial if Company cleared it
                if (!$company->trial_ends_at) {
                    $updateData['trial_ends_at'] = null;
                }

                Tenant::on('mysql')->where('id', $tenant->id)->update($updateData);
            }
        } catch (\Exception $e) {
            Log::warning('Could not sync tenant: ' . $e->getMessage());
        }
    }

    /**
     * Get the authenticated user's company from the CENTRAL database.
     *
     * The User model lives in the tenant DB and its company_id references the
     * tenant-local companies table, so we cannot use $user->company (which
     * queries the central DB with a mismatched ID). Instead we resolve the
     * Company via the Tenant record stored in the session.
     */
    private function getCompany(): ?Company
    {
        // 1. Try the currentTenant binding set by ResolveTenantFromSession middleware
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        if ($tenant) {
            // Match central Company by subdomain or database name
            $company = Company::where('subdomain', $tenant->subdomain)->first()
                    ?? Company::where('database', 'tenant' . $tenant->subdomain)->first()
                    ?? Company::where('email', $tenant->email)->first();

            if ($company) return $company;
        }

        // 2. Try tenant_id from session → look up Tenant, then find matching Company
        $tenantId = session('tenant_id');
        if ($tenantId) {
            $tenantRecord = \App\Models\Tenant::on('mysql')->find($tenantId);
            if ($tenantRecord) {
                $company = Company::where('subdomain', $tenantRecord->subdomain)->first()
                        ?? Company::where('email', $tenantRecord->email)->first();

                if ($company) return $company;
            }
        }

        // 3. Last resort: if there's only one company, use it
        if (Company::count() === 1) {
            return Company::first();
        }

        return null;
    }

    /**
     * Build subscription status info array.
     */
    private function getSubscriptionInfo(Company $company): array
    {
        $plan = $company->plan;
        $info = [
            'status' => 'none',
            'status_label' => '',
            'status_color' => 'danger',
            'plan_name' => $plan->name ?? __('messages.no_plan'),
            'is_trial' => false,
            'is_active' => false,
            'is_cancelled' => false,
            'is_expired' => false,
            'is_free' => false,
            'trial_days_remaining' => 0,
            'subscription_start' => null,
            'subscription_end' => null,
            'days_remaining' => 0,
            'can_renew' => false,
            'can_cancel' => false,
            'can_upgrade' => true,
        ];

        if (!$plan) {
            $info['status'] = 'no_plan';
            $info['status_label'] = __('messages.no_active_plan');
            $info['status_color'] = 'danger';
            return $info;
        }

        // Free plan
        if ($plan->isFree()) {
            $info['status'] = 'free';
            $info['status_label'] = __('messages.free_plan');
            $info['status_color'] = 'info';
            $info['is_free'] = true;
            $info['is_active'] = true;
            $info['can_cancel'] = false;
            return $info;
        }

        // Trial
        if ($company->onTrial()) {
            $info['status'] = 'trial';
            $info['status_label'] = __('messages.trial_active');
            $info['status_color'] = 'warning';
            $info['is_trial'] = true;
            $info['is_active'] = true;
            $info['trial_days_remaining'] = $company->trialDaysRemaining();
            $info['subscription_end'] = $company->trial_ends_at;
            $info['can_renew'] = false;
            $info['can_cancel'] = false;
            return $info;
        }

        // Active subscription
        if ($company->stripe_subscription_status === 'active') {
            $info['status'] = 'active';
            $info['status_label'] = __('messages.active_subscription');
            $info['status_color'] = 'success';
            $info['is_active'] = true;
            $info['subscription_end'] = $company->subscription_ends_at;
            $info['can_cancel'] = true;

            if ($company->subscription_ends_at) {
                $info['days_remaining'] = max(0, now()->diffInDays($company->subscription_ends_at, false));
                // Show renew button when less than 7 days remaining
                $info['can_renew'] = $info['days_remaining'] <= 7;
            }
            return $info;
        }

        // Cancelled (still within period)
        if ($company->stripe_subscription_status === 'cancelled') {
            if ($company->subscription_ends_at && $company->subscription_ends_at->isFuture()) {
                $info['status'] = 'cancelled_active';
                $info['status_label'] = __('messages.cancelled_active');
                $info['status_color'] = 'warning';
                $info['is_cancelled'] = true;
                $info['is_active'] = true;
                $info['subscription_end'] = $company->subscription_ends_at;
                $info['days_remaining'] = max(0, now()->diffInDays($company->subscription_ends_at, false));
                $info['can_renew'] = true;
                $info['can_cancel'] = false;
            } else {
                $info['status'] = 'expired';
                $info['status_label'] = __('messages.subscription_expired');
                $info['status_color'] = 'danger';
                $info['is_expired'] = true;
                $info['can_renew'] = true;
                $info['can_cancel'] = false;
            }
            return $info;
        }

        // Expired
        if ($company->subscription_ends_at && $company->subscription_ends_at->isPast()) {
            $info['status'] = 'expired';
            $info['status_label'] = __('messages.subscription_expired');
            $info['status_color'] = 'danger';
            $info['is_expired'] = true;
            $info['can_renew'] = true;
            $info['can_cancel'] = false;
            return $info;
        }

        return $info;
    }
}
