<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistics
        $totalTenants = Company::count();
        $activeTenants = Company::where('is_active', true)->count();
        $trialTenants = Company::where('trial_ends_at', '>', now())->count();
        
        // Revenue Estimation (Monthly)
        $monthlyRevenue = Company::where('companies.is_active', true)
            ->join('plans', 'companies.plan_id', '=', 'plans.id')
            ->sum('plans.price_monthly');

        // Recent Tenants
        $recentTenants = Company::with('plan')->latest()->take(5)->get();

        // Plan Distribution
        $planDistribution = Company::select('plan_id', DB::raw('count(*) as total'))
            ->groupBy('plan_id')
            ->with('plan')
            ->get();

        return view('super-admin.dashboard', compact(
            'totalTenants',
            'activeTenants',
            'trialTenants',
            'monthlyRevenue',
            'recentTenants',
            'planDistribution'
        ));
    }
}
