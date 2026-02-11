<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\InviteUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        // List users who are NOT just employees (i.e., admins, managers)
        $users = User::where('company_id', auth()->user()->company_id)
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['company-admin', 'manager']);
            })
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::whereIn('name', ['company-admin', 'manager'])->get();
        return view('admin.users.invite', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make(Str::random(16)), // Temporary random password
            'company_id' => auth()->user()->company_id,
            'is_active' => true,
        ]);

        $user->assignRole($request->role);

        $user->notify(new InviteUser($user));

        return redirect()->route('admin.users.index')->with('success', 'Invitation sent successfully!');
    }

    public function acceptInvite(Request $request, User $user)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired invitation link.');
        }

        return view('auth.accept-invite', compact('user'));
    }

    public function storePassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|confirmed|min:8',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
            'email_verified_at' => now(),
        ]);

        auth()->login($user);

        return redirect()->route('dashboard')->with('success', 'Account activated successfully!');
    }
}
