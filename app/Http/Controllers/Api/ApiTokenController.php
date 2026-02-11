<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiTokenController extends Controller
{
    public function index(Request $request, $tenant)
    {
        $tokens = ApiToken::where('company_id', $tenant)->get();
        return response()->json(['data' => $tokens]);
    }

    public function store(Request $request, $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'abilities' => 'nullable|array',
            'expires_at' => 'nullable|date',
        ]);

        $plainToken = Str::random(64);

        $token = ApiToken::create([
            'company_id' => $tenant,
            'name' => $validated['name'],
            'token' => hash('sha256', $plainToken),
            'abilities' => $validated['abilities'] ?? ['*'],
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        return response()->json([
            'data' => $token,
            'plain_token' => $plainToken, // Only shown once!
        ], 201);
    }

    public function destroy($tenant, $token)
    {
        $token = ApiToken::where('company_id', $tenant)->findOrFail($token);
        $token->delete();
        return response()->json(['message' => 'Token revoked']);
    }
}
