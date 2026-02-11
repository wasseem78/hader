<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class SystemController extends Controller
{
    public function index()
    {
        return view('super-admin.system.index', [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_ip' => request()->server('SERVER_ADDR') ?? '127.0.0.1',
            'user_agent' => request()->header('User-Agent'),
        ]);
    }
}
