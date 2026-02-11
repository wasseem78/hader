<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            @if(auth()->user()->company && auth()->user()->company->logo)
                <img src="{{ route('tenant.storage', ['path' => auth()->user()->company->logo]) }}" alt="Logo" style="height: 40px; width: auto; max-width: 150px; border-radius: 8px; object-fit: contain;">
            @else
                <div class="logo-icon">✨</div>
                <div class="app-name">{{ auth()->user()->company?->name ?? __('messages.company_name') }}</div>
            @endif
        </div>
        
        <nav class="nav-menu">
            @if(auth()->user()->hasRole('super-admin'))
                <!-- Super Admin Menu -->
                <a href="{{ route('super-admin.dashboard') }}" class="nav-item {{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">
                    <span class="nav-icon">📊</span> Dashboard
                </a>
                <a href="{{ route('super-admin.tenants.index') }}" class="nav-item {{ request()->routeIs('super-admin.tenants.*') ? 'active' : '' }}">
                    <span class="nav-icon">🏢</span> Tenants
                </a>
                <a href="{{ route('super-admin.plans.index') }}" class="nav-item {{ request()->routeIs('super-admin.plans.*') ? 'active' : '' }}">
                    <span class="nav-icon">📝</span> Plans
                </a>
                <a href="{{ route('super-admin.system.index') }}" class="nav-item {{ request()->routeIs('super-admin.system.index') ? 'active' : '' }}">
                    <span class="nav-icon">🖥️</span> System
                </a>
            @else
                <!-- Tenant Menu -->
                <a href="/dashboard" class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
                    <span class="nav-icon">📊</span> {{ __('messages.dashboard') }}
                </a>
                <a href="/devices" class="nav-item {{ request()->is('devices*') ? 'active' : '' }}">
                    <span class="nav-icon">📱</span> {{ __('messages.devices') }}
                </a>
                <a href="/employees" class="nav-item {{ request()->is('employees*') ? 'active' : '' }}">
                    <span class="nav-icon">👥</span> {{ __('messages.employees') }}
                </a>
                <a href="/attendance" class="nav-item {{ request()->is('attendance*') ? 'active' : '' }}">
                    <span class="nav-icon">⏰</span> {{ __('messages.attendance') }}
                </a>
                <a href="/reports" class="nav-item {{ request()->is('reports*') ? 'active' : '' }}">
                    <span class="nav-icon">📈</span> {{ __('messages.reports') }}
                </a>
                <a href="/admin/shifts" class="nav-item {{ request()->is('admin/shifts*') ? 'active' : '' }}">
                    <span class="nav-icon">📅</span> {{ __('messages.shifts') }}
                </a>
                <a href="/billing" class="nav-item {{ request()->is('billing*') ? 'active' : '' }}">
                    <span class="nav-icon">💳</span> {{ __('messages.billing') }}
                </a>
                <a href="/admin/settings" class="nav-item {{ request()->is('admin/settings*') ? 'active' : '' }}">
                    <span class="nav-icon">⚙️</span> {{ __('messages.settings') }}
                </a>
            @endif
        </nav>

        <div class="user-profile">
            <div class="lang-switch">
                <a href="{{ route('lang.switch', ['locale' => 'ar']) }}" class="lang-btn {{ app()->getLocale() == 'ar' ? 'active' : '' }}">العربية</a>
                <a href="{{ route('lang.switch', ['locale' => 'en']) }}" class="lang-btn {{ app()->getLocale() == 'en' ? 'active' : '' }}">English</a>
            </div>

            <div class="user-card">
                <div class="avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                <div class="user-info" style="flex: 1;">
                    <h4>{{ auth()->user()->name }}</h4>
                    <p>{{ auth()->user()->email }}</p>
                </div>
                @if(auth()->user()->hasRole('super-admin'))
                    <form action="{{ route('super-admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="logout-btn" title="{{ __('messages.logout') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        </button>
                    </form>
                @else
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="logout-btn" title="{{ __('messages.logout') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        @hasSection('header')
            <div class="page-header">
                <div class="page-title">
                    @yield('header')
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>

    @yield('scripts')
</body>
</html>
