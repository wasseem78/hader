<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Tenant::latest();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subdomain', 'like', "%{$search}%");
            });
        }

        $tenants = $query->paginate(10);
        return view('super-admin.tenants.index', compact('tenants'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $plans = Plan::where('is_active', true)->get();
        return view('super-admin.tenants.create', compact('plans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, \App\Tenancy\Services\TenantProvisioner $provisioner)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tenants',
            'email' => 'required|email|max:255',
            'subdomain' => 'required|string|max:50|unique:tenants|alpha_dash',
            'plan_id' => 'required|exists:plans,id',
        ]);

        try {
            $tenant = $provisioner->provision([
                'name' => $request->name,
                'email' => $request->email,
                'subdomain' => $request->subdomain,
                'plan_id' => $request->plan_id,
            ]);

            return redirect()->route('super-admin.tenants.index')
                ->with('success', 'Tenant provisioned successfully. Database created and migrated.');
        } catch (\Exception $e) {
            return back()->with('error', 'Provisioning failed: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $tenant)
    {
        return view('super-admin.tenants.show', compact('tenant'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $tenant)
    {
        $plans = Plan::where('is_active', true)->get();
        return view('super-admin.tenants.edit', compact('tenant', 'plans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $tenant)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:companies,name,' . $tenant->id,
            'email' => 'required|email|max:255',
            'plan_id' => 'required|exists:plans,id',
            'domain' => 'nullable|string|unique:companies,domain,' . $tenant->id,
        ]);

        $tenant->update($request->all());

        return redirect()->route('super-admin.tenants.index')
            ->with('success', 'Tenant updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * Impersonate the tenant admin.
     */
    public function impersonate(\App\Models\Tenant $tenant)
    {
        // Generate a token payload with tenant ID
        $payload = [
            'tenant_id' => $tenant->id,
            'email' => $tenant->email,
            'timestamp' => now()->timestamp,
            'expires_at' => now()->addMinutes(5)->timestamp,
        ];
        
        $token = \Illuminate\Support\Facades\Crypt::encryptString(json_encode($payload));
        
        // Since we no longer use subdomains, redirect to main domain magic-login
        $url = url('/magic-login?token=' . $token);

        return redirect($url);
    }

    public function destroy(\App\Models\Tenant $tenant)
    {
        $tenant->delete();
        return redirect()->route('super-admin.tenants.index')
            ->with('success', 'Tenant deleted successfully.');
    }
}
