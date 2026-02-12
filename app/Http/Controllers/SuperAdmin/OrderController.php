<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\SubscriptionOrder;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * List all subscription orders with filters.
     */
    public function index(Request $request)
    {
        $query = SubscriptionOrder::with(['company', 'plan', 'previousPlan'])->latest();

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Search by company name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('company', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(15);

        // Stats
        $pendingCount = SubscriptionOrder::where('status', 'pending')->count();
        $approvedCount = SubscriptionOrder::where('status', 'approved')->count();
        $totalRevenue = SubscriptionOrder::where('status', 'approved')->sum('amount');

        return view('super-admin.orders.index', compact(
            'orders',
            'pendingCount',
            'approvedCount',
            'totalRevenue'
        ));
    }

    /**
     * Show order details.
     */
    public function show(SubscriptionOrder $order)
    {
        $order->load(['company', 'plan', 'previousPlan', 'invoice']);

        return view('super-admin.orders.show', compact('order'));
    }

    /**
     * Approve an order — activate the plan and create an invoice.
     */
    public function approve(Request $request, SubscriptionOrder $order)
    {
        if (!$order->isPending()) {
            return back()->with('error', app()->getLocale() == 'ar'
                ? 'لا يمكن الموافقة على هذا الطلب.'
                : 'This order cannot be approved.');
        }

        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::connection('mysql')->beginTransaction();

            $company = $order->company;
            $plan = $order->plan;

            // Calculate subscription period
            $startDate = now();
            if ($order->type === 'renewal' && $company->subscription_ends_at && $company->subscription_ends_at->isFuture()) {
                $startDate = $company->subscription_ends_at;
            }

            $endDate = $order->billing_cycle === 'yearly'
                ? $startDate->copy()->addYear()
                : $startDate->copy()->addMonth();

            // Update company subscription
            $company->update([
                'plan_id' => $plan->id,
                'stripe_subscription_status' => 'active',
                'subscription_ends_at' => $endDate,
                'max_devices' => $plan->max_devices,
                'max_employees' => $plan->max_employees,
                'trial_ends_at' => null,
            ]);

            // Create invoice
            $invoice = Invoice::create([
                'company_id' => $company->id,
                'invoice_number' => 'INV-' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'plan_name' => $plan->name,
                'billing_cycle' => $order->billing_cycle,
                'amount' => $order->amount,
                'currency' => $order->currency,
                'status' => 'paid',
                'invoice_date' => now(),
                'paid_at' => now(),
                'period_start' => $startDate,
                'period_end' => $endDate,
            ]);

            // Update order
            $order->update([
                'status' => 'approved',
                'approved_by' => auth()->guard('central')->id(),
                'approved_at' => now(),
                'admin_notes' => $request->input('admin_notes'),
                'invoice_id' => $invoice->id,
            ]);

            // Sync tenant record
            $this->syncTenant($company, $plan);

            DB::connection('mysql')->commit();

            $msg = app()->getLocale() == 'ar'
                ? 'تمت الموافقة على الطلب وتفعيل الاشتراك بنجاح.'
                : 'Order approved and subscription activated successfully.';

            return redirect()->route('super-admin.orders.show', $order)
                ->with('success', $msg);

        } catch (\Exception $e) {
            DB::connection('mysql')->rollBack();
            Log::error('Order approval error: ' . $e->getMessage());

            return back()->with('error', app()->getLocale() == 'ar'
                ? 'حدث خطأ أثناء الموافقة على الطلب.'
                : 'An error occurred while approving the order.');
        }
    }

    /**
     * Reject an order.
     */
    public function reject(Request $request, SubscriptionOrder $order)
    {
        if (!$order->isPending()) {
            return back()->with('error', app()->getLocale() == 'ar'
                ? 'لا يمكن رفض هذا الطلب.'
                : 'This order cannot be rejected.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $order->update([
            'status' => 'rejected',
            'approved_by' => auth()->guard('central')->id(),
            'approved_at' => now(),
            'rejection_reason' => $request->input('rejection_reason'),
            'admin_notes' => $request->input('admin_notes'),
        ]);

        $msg = app()->getLocale() == 'ar'
            ? 'تم رفض الطلب.'
            : 'Order has been rejected.';

        return redirect()->route('super-admin.orders.show', $order)
            ->with('success', $msg);
    }

    /**
     * Sync tenant record with company subscription data.
     */
    private function syncTenant(Company $company, Plan $plan): void
    {
        try {
            $tenant = Tenant::on('mysql')->where('subdomain', $company->subdomain)->first();

            if ($tenant) {
                Tenant::on('mysql')->where('id', $tenant->id)->update([
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'trial_ends_at' => null,
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Could not sync tenant after order approval: ' . $e->getMessage());
        }
    }
}
