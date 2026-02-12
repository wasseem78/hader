<?php

// =============================================================================
// Billing Controller - Subscription Management (Production-Ready)
// Orders go through a checkout → pending approval workflow.
// Super admin must approve before a plan is activated.
// =============================================================================

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\SubscriptionOrder;
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

        $subscriptionInfo = $this->getSubscriptionInfo($company);
        $usageStats = $this->getUsageStats($company);

        // Get pending orders for this company
        $pendingOrders = SubscriptionOrder::where('company_id', $company->id)
            ->where('status', 'pending')
            ->with('plan')
            ->latest()
            ->get();

        return view('billing.index', [
            'company' => $company,
            'plans' => $plans,
            'currentPlan' => $currentPlan,
            'subscriptionInfo' => $subscriptionInfo,
            'usageStats' => $usageStats,
            'pendingOrders' => $pendingOrders,
        ]);
    }

    /**
     * Show checkout page for plan subscription / upgrade.
     */
    public function showCheckout(Request $request, Plan $plan)
    {
        $company = $this->getCompany();
        if (!$company) {
            return redirect()->route('dashboard')->with('error', __('messages.no_company'));
        }

        // Free plans don't need checkout
        if ($plan->isFree()) {
            return $this->activateFreePlan($company, $plan);
        }

        $billingCycle = $request->input('billing_cycle', 'monthly');
        $price = $billingCycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;
        $currentPlan = $company->plan;

        // Determine order type
        $orderType = 'new';
        if ($currentPlan && !$currentPlan->isFree()) {
            if ($plan->price_monthly > ($currentPlan->price_monthly ?? 0)) {
                $orderType = 'upgrade';
            } elseif ($plan->price_monthly < ($currentPlan->price_monthly ?? 0)) {
                $orderType = 'downgrade';
            }
        }

        // Check if there's already a pending order for this plan
        $existingPending = SubscriptionOrder::where('company_id', $company->id)
            ->where('plan_id', $plan->id)
            ->where('status', 'pending')
            ->first();

        return view('billing.checkout', [
            'company' => $company,
            'plan' => $plan,
            'currentPlan' => $currentPlan,
            'billingCycle' => $billingCycle,
            'price' => $price,
            'orderType' => $orderType,
            'existingPending' => $existingPending,
        ]);
    }

    /**
     * Show checkout page for renewal of current plan.
     */
    public function showRenewCheckout(Request $request)
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
        $price = $billingCycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;

        $existingPending = SubscriptionOrder::where('company_id', $company->id)
            ->where('plan_id', $plan->id)
            ->where('type', 'renewal')
            ->where('status', 'pending')
            ->first();

        return view('billing.checkout', [
            'company' => $company,
            'plan' => $plan,
            'currentPlan' => $plan,
            'billingCycle' => $billingCycle,
            'price' => $price,
            'orderType' => 'renewal',
            'existingPending' => $existingPending,
        ]);
    }

    /**
     * Submit a subscription order (creates pending order).
     * Plan is NOT activated until super admin approves.
     */
    public function subscribe(Request $request, Plan $plan)
    {
        $company = $this->getCompany();
        if (!$company) {
            return redirect()->route('dashboard')->with('error', __('messages.no_company'));
        }

        // Free plans activate immediately
        if ($plan->isFree()) {
            return $this->activateFreePlan($company, $plan);
        }

        // Validate downgrade limits
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

        // Check for existing pending order for same plan
        $existingPending = SubscriptionOrder::where('company_id', $company->id)
            ->where('plan_id', $plan->id)
            ->where('status', 'pending')
            ->first();

        if ($existingPending) {
            return redirect()->route('billing.order.show', $existingPending)
                ->with('info', __('messages.order_already_pending'));
        }

        $billingCycle = $request->input('billing_cycle', 'monthly');
        $price = $billingCycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;
        $currentPlan = $company->plan;

        // Determine order type
        $orderType = 'new';
        if ($currentPlan && !$currentPlan->isFree()) {
            if ($plan->price_monthly > ($currentPlan->price_monthly ?? 0)) {
                $orderType = 'upgrade';
            } elseif ($plan->price_monthly < ($currentPlan->price_monthly ?? 0)) {
                $orderType = 'downgrade';
            }
        }

        try {
            $order = SubscriptionOrder::create([
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'type' => $orderType,
                'billing_cycle' => $billingCycle,
                'currency' => $plan->currency ?? 'USD',
                'amount' => $price,
                'previous_plan_id' => $currentPlan?->id,
                'status' => 'pending',
                'customer_notes' => $request->input('customer_notes'),
                'payment_reference' => $request->input('payment_reference'),
            ]);

            return redirect()->route('billing.order.show', $order)
                ->with('success', __('messages.order_submitted'));

        } catch (\Exception $e) {
            Log::error('Subscription order error: ' . $e->getMessage());
            return back()->with('error', __('messages.subscription_error'));
        }
    }

    /**
     * Submit a renewal order (creates pending order).
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

        // Check for existing pending renewal
        $existingPending = SubscriptionOrder::where('company_id', $company->id)
            ->where('plan_id', $plan->id)
            ->where('type', 'renewal')
            ->where('status', 'pending')
            ->first();

        if ($existingPending) {
            return redirect()->route('billing.order.show', $existingPending)
                ->with('info', __('messages.order_already_pending'));
        }

        $billingCycle = $request->input('billing_cycle', 'monthly');
        $price = $billingCycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;

        try {
            $order = SubscriptionOrder::create([
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'type' => 'renewal',
                'billing_cycle' => $billingCycle,
                'currency' => $plan->currency ?? 'USD',
                'amount' => $price,
                'previous_plan_id' => $plan->id,
                'status' => 'pending',
                'customer_notes' => $request->input('customer_notes'),
                'payment_reference' => $request->input('payment_reference'),
            ]);

            return redirect()->route('billing.order.show', $order)
                ->with('success', __('messages.order_submitted'));

        } catch (\Exception $e) {
            Log::error('Renewal order error: ' . $e->getMessage());
            return back()->with('error', __('messages.subscription_error'));
        }
    }

    /**
     * Show a specific order details (for the tenant user).
     */
    public function showOrder(SubscriptionOrder $order)
    {
        $company = $this->getCompany();
        if (!$company || $order->company_id !== $company->id) {
            abort(403);
        }

        return view('billing.order-detail', [
            'order' => $order->load('plan', 'previousPlan'),
            'company' => $company,
        ]);
    }

    /**
     * Show order history.
     */
    public function orders()
    {
        $company = $this->getCompany();
        if (!$company) {
            return redirect()->route('dashboard')->with('error', __('messages.no_company'));
        }

        $orders = SubscriptionOrder::where('company_id', $company->id)
            ->with('plan')
            ->latest()
            ->paginate(15);

        return view('billing.orders', [
            'company' => $company,
            'orders' => $orders,
            'currentPlan' => $company->plan,
        ]);
    }

    /**
     * Cancel a pending order (by the tenant user).
     */
    public function cancelOrder(SubscriptionOrder $order)
    {
        $company = $this->getCompany();
        if (!$company || $order->company_id !== $company->id) {
            abort(403);
        }

        if (!$order->isPending()) {
            return back()->with('error', __('messages.order_not_cancellable'));
        }

        $order->update(['status' => 'cancelled']);

        return redirect()->route('billing.index')
            ->with('success', __('messages.order_cancelled'));
    }

    /**
     * Activate free plan immediately (no approval needed).
     */
    private function activateFreePlan(Company $company, Plan $plan)
    {
        try {
            DB::connection('mysql')->beginTransaction();

            $company->update([
                'plan_id' => $plan->id,
                'stripe_subscription_status' => 'free',
                'subscription_ends_at' => null,
                'max_devices' => $plan->max_devices,
                'max_employees' => $plan->max_employees,
                'trial_ends_at' => null,
            ]);

            $this->syncTenant($company, $plan);

            DB::connection('mysql')->commit();

            return redirect()->route('billing.index')
                ->with('success', __('messages.subscription_updated'));

        } catch (\Exception $e) {
            DB::connection('mysql')->rollBack();
            Log::error('Free plan activation error: ' . $e->getMessage());
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
            ]);

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

    // =========================================================================
    // Helpers
    // =========================================================================

    private function getUsageStats(Company $company): array
    {
        $deviceCount = 0;
        $employeeCount = 0;

        try {
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

                if (!$company->trial_ends_at) {
                    $updateData['trial_ends_at'] = null;
                }

                Tenant::on('mysql')->where('id', $tenant->id)->update($updateData);
            }
        } catch (\Exception $e) {
            Log::warning('Could not sync tenant: ' . $e->getMessage());
        }
    }

    private function getCompany(): ?Company
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        if ($tenant) {
            $company = Company::where('subdomain', $tenant->subdomain)->first()
                    ?? Company::where('database', 'tenant' . $tenant->subdomain)->first()
                    ?? Company::where('email', $tenant->email)->first();

            if ($company) return $company;
        }

        $tenantId = session('tenant_id');
        if ($tenantId) {
            $tenantRecord = Tenant::on('mysql')->find($tenantId);
            if ($tenantRecord) {
                $company = Company::where('subdomain', $tenantRecord->subdomain)->first()
                        ?? Company::where('email', $tenantRecord->email)->first();

                if ($company) return $company;
            }
        }

        if (Company::count() === 1) {
            return Company::first();
        }

        return null;
    }

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
            'is_pending' => false,
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

        // Check for pending orders
        $hasPendingOrder = SubscriptionOrder::where('company_id', $company->id)
            ->where('status', 'pending')
            ->exists();
        $info['is_pending'] = $hasPendingOrder;

        if ($plan->isFree()) {
            $info['status'] = 'free';
            $info['status_label'] = __('messages.free_plan');
            $info['status_color'] = 'info';
            $info['is_free'] = true;
            $info['is_active'] = true;
            $info['can_cancel'] = false;
            return $info;
        }

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

        if ($company->stripe_subscription_status === 'active') {
            $info['status'] = 'active';
            $info['status_label'] = __('messages.active_subscription');
            $info['status_color'] = 'success';
            $info['is_active'] = true;
            $info['subscription_end'] = $company->subscription_ends_at;
            $info['can_cancel'] = true;

            if ($company->subscription_ends_at) {
                $info['days_remaining'] = max(0, now()->diffInDays($company->subscription_ends_at, false));
                $info['can_renew'] = $info['days_remaining'] <= 7;
            }
            return $info;
        }

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
