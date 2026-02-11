<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        $company = $this->getTenantCompany();
        $settings = is_string($company->settings ?? null)
            ? json_decode($company->settings, true) ?? []
            : ($company->settings ?? []);

        return view('admin.settings', [
            'company' => $company,
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'settings' => 'array',
        ]);

        $company = $this->getTenantCompany();
        
        $data = [
            'name' => $request->company_name,
            'address' => $request->address,
            'phone' => $request->phone,
            'website' => $request->website,
            'settings' => json_encode($request->input('settings', [])),
            'updated_at' => now(),
        ];

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $data['logo'] = $path;
        }

        DB::connection('tenant')->table('companies')
            ->where('id', $company->id)
            ->update($data);

        return back()->with('success', __('messages.settings_saved'));
    }

    /**
     * Get or create the company record in the TENANT database.
     *
     * The Company model uses CentralConnection (central DB), but the users
     * table FK references the tenant DB's own companies table.
     * We must work with the tenant's companies table directly.
     */
    private function getTenantCompany()
    {
        $user = auth()->user();

        // If user already has a company_id, try to fetch from the tenant DB
        if ($user->company_id) {
            $company = DB::connection('tenant')->table('companies')
                ->where('id', $user->company_id)
                ->first();

            if ($company) {
                return $company;
            }
        }

        // Look for any existing company in the tenant DB
        $company = DB::connection('tenant')->table('companies')->first();

        if (!$company) {
            // Create a company in the tenant DB, pulling info from central if available
            $centralCompany = \App\Models\Company::find(session('tenant_id'));
            
            $companyId = DB::connection('tenant')->table('companies')->insertGetId([
                'name' => $centralCompany->name ?? 'My Company',
                'email' => $centralCompany->email ?? $user->email,
                'is_active' => true,
                'max_devices' => $centralCompany->max_devices ?? 5,
                'max_employees' => $centralCompany->max_employees ?? 10,
                'max_users' => $centralCompany->max_users ?? 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $company = DB::connection('tenant')->table('companies')
                ->where('id', $companyId)
                ->first();
        }

        // Link user to the tenant company if not already linked
        if ($user->company_id != $company->id) {
            DB::connection('tenant')->table('users')
                ->where('id', $user->id)
                ->update(['company_id' => $company->id, 'updated_at' => now()]);
        }

        return $company;
    }
}
