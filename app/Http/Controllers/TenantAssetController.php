<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TenantAssetController extends Controller
{
    public function show($path)
    {
        // Security: Prevent directory traversal
        if (str_contains($path, '..')) {
            abort(403);
        }

        // Get current tenant from container
        $tenant = app('currentTenant');
        
        if (!$tenant) {
            abort(404);
        }

        // Build the full path to the tenant's file
        $tenantPath = storage_path('app/tenants/' . $tenant->uuid . '/public/' . $path);
        
        if (!file_exists($tenantPath)) {
            abort(404);
        }

        // Get mime type
        $mimeType = mime_content_type($tenantPath);
        
        return response()->file($tenantPath, [
            'Content-Type' => $mimeType,
        ]);
    }
}
