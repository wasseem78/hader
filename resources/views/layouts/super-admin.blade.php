<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin') - System Administration</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --danger: #ef4444;
            --success: #10b981;
            --warning: #f59e0b;
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-tertiary: #334155;
            --border-color: rgba(148, 163, 184, 0.1);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --sidebar-width: 250px;
        }

        body {
            font-family: {!! "'Tajawal', -apple-system, BlinkMacSystemFont, sans-serif" !!};
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            font-size: 14px;
            line-height: 1.6;
        }

        /* RTL Support */
        [dir="rtl"] .sidebar { right: 0; left: auto; border-right: none; border-left: 1px solid var(--border-color); }
        [dir="rtl"] .main-wrapper { margin-right: var(--sidebar-width); margin-left: 0; }
        [dir="rtl"] th, [dir="rtl"] td { text-align: right; }
        [dir="rtl"] .action-btns { flex-direction: row-reverse; }
        [dir="rtl"] .nav-item svg { margin-left: 10px; margin-right: 0; }
        [dir="rtl"] .btn svg { margin-left: 6px; margin-right: 0; }
        [dir="rtl"] .company-info { flex-direction: row-reverse; }
        [dir="rtl"] .stat-card { flex-direction: row-reverse; }
        [dir="rtl"] .user-card { flex-direction: row-reverse; }
        [dir="rtl"] .brand { flex-direction: row-reverse; }
        [dir="rtl"] .alert { flex-direction: row-reverse; }
        [dir="rtl"] .input-group .form-control { border-radius: 0 8px 8px 0; }
        [dir="rtl"] .input-addon { border-radius: 8px 0 0 8px; border-left: 1px solid var(--border-color); border-right: none; }

        /* Language Switcher */
        .lang-switcher {
            display: flex;
            align-items: center;
            gap: 4px;
            background: var(--bg-tertiary);
            border-radius: 8px;
            padding: 4px;
        }
        .lang-btn {
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
            border: none;
            background: transparent;
            color: var(--text-muted);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        .lang-btn:hover {
            color: var(--text-primary);
        }
        .lang-btn.active {
            background: var(--primary);
            color: white;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--bg-secondary);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary), #8b5cf6);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .brand-text {
            font-weight: 600;
            font-size: 15px;
        }

        .brand-badge {
            font-size: 9px;
            background: var(--danger);
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .nav-section {
            padding: 16px 12px;
            flex: 1;
            overflow-y: auto;
        }

        .nav-label {
            font-size: 10px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 8px 12px;
            margin-bottom: 4px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 2px;
            transition: all 0.2s ease;
            font-size: 13px;
            font-weight: 500;
        }

        .nav-item:hover {
            background: rgba(99, 102, 241, 0.1);
            color: var(--text-primary);
        }

        .nav-item.active {
            background: rgba(99, 102, 241, 0.15);
            color: var(--primary);
        }

        .nav-item svg {
            width: 18px;
            height: 18px;
            opacity: 0.7;
        }

        .nav-item.active svg {
            opacity: 1;
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--border-color);
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: rgba(15, 23, 42, 0.5);
            border-radius: 10px;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary), #8b5cf6);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        .user-info {
            flex: 1;
            min-width: 0;
        }

        .user-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .user-role {
            font-size: 11px;
            color: var(--text-muted);
        }

        .logout-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        /* Main Content */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        .top-bar {
            position: sticky;
            top: 0;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 50;
        }

        .page-title {
            font-size: 16px;
            font-weight: 600;
        }

        .top-bar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .date-display {
            font-size: 12px;
            color: var(--text-muted);
        }

        .main-content {
            padding: 24px;
        }

        /* Alert Messages */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #34d399;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #f87171;
        }

        /* Cards */
        .card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
        }

        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 14px;
            font-weight: 600;
        }

        .card-body {
            padding: 20px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 500;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-primary);
        }

        .btn-secondary:hover {
            background: #475569;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-sm {
            padding: 6px 10px;
            font-size: 12px;
        }

        .btn svg {
            width: 16px;
            height: 16px;
        }

        /* Forms */
        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            font-size: 13px;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        /* Tables */
        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 12px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: rgba(15, 23, 42, 0.5);
            border-bottom: 1px solid var(--border-color);
        }

        td {
            padding: 12px 16px;
            font-size: 13px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-secondary);
        }

        tbody tr:hover {
            background: rgba(99, 102, 241, 0.05);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 6px;
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.1);
            color: #34d399;
        }

        .badge-warning {
            background: rgba(245, 158, 11, 0.1);
            color: #fbbf24;
        }

        .badge-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
        }

        .badge-info {
            background: rgba(99, 102, 241, 0.1);
            color: #818cf8;
        }

        /* Stat Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        @media (max-width: 1200px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: 1fr; }
        }

        .stat-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 16px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
        }

        .stat-content h3 {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1;
        }

        .stat-change {
            font-size: 11px;
            margin-top: 6px;
        }

        .stat-change.positive { color: var(--success); }
        .stat-change.negative { color: var(--danger); }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon svg {
            width: 20px;
            height: 20px;
        }

        .stat-icon.purple { background: rgba(139, 92, 246, 0.1); color: #a78bfa; }
        .stat-icon.green { background: rgba(16, 185, 129, 0.1); color: #34d399; }
        .stat-icon.blue { background: rgba(59, 130, 246, 0.1); color: #60a5fa; }
        .stat-icon.yellow { background: rgba(245, 158, 11, 0.1); color: #fbbf24; }

        /* Grid Layout */
        .grid-2 {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }

        @media (max-width: 1024px) {
            .grid-2 { grid-template-columns: 1fr; }
        }

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .page-header-title h1 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .page-header-title p {
            font-size: 13px;
            color: var(--text-muted);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
        }

        .empty-state svg {
            width: 48px;
            height: 48px;
            margin-bottom: 12px;
            opacity: 0.5;
        }

        /* Progress Bar */
        .progress {
            height: 6px;
            background: var(--bg-primary);
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), #8b5cf6);
            border-radius: 3px;
        }

        /* Action Buttons */
        .action-btns {
            display: flex;
            gap: 6px;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            background: transparent;
            color: var(--text-muted);
        }

        .action-btn:hover {
            background: var(--bg-tertiary);
            color: var(--text-primary);
        }

        .action-btn.danger:hover {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .action-btn svg {
            width: 16px;
            height: 16px;
        }

        /* Company Avatar */
        .company-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary), #8b5cf6);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            color: white;
        }

        .company-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .company-details h4 {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .company-details p {
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Utilities */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mt-4 { margin-top: 16px; }
        .mb-4 { margin-bottom: 16px; }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        @media (max-width: 640px) {
            .form-grid { grid-template-columns: 1fr; }
        }

        /* Input Group */
        .input-group {
            display: flex;
        }

        .input-group .form-control {
            border-radius: 8px 0 0 8px;
        }

        .input-addon {
            padding: 10px 12px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-left: none;
            border-radius: 0 8px 8px 0;
            font-size: 12px;
            color: var(--text-muted);
            white-space: nowrap;
        }

        /* Checkbox */
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 13px;
            color: var(--text-secondary);
        }

        .checkbox-label input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--primary);
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--bg-tertiary); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }

        /* Link */
        a.link {
            color: var(--primary);
            text-decoration: none;
        }
        a.link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="brand">
                <div class="brand-icon">⚡</div>
                <div>
                    <div class="brand-text">Attendance</div>
                    <span class="brand-badge">Admin</span>
                </div>
            </div>
        </div>
        
        <nav class="nav-section">
            <div class="nav-label">{{ app()->getLocale() == 'ar' ? 'القائمة الرئيسية' : 'Main Menu' }}</div>
            <a href="{{ route('super-admin.dashboard') }}" class="nav-item {{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                {{ app()->getLocale() == 'ar' ? 'لوحة التحكم' : 'Dashboard' }}
            </a>
            
            <div class="nav-label" style="margin-top: 16px;">{{ app()->getLocale() == 'ar' ? 'الإدارة' : 'Management' }}</div>
            <a href="{{ route('super-admin.tenants.index') }}" class="nav-item {{ request()->routeIs('super-admin.tenants.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                {{ app()->getLocale() == 'ar' ? 'المشتركين' : 'Tenants' }}
            </a>
            <a href="{{ route('super-admin.plans.index') }}" class="nav-item {{ request()->routeIs('super-admin.plans.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                {{ app()->getLocale() == 'ar' ? 'الخطط' : 'Plans' }}
            </a>
            @php $pendingOrdersCount = \App\Models\SubscriptionOrder::where('status', 'pending')->count(); @endphp
            <a href="{{ route('super-admin.orders.index') }}" class="nav-item {{ request()->routeIs('super-admin.orders.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                {{ app()->getLocale() == 'ar' ? 'الطلبات' : 'Orders' }}
                @if($pendingOrdersCount > 0)
                <span style="background: var(--danger); color: white; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 10px; margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: auto;">{{ $pendingOrdersCount }}</span>
                @endif
            </a>
            
            <div class="nav-label" style="margin-top: 16px;">{{ app()->getLocale() == 'ar' ? 'النظام' : 'System' }}</div>
            <a href="{{ route('super-admin.system.index') }}" class="nav-item {{ request()->routeIs('super-admin.system.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                {{ app()->getLocale() == 'ar' ? 'حالة النظام' : 'System Status' }}
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar">{{ substr(auth()->guard('central')->user()->name ?? 'A', 0, 1) }}</div>
                <div class="user-info">
                    <div class="user-name">{{ auth()->guard('central')->user()->name ?? 'Admin' }}</div>
                    <div class="user-role">Super Admin</div>
                </div>
                <form action="{{ route('super-admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-btn" title="Logout">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-wrapper">
        <header class="top-bar">
            <h1 class="page-title">@yield('header', app()->getLocale() == 'ar' ? 'لوحة التحكم' : 'Dashboard')</h1>
            <div class="top-bar-right">
                @hasSection('header-actions')
                    <div style="display: flex; gap: 10px; align-items: center;">
                        @yield('header-actions')
                    </div>
                @endif
                
                <!-- Language Switcher -->
                <div class="lang-switcher">
                    <a href="{{ route('super-admin.lang', 'ar') }}" class="lang-btn {{ app()->getLocale() == 'ar' ? 'active' : '' }}">العربية</a>
                    <a href="{{ route('super-admin.lang', 'en') }}" class="lang-btn {{ app()->getLocale() == 'en' ? 'active' : '' }}">English</a>
                </div>
                
                <span class="date-display">{{ app()->getLocale() == 'ar' ? now()->locale('ar')->translatedFormat('l، j F Y') : now()->format('l, F j, Y') }}</span>
            </div>
        </header>

        <main class="main-content">
            @if(session('success'))
                <div class="alert alert-success">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>
