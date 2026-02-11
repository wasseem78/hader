<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $employees = User::where('company_id', $request->route('tenant'))
            ->whereHas('roles', fn($q) => $q->where('name', 'employee'))
            ->paginate(20);

        return response()->json($employees);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'employee_id' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
        ]);

        $employee = User::create([
            ...$validated,
            'company_id' => $request->route('tenant'),
            'password' => bcrypt('password'),
        ]);

        $employee->assignRole('employee');

        return response()->json(['data' => $employee], 201);
    }

    public function show(Request $request, $tenant, $employee)
    {
        $user = User::where('company_id', $tenant)->findOrFail($employee);
        return response()->json(['data' => $user]);
    }

    public function update(Request $request, $tenant, $employee)
    {
        $user = User::where('company_id', $tenant)->findOrFail($employee);
        $user->update($request->only(['name', 'employee_id', 'department', 'position']));
        return response()->json(['data' => $user]);
    }

    public function destroy($tenant, $employee)
    {
        $user = User::where('company_id', $tenant)->findOrFail($employee);
        $user->delete();
        return response()->json(['message' => 'Employee deleted']);
    }
}
