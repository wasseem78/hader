<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::orderBy('sort_order')->get();
        return view('super-admin.plans.index', ['plans' => $plans]);
    }

    public function create()
    {
        return view('super-admin.plans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans',
            'stripe_price_monthly_id' => 'nullable|string',
            'stripe_price_yearly_id' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'max_employees' => 'required|integer|min:0',
            'max_devices' => 'required|integer|min:0',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ]);

        Plan::create($validated);
        return redirect()->route('super-admin.plans.index')->with('success', 'Plan created successfully.');
    }

    public function edit(Plan $plan)
    {
        return view('super-admin.plans.edit', ['plan' => $plan]);
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans,slug,' . $plan->id,
            'stripe_price_monthly_id' => 'nullable|string',
            'stripe_price_yearly_id' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'max_employees' => 'required|integer|min:0',
            'max_devices' => 'required|integer|min:0',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $plan->update($validated);
        return redirect()->route('super-admin.plans.index')->with('success', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();
        return redirect()->route('super-admin.plans.index')->with('success', 'Plan deleted successfully.');
    }
}
