<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name')) - {{ app()->getLocale() == 'ar' ? 'نظام حاضر' : 'Hadir System' }}</title>
    @include('partials.seo-meta')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if(app()->getLocale() == 'ar')
        <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    @else
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    @endif
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --primary: #6366f1;
            --primary-light: #818cf8;
            --primary-dark: #4f46e5;
            --secondary: #f59e0b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg-dark: #0f172a;
            --bg-card: #1e293b;
            --bg-hover: #334155;
            --border-color: rgba(255, 255, 255, 0.1);
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #64748b;
            --sidebar-width: 260px;
            --glass-bg: rgba(30, 41, 59, 0.8);
            --glass-border: rgba(255, 255, 255, 0.08);
        }

        body {
            font-family: {!! app()->getLocale() == 'ar' ? "'Tajawal'" : "'Plus Jakarta Sans'" !!}, -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-dark);
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.15) 0, transparent 50%),
                radial-gradient(at 100% 0%, rgba(139, 92, 246, 0.1) 0, transparent 50%),
                radial-gradient(at 50% 100%, rgba(245, 158, 11, 0.05) 0, transparent 50%);
            color: var(--text-primary);
            min-height: 100vh;
            font-size: 14px;
            line-height: 1.6;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            {{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 1px solid var(--glass-border);
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 70px;
        }

        .sidebar-logo {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            object-fit: cover;
        }

        .logo-placeholder {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), #8b5cf6);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .company-name {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-primary);
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
            gap: 12px;
            padding: 11px 14px;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 4px;
            transition: all 0.2s ease;
            font-size: 13px;
            font-weight: 500;
        }

        .nav-item:hover {
            background: rgba(99, 102, 241, 0.1);
            color: var(--text-primary);
            transform: translate{{ app()->getLocale() == 'ar' ? 'X(-4px)' : 'X(4px)' }};
        }

        .nav-item.active {
            background: rgba(99, 102, 241, 0.15);
            color: var(--primary-light);
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        .nav-icon {
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--glass-border);
            background: rgba(0,0,0,0.2);
        }

        .lang-switch {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 12px;
        }

        .lang-btn {
            padding: 5px 12px;
            font-size: 11px;
            font-weight: 500;
            border-radius: 6px;
            border: 1px solid var(--glass-border);
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.2s;
        }

        .lang-btn:hover {
            background: rgba(255,255,255,0.05);
            color: var(--text-primary);
        }

        .lang-btn.active {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--glass-border);
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
            color: white;
        }

        .user-info {
            flex: 1;
            min-width: 0;
        }

        .user-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-email {
            font-size: 11px;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .logout-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        /* Main Content */
        .main-wrapper {
            margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: var(--sidebar-width);
            min-height: 100vh;
        }

        .top-bar {
            position: sticky;
            top: 0;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--glass-border);
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 50;
        }

        .page-header-content h1 {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .page-header-content p {
            font-size: 12px;
            color: var(--text-muted);
            margin: 2px 0 0 0;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .main-content {
            padding: 24px;
        }

        /* Alerts */
        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 500;
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
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            overflow: hidden;
        }

        .card-header {
            padding: 18px 22px;
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h2,
        .card-header h3 {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .card-body {
            padding: 22px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 14px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            border-color: rgba(99, 102, 241, 0.3);
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--glass-border);
        }

        .stat-icon.primary { background: rgba(99, 102, 241, 0.15); border-color: rgba(99, 102, 241, 0.3); }
        .stat-icon.success { background: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.3); }
        .stat-icon.warning { background: rgba(245, 158, 11, 0.15); border-color: rgba(245, 158, 11, 0.3); }
        .stat-icon.danger { background: rgba(239, 68, 68, 0.15); border-color: rgba(239, 68, 68, 0.3); }

        .stat-label {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500;
            margin-bottom: 4px;
        }

        .stat-info {
            display: flex;
            flex-direction: column;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.5px;
        }

        .stat-value span {
            font-size: 16px;
            color: var(--text-muted);
            font-weight: 400;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            font-size: 13px;
            font-weight: 500;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }

        .btn-secondary {
            background: var(--bg-hover);
            color: var(--text-secondary);
            border: 1px solid var(--glass-border);
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,0.1);
            color: var(--text-primary);
        }

        .btn-danger {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .btn-danger:hover {
            background: rgba(239, 68, 68, 0.25);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        /* Tables */
        .table-container {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            padding: 14px 18px;
            text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: rgba(0,0,0,0.2);
            border-bottom: 1px solid var(--glass-border);
        }

        table td {
            padding: 14px 18px;
            font-size: 13px;
            color: var(--text-secondary);
            border-bottom: 1px solid rgba(255,255,255,0.03);
        }

        table tbody tr:hover {
            background: rgba(255,255,255,0.02);
        }

        table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 20px;
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .badge-danger {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .badge-warning {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .badge-secondary {
            background: rgba(100, 116, 139, 0.15);
            color: #94a3b8;
            border: 1px solid rgba(100, 116, 139, 0.2);
        }

        .badge-info {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .badge-primary {
            background: rgba(99, 102, 241, 0.15);
            color: #818cf8;
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        /* Forms */
        .form-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 28px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px;
            font-size: 14px;
            color: var(--text-primary);
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            transition: all 0.2s;
            outline: none;
        }

        .form-control.is-invalid {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
        }

        .invalid-feedback {
            color: #f87171;
            font-size: 12px;
            margin-top: 4px;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .form-grid-2, .form-grid-3 { grid-template-columns: 1fr; }
        }

        .form-hint {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 6px;
        }

        .form-error {
            color: #f87171;
            font-size: 12px;
            margin-top: 4px;
        }

        .form-actions {
            margin-top: 28px;
            display: flex;
            gap: 12px;
        }

        /* Action Buttons Group */
        .action-btns {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.4;
        }

        .empty-state p {
            color: var(--text-muted);
            font-size: 14px;
        }

        /* Avatar */
        .avatar-sm {
            width: 32px;
            height: 32px;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 12px;
            color: var(--text-primary);
        }

        .user-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-cell-info {
            display: flex;
            flex-direction: column;
        }

        .user-cell-name {
            font-weight: 600;
            color: var(--text-primary);
        }

        .user-cell-sub {
            font-size: 11px;
            color: var(--text-muted);
        }

        /* Status Badge */
        .status-online {
            color: #34d399;
        }

        .status-offline {
            color: #f87171;
        }

        /* Code Style */
        code {
            font-family: 'Fira Code', monospace;
            font-size: 12px;
            background: rgba(0,0,0,0.3);
            padding: 3px 8px;
            border-radius: 6px;
            color: #fbbf24;
        }

        /* Filter Card */
        .filter-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .filter-row {
            display: flex;
            gap: 16px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .filter-row .form-group {
            flex: 1;
            min-width: 180px;
            margin-bottom: 0;
        }

        /* Timeline */
        .timeline-item {
            display: flex;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .timeline-item:last-child {
            border-bottom: none;
        }

        .timeline-item:hover {
            background: rgba(255,255,255,0.02);
            margin: 0 -22px;
            padding: 14px 22px;
            border-radius: 10px;
        }

        .timeline-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #475569, #1e293b);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
            margin-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 14px;
            border: 1px solid var(--glass-border);
            flex-shrink: 0;
        }

        .timeline-content {
            flex: 1;
            min-width: 0;
        }

        .timeline-name {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 14px;
        }

        .timeline-meta {
            color: var(--text-muted);
            font-size: 12px;
        }

        .timeline-time {
            text-align: {{ app()->getLocale() == 'ar' ? 'left' : 'right' }};
            margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 14px;
        }

        .timeline-time-value {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 14px;
        }

        /* Quick Action */
        .quick-action {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            margin-bottom: 10px;
            cursor: pointer;
            text-decoration: none;
            color: var(--text-primary);
            font-weight: 500;
            font-size: 13px;
            transition: all 0.2s;
        }

        .quick-action:hover {
            background: rgba(255,255,255,0.06);
            transform: translate{{ app()->getLocale() == 'ar' ? 'X(-6px)' : 'X(6px)' }};
            border-color: rgba(99, 102, 241, 0.3);
        }

        .quick-action-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
            transition: transform 0.2s;
        }

        .quick-action:hover .quick-action-icon {
            transform: scale(1.1);
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }

        @media (max-width: 1024px) {
            .content-grid { grid-template-columns: 1fr; }
        }

        /* Progress Bar */
        .progress-bar {
            width: 100%;
            height: 8px;
            background: rgba(255,255,255,0.1);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.5s ease;
        }

        .progress-bar-fill.primary { background: var(--primary); }
        .progress-bar-fill.warning { background: var(--warning); }
        .progress-bar-fill.success { background: var(--success); }

        /* Link */
        a.link {
            color: var(--primary-light);
            text-decoration: none;
        }

        a.link:hover {
            text-decoration: underline;
        }

        /* Pagination */
        .pagination-wrapper {
            padding: 16px 20px;
            border-top: 1px solid var(--glass-border);
        }

        /* Checkbox Grid */
        .checkbox-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            padding: 16px;
            background: rgba(0,0,0,0.2);
            border-radius: 12px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 8px 14px;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            transition: all 0.2s;
        }

        .checkbox-label:hover {
            background: rgba(255,255,255,0.06);
            border-color: rgba(99, 102, 241, 0.3);
        }

        .checkbox-label input[type="checkbox"] {
            accent-color: var(--primary);
            width: 16px;
            height: 16px;
        }

        .checkbox-label input[type="checkbox"]:checked + .checkbox-text {
            color: var(--primary-light);
        }

        .checkbox-text {
            font-size: 13px;
            color: var(--text-secondary);
        }

        /* Radio Group */
        .radio-group {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .radio-label {
            cursor: pointer;
        }

        .radio-label input[type="radio"] {
            display: none;
        }

        .radio-text {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 18px;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            font-size: 13px;
            color: var(--text-secondary);
            transition: all 0.2s;
        }

        .radio-label input[type="radio"]:checked + .radio-text {
            background: rgba(99, 102, 241, 0.15);
            border-color: rgba(99, 102, 241, 0.4);
            color: var(--primary-light);
        }

        .radio-icon {
            font-size: 16px;
        }

        /* Empty State Enhanced */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-icon {
            font-size: 56px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-state p {
            color: var(--text-muted);
            font-size: 15px;
            margin-bottom: 8px;
        }

        .empty-state .btn {
            margin-top: 16px;
        }

        /* Quick Action Content */
        .quick-action-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .quick-action-content strong {
            font-size: 13px;
            color: var(--text-primary);
        }

        .quick-action-content span {
            font-size: 11px;
            color: var(--text-muted);
        }

        /* ==========================================
         * RESPONSIVE DESIGN — Dashboard/Admin Panel
         * ========================================== */

        /* Hamburger button (hidden on desktop) */
        .hamburger-btn {
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 5px;
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            cursor: pointer;
            padding: 0;
            flex-shrink: 0;
            transition: background 0.2s;
        }
        .hamburger-btn:hover { background: rgba(255,255,255,0.1); }
        .hamburger-btn span {
            display: block;
            width: 18px;
            height: 2px;
            background: var(--text-secondary);
            border-radius: 2px;
            transition: all 0.3s ease;
        }
        .hamburger-btn.is-open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
        .hamburger-btn.is-open span:nth-child(2) { opacity: 0; transform: scaleX(0); }
        .hamburger-btn.is-open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

        /* Mobile overlay */
        .mobile-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.65);
            z-index: 99;
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }
        .mobile-overlay.is-open {
            display: block;
            animation: overlayFadeIn 0.25s ease;
        }
        @keyframes overlayFadeIn { from { opacity: 0; } to { opacity: 1; } }

        /* Sidebar smooth slide transition */
        .sidebar { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }

        /* ========================
         * TABLET (≤ 1024px)
         * ======================== */
        @media (max-width: 1024px) {
            .content-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .main-content { padding: 20px; }
            .top-bar { padding: 14px 20px; }
        }

        /* ========================
         * MOBILE (≤ 768px)
         * ======================== */
        @media (max-width: 768px) {
            /* Form grids → single column */
            .form-grid-2, .form-grid-3 { grid-template-columns: 1fr; }

            /* Sidebar: use blade-rendered direction-specific transform */
            .sidebar {
                transform: translateX({{ app()->getLocale() == 'ar' ? '110%' : '-110%' }});
                transition: transform 0.3s ease;
                z-index: 9998 !important;
            }
            .sidebar.mob-open {
                transform: translateX(0) !important;
                z-index: 9998 !important;
            }

            /* Mobile overlay sits below sidebar */
            .mobile-overlay { z-index: 9997; }

            /* Top-bar must be ABOVE overlay so hamburger stays clickable */
            .top-bar { z-index: 9999 !important; }

            /* Remove sidebar margin from main wrapper */
            .main-wrapper { margin-left: 0 !important; margin-right: 0 !important; }

            /* Show hamburger button */
            .hamburger-btn { display: flex; }

            /* Top bar */
            .top-bar { padding: 12px 16px; gap: 10px; }
            .page-header-content h1 { font-size: 15px; }
            .page-header-content p { font-size: 11px; }

            /* Main content */
            .main-content { padding: 16px; }

            /* Stats */
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .stat-card { padding: 14px; }
            .stat-value { font-size: 22px; }

            /* Cards */
            .card-header { padding: 14px 16px; flex-wrap: wrap; gap: 10px; }
            .card-body { padding: 16px; }
            .form-card { padding: 18px; }

            /* Tables: horizontal scroll */
            .table-container { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            table th, table td { padding: 11px 12px; font-size: 12px; white-space: nowrap; }

            /* Filters */
            .filter-row { flex-direction: column; }
            .filter-row .form-group { min-width: unset; width: 100%; }

            /* Form actions */
            .form-actions { flex-direction: column; }
            .form-actions .btn { width: 100%; justify-content: center; }

            /* Buttons */
            .btn { padding: 9px 14px; font-size: 12px; }

            /* Misc */
            .header-actions { gap: 6px; }
            .action-btns { flex-wrap: wrap; }
            .quick-action:hover { transform: none; }
        }

        /* ========================
         * SMALL MOBILE (≤ 480px)
         * ======================== */
        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
            .stat-value { font-size: 20px; }
            .stat-icon { width: 36px; height: 36px; font-size: 16px; }
            .main-content { padding: 12px; }
            .top-bar { padding: 10px 12px; }
            .page-header-content h1 { font-size: 14px; }
            .card-header, .card-body { padding: 12px 14px; }
            .form-card { padding: 14px; }
            table th, table td { padding: 9px 10px; font-size: 11px; }
            .form-actions { gap: 8px; }
            .sidebar-header { padding: 16px; }
            .nav-section { padding: 12px 8px; }
        }

        @yield('styles')
    </style>
</head>
<body>
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            @if(auth()->user()->company_profile && auth()->user()->company_profile->logo)
                <img src="{{ route('tenant.storage', ['path' => auth()->user()->company_profile->logo]) }}" alt="Logo" class="sidebar-logo">
            @else
                <div class="logo-placeholder">✨</div>
            @endif
            <span class="company-name">{{ auth()->user()->company_profile?->name ?? __('messages.company_name') }}</span>
        </div>
        
        <nav class="nav-section">
            <div class="nav-label">{{ __('messages.main_menu') ?? 'القائمة الرئيسية' }}</div>
            @can('dashboard.view')
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
                <span class="nav-icon">📊</span> {{ __('messages.dashboard') }}
            </a>
            @endcan

            @canany(['branches.view', 'departments.view', 'work-points.view', 'work-tasks.view', 'devices.view', 'employees.view', 'contracts.view'])
            <div class="nav-label" style="margin-top: 20px;">{{ __('messages.organizational_structure') ?? 'الهيكل التنظيمي' }}</div>
            @endcanany
            @can('branches.view')
            <a href="{{ route('branches.index') }}" class="nav-item {{ request()->is('branches*') ? 'active' : '' }}">
                <span class="nav-icon">🏢</span> {{ __('messages.branches') }}
            </a>
            @endcan
            @can('departments.view')
            <a href="{{ route('departments.index') }}" class="nav-item {{ request()->is('departments*') ? 'active' : '' }}">
                <span class="nav-icon">🗂️</span> {{ __('messages.departments') }}
            </a>
            @endcan
            @can('work-points.view')
            <a href="{{ route('work-points.index') }}" class="nav-item {{ request()->is('work-points*') ? 'active' : '' }}">
                <span class="nav-icon">📍</span> {{ __('messages.work_points') }}
            </a>
            @endcan
            @can('work-tasks.view')
            <a href="{{ route('work-tasks.index') }}" class="nav-item {{ request()->is('work-tasks*') ? 'active' : '' }}">
                <span class="nav-icon">📋</span> {{ __('messages.work_tasks') }}
            </a>
            @endcan
            @can('devices.view')
            <a href="{{ route('devices.index') }}" class="nav-item {{ request()->is('devices*') ? 'active' : '' }}">
                <span class="nav-icon">📱</span> {{ __('messages.devices') }}
            </a>
            @endcan
            @can('employees.view')
            <a href="{{ route('employees.index') }}" class="nav-item {{ request()->is('employees*') ? 'active' : '' }}">
                <span class="nav-icon">👥</span> {{ __('messages.employees') }}
            </a>
            @endcan
            @can('contracts.view')
            <a href="{{ route('contract-documents.index') }}" class="nav-item {{ request()->is('contract-documents*') ? 'active' : '' }}">
                <span class="nav-icon">📄</span> {{ __('messages.contract_documents') }}
            </a>
            @endcan

            @canany(['attendance.view', 'reports.view', 'analytics.view'])
            <div class="nav-label" style="margin-top: 20px;">{{ __('messages.monitoring') ?? 'المراقبة والتقارير' }}</div>
            @endcanany
            @can('attendance.view')
            <a href="{{ route('attendance.index') }}" class="nav-item {{ request()->is('attendance*') ? 'active' : '' }}">
                <span class="nav-icon">⏰</span> {{ __('messages.attendance') }}
            </a>
            @endcan
            @can('reports.view')
            <a href="{{ route('reports.index') }}" class="nav-item {{ request()->is('reports*') ? 'active' : '' }}">
                <span class="nav-icon">📈</span> {{ __('messages.reports') }}
            </a>
            @endcan
            @can('analytics.view')
            <a href="{{ route('analytics.index') }}" class="nav-item {{ request()->is('analytics*') ? 'active' : '' }}">
                <span class="nav-icon">📊</span> {{ __('messages.analytics') }}
            </a>
            <a href="{{ route('attendance-report.index') }}" class="nav-item {{ request()->is('attendance-report*') ? 'active' : '' }}">
                <span class="nav-icon">📋</span> {{ __('messages.attendance_report') }}
            </a>
            @endcan
            
            <div class="nav-label" style="margin-top: 20px;">{{ __('messages.employee_portal') ?? 'بوابة الموظف' }}</div>
            <a href="{{ route('employee.dashboard') }}" class="nav-item {{ request()->is('employee') ? 'active' : '' }}">
                <span class="nav-icon">🏠</span> {{ __('messages.my_portal') }}
            </a>
            <a href="{{ route('employee.leaves.index') }}" class="nav-item {{ request()->is('employee/leaves*') ? 'active' : '' }}">
                <span class="nav-icon">🏖️</span> {{ __('messages.my_leaves') }}
            </a>
            <a href="{{ route('employee.permissions.index') }}" class="nav-item {{ request()->is('employee/permissions*') ? 'active' : '' }}">
                <span class="nav-icon">🕐</span> {{ __('messages.my_permissions') }}
            </a>
            <a href="{{ route('employee.corrections.index') }}" class="nav-item {{ request()->is('employee/corrections*') ? 'active' : '' }}">
                <span class="nav-icon">✏️</span> {{ __('messages.my_corrections') }}
            </a>

            @canany(['shifts.view', 'time-off.view', 'users.view', 'settings.view', 'billing.view'])
            <div class="nav-label" style="margin-top: 20px;">{{ __('messages.management') ?? 'الإدارة' }}</div>
            @endcanany
            @can('shifts.view')
            <a href="{{ route('admin.shifts.index') }}" class="nav-item {{ request()->is('admin/shifts*') ? 'active' : '' }}">
                <span class="nav-icon">📅</span> {{ __('messages.shifts') }}
            </a>
            @endcan
            @can('time-off.view')
            <a href="{{ route('admin.time-off.index') }}" class="nav-item {{ request()->is('admin/time-off*') ? 'active' : '' }}">
                <span class="nav-icon">📝</span> {{ __('messages.leave_management') }}
            </a>
            @endcan
            @can('permission-requests.view')
            <a href="{{ route('admin.permission-requests.index') }}" class="nav-item {{ request()->is('admin/permission-requests*') ? 'active' : '' }}">
                <span class="nav-icon">🕐</span> {{ __('messages.permission_management') }}
            </a>
            @endcan
            @can('attendance-corrections.view')
            <a href="{{ route('admin.attendance-corrections.index') }}" class="nav-item {{ request()->is('admin/attendance-corrections*') ? 'active' : '' }}">
                <span class="nav-icon">✏️</span> {{ __('messages.attendance_corrections') }}
            </a>
            @endcan
            @can('users.view')
            <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->is('admin/users*') ? 'active' : '' }}">
                <span class="nav-icon">👤</span> {{ __('messages.user_management') ?? 'إدارة المستخدمين' }}
            </a>
            @endcan
            @can('billing.view')
            <a href="{{ route('billing.index') }}" class="nav-item {{ request()->is('billing*') ? 'active' : '' }}">
                <span class="nav-icon">💳</span> {{ __('messages.billing') }}
            </a>
            @endcan

            @canany(['payroll.view', 'salary-components.view', 'rewards-penalties.view', 'salary-advances.view'])
            <div class="nav-label" style="margin-top: 20px;">{{ __('messages.payroll_section') }}</div>
            @endcanany
            @can('payroll.view')
            <a href="{{ route('admin.payroll.index') }}" class="nav-item {{ request()->is('admin/payroll*') ? 'active' : '' }}">
                <span class="nav-icon">💰</span> {{ __('messages.payroll') }}
            </a>
            @endcan
            @can('salary-components.view')
            <a href="{{ route('admin.salary-components.index') }}" class="nav-item {{ request()->is('admin/salary-components*') ? 'active' : '' }}">
                <span class="nav-icon">📋</span> {{ __('messages.salary_components') }}
            </a>
            @endcan
            @can('rewards-penalties.view')
            <a href="{{ route('admin.rewards-penalties.index') }}" class="nav-item {{ request()->is('admin/rewards-penalties*') && !request()->is('admin/rewards-penalties/types*') ? 'active' : '' }}">
                <span class="nav-icon">🏆</span> {{ __('messages.rewards_penalties') }}
            </a>
            @endcan
            @can('salary-advances.view')
            <a href="{{ route('admin.salary-advances.index') }}" class="nav-item {{ request()->is('admin/salary-advances*') ? 'active' : '' }}">
                <span class="nav-icon">💸</span> {{ __('messages.salary_advances') ?? 'السلف' }}
            </a>
            @endcan

            @can('settings.view')
            <a href="{{ route('admin.settings.index') }}" class="nav-item {{ request()->is('admin/settings*') ? 'active' : '' }}">
                <span class="nav-icon">⚙️</span> {{ __('messages.settings') }}
            </a>
            @endcan
            <a href="{{ route('notifications.index') }}" class="nav-item {{ request()->is('notifications*') ? 'active' : '' }}">
                <span class="nav-icon">🔔</span> {{ __('messages.notifications') ?? 'الإشعارات' }}
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="lang-switch">
                <a href="{{ route('lang.switch', ['locale' => 'ar']) }}" class="lang-btn {{ app()->getLocale() == 'ar' ? 'active' : '' }}">العربية</a>
                <a href="{{ route('lang.switch', ['locale' => 'en']) }}" class="lang-btn {{ app()->getLocale() == 'en' ? 'active' : '' }}">English</a>
            </div>

            <div class="user-card">
                <a href="{{ route('profile.edit') }}" style="text-decoration: none; display: flex; align-items: center; gap: 10px; flex: 1;">
                    <div class="user-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                    <div class="user-info">
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-email">{{ auth()->user()->email }}</div>
                    </div>
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-btn" title="{{ __('messages.logout') }}">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-wrapper">
        <header class="top-bar">
            <!-- Hamburger (mobile only) -->
            <button class="hamburger-btn" id="sidebarToggle" aria-label="{{ __('messages.toggle_menu') ?? 'Toggle Menu' }}">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div class="page-header-content">
                @yield('header')
            </div>
            <div class="header-actions">
                @yield('header-actions')
                <!-- Notification Bell -->
                <div class="notification-wrapper" id="notificationWrapper">
                    <button class="notification-bell" id="notificationBell" onclick="toggleNotifications()" title="{{ __('messages.notifications') ?? 'الإشعارات' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                        <span class="notification-badge" id="notificationBadge" style="display:none;">0</span>
                    </button>
                    <div class="notification-dropdown" id="notificationDropdown">
                        <div class="notification-dropdown-header">
                            <span class="notification-dropdown-title">{{ __('messages.notifications') ?? 'الإشعارات' }}</span>
                            <button class="notification-mark-all" onclick="markAllRead()" title="{{ __('messages.mark_all_read') ?? 'تعليم الكل كمقروء' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </button>
                        </div>
                        <div class="notification-list" id="notificationList">
                            <div class="notification-empty">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.3"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                                <p>{{ __('messages.no_notifications') ?? 'لا توجد إشعارات' }}</p>
                            </div>
                        </div>
                        <a href="/notifications" class="notification-view-all" onclick="event.stopPropagation(); window.location.href='/notifications';">{{ __('messages.view_all_notifications') ?? 'عرض جميع الإشعارات' }}</a>
                    </div>
                </div>
            </div>
        </header>

        <main class="main-content">
            @if(session('success'))
                <div class="alert alert-success">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @include('components.help-widget')

    @yield('scripts')

    <style>
    .notification-wrapper { position: relative; }
    .notification-bell {
        position: relative; background: none; border: none; cursor: pointer;
        padding: 8px; border-radius: 10px; color: var(--text-secondary, #64748b);
        transition: all 0.2s; display: flex; align-items: center; justify-content: center;
    }
    .notification-bell:hover { background: var(--bg-hover, rgba(0,0,0,0.05)); color: var(--text-primary, #1e293b); }
    .notification-badge {
        position: absolute; top: 2px; right: 2px;
        min-width: 18px; height: 18px; padding: 0 5px;
        background: #ef4444; color: #fff; font-size: 11px; font-weight: 700;
        border-radius: 9px; display: flex; align-items: center; justify-content: center;
        line-height: 1; border: 2px solid var(--bg-card, #fff);
    }
    .notification-dropdown {
        display: none; position: fixed; top: auto;
        width: 380px; max-width: calc(100vw - 24px); max-height: 480px;
        background: var(--bg-card, #fff); border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15), 0 0 0 1px rgba(0,0,0,0.05);
        z-index: 9999; overflow: hidden;
    }
    [dir="rtl"] .notification-dropdown { left: 12px; right: auto; }
    [dir="ltr"] .notification-dropdown { right: 12px; left: auto; }
    .notification-dropdown.show { display: block; animation: notifSlideIn 0.2s ease; }
    .notification-view-all {
        display: block; text-align: center; padding: 10px; font-size: 13px; font-weight: 600;
        color: #818cf8; text-decoration: none; border-top: 1px solid var(--glass-border);
        transition: background 0.2s;
    }
    .notification-view-all:hover { background: rgba(99,102,241,0.1); }
    @keyframes notifSlideIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
    .notification-dropdown-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 18px; border-bottom: 1px solid var(--border-color, #e2e8f0);
    }
    .notification-dropdown-title { font-weight: 700; font-size: 15px; color: var(--text-primary, #1e293b); }
    .notification-mark-all {
        background: none; border: none; cursor: pointer; padding: 6px;
        border-radius: 8px; color: var(--text-secondary, #64748b); transition: all 0.2s;
    }
    .notification-mark-all:hover { background: var(--bg-hover, rgba(0,0,0,0.05)); color: var(--primary, #6366f1); }
    .notification-list { max-height: 400px; overflow-y: auto; }
    .notification-item {
        display: flex; gap: 12px; padding: 14px 18px; cursor: pointer;
        transition: background 0.15s; border-bottom: 1px solid var(--border-color, #f1f5f9); position: relative;
    }
    .notification-item:hover { background: var(--bg-hover, rgba(0,0,0,0.02)); }
    .notification-item.unread { background: rgba(99,102,241,0.04); }
    .notification-item.unread::before {
        content: ''; position: absolute; width: 6px; height: 6px; border-radius: 50%;
        background: #6366f1; top: 18px;
    }
    [dir="rtl"] .notification-item.unread::before { right: 8px; }
    [dir="ltr"] .notification-item.unread::before { left: 8px; }
    .notif-icon {
        width: 38px; height: 38px; border-radius: 10px; display: flex;
        align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;
    }
    .notif-content { flex: 1; min-width: 0; }
    .notif-title { font-size: 13px; font-weight: 600; color: var(--text-primary, #1e293b); margin-bottom: 2px; line-height: 1.4; }
    .notif-body { font-size: 12px; color: var(--text-secondary, #64748b); line-height: 1.4; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .notif-time { font-size: 11px; color: var(--text-muted, #94a3b8); margin-top: 4px; }
    .notification-empty {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        padding: 40px 20px; color: var(--text-muted, #94a3b8); gap: 8px;
    }
    .notification-empty p { font-size: 13px; }
    </style>
    <script>
    (function() {
        let dropdownOpen = false;
        const iconMap = {
            'clock': '⏰', 'calendar': '📅', 'banknotes': '💰', 'currency': '💵',
            'clock-rotate': '🔄', 'file-text': '📄', 'timer': '⏱️', 'hand': '✋',
            'pencil': '✏️', 'star': '⭐', 'bell': '🔔', 'check-circle': '✅', 'x-circle': '❌'
        };
        const colorMap = {
            'orange': 'rgba(249,115,22,0.1)', 'green': 'rgba(34,197,94,0.1)',
            'emerald': 'rgba(16,185,129,0.1)', 'yellow': 'rgba(234,179,8,0.1)',
            'purple': 'rgba(168,85,247,0.1)', 'red': 'rgba(239,68,68,0.1)',
            'amber': 'rgba(245,158,11,0.1)', 'teal': 'rgba(20,184,166,0.1)',
            'slate': 'rgba(100,116,139,0.1)', 'gold': 'rgba(234,179,8,0.1)',
            'blue': 'rgba(99,102,241,0.1)'
        };

        // Navigate to notification - global function for onclick fallback
        window.goToNotification = function(id, href, ev) {
            if (ev) { ev.stopPropagation(); ev.preventDefault(); }
            if (id) {
                fetch('/notifications/' + id + '/read', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                }).catch(function(){});
            }
            document.querySelectorAll('.notification-dropdown').forEach(function(d) { d.classList.remove('show'); });
            dropdownOpen = false;
            window.location.href = href || '/notifications';
        };

        // Capture-phase handler: fires BEFORE any bubbling handlers
        document.addEventListener('click', function(e) {
            var item = e.target.closest('.notification-item');
            if (!item) return;
            e.stopPropagation();
            e.preventDefault();
            var id = item.getAttribute('data-id');
            var href = item.getAttribute('data-href') || '/notifications';
            window.goToNotification(id, href);
        }, true);

        window.toggleNotifications = function() {
            const dd = document.querySelector('.notification-dropdown');
            if (!dd) return;
            dropdownOpen = !dropdownOpen;
            if (dropdownOpen) {
                const bell = document.querySelector('.notification-bell');
                const rect = bell.getBoundingClientRect();
                dd.style.top = (rect.bottom + 8) + 'px';
            }
            dd.classList.toggle('show', dropdownOpen);
            if (dropdownOpen) loadNotifications();
        };

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.notification-wrapper')) {
                document.querySelectorAll('.notification-dropdown').forEach(d => d.classList.remove('show'));
                dropdownOpen = false;
            }
        });

        function updateBadge(count) {
            document.querySelectorAll('[id^="notificationBadge"]').forEach(b => {
                if (count > 0) { b.textContent = count > 99 ? '99+' : count; b.style.display = 'flex'; }
                else { b.style.display = 'none'; }
            });
        }

        function renderNotifications(items) {
            document.querySelectorAll('.notification-list').forEach(list => {
                if (!items || items.length === 0) {
                    list.innerHTML = '<div class="notification-empty"><svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.3"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg><p>لا توجد إشعارات</p></div>';
                    return;
                }
                list.innerHTML = items.map(n => {
                    var nid = parseInt(n.id) || 0;
                    var itemHref = n.action_url || '/notifications';
                    return '<div class="notification-item ' + (n.is_read ? '' : 'unread') + '" data-id="' + nid + '" data-read="' + (n.is_read ? 'true' : 'false') + '" data-href="' + escapeHtml(itemHref) + '" onclick="goToNotification(\'' + nid + '\',\'' + escapeHtml(itemHref) + '\',event)">'
                        + '<div class="notif-icon" style="background:' + (colorMap[n.color] || colorMap.blue) + '">' + (iconMap[n.icon] || '🔔') + '</div>'
                        + '<div class="notif-content">'
                        + '<div class="notif-title">' + escapeHtml(n.title) + '</div>'
                        + (n.body ? '<div class="notif-body">' + escapeHtml(n.body) + '</div>' : '')
                        + '<div class="notif-time">' + escapeHtml(n.time_ago || '') + '</div>'
                        + '</div>'
                        + '</div>';
                }).join('');
            });
        }

        function escapeHtml(text) {
            if (!text) return '';
            var d = document.createElement('div'); d.textContent = text; return d.innerHTML;
        }

        window.loadNotifications = function() {
            fetch('/notifications/api', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                updateBadge(data.unread_count);
                renderNotifications(data.notifications);
            }).catch(() => {});
        };

        window.markAllRead = function() {
            fetch('/notifications/read-all', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
            }).then(() => {
                document.querySelectorAll('.notification-item.unread').forEach(i => i.classList.remove('unread'));
                updateBadge(0);
            });
        };

        function fetchUnreadCount() {
            fetch('/notifications/unread-count', { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => updateBadge(data.unread_count))
            .catch(() => {});
        }

        // Poll every 30 seconds
        fetchUnreadCount();
        setInterval(fetchUnreadCount, 30000);
    })();
    </script>

    <script>
    // Mobile sidebar toggle — runs after DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        var sidebar  = document.querySelector('.sidebar');
        var overlay  = document.getElementById('mobileOverlay');
        var toggleBtn = document.getElementById('sidebarToggle');

        if (!sidebar || !toggleBtn) return;

        function openSidebar() {
            sidebar.classList.add('mob-open');
            if (overlay)   overlay.classList.add('is-open');
            if (toggleBtn) toggleBtn.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.remove('mob-open');
            if (overlay)   overlay.classList.remove('is-open');
            if (toggleBtn) toggleBtn.classList.remove('is-open');
            document.body.style.overflow = '';
        }

        // Wire up hamburger button
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.contains('mob-open') ? closeSidebar() : openSidebar();
        });

        // Overlay click → close
        if (overlay) overlay.addEventListener('click', closeSidebar);

        // Resize → close on desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) closeSidebar();
        });

        // Nav links → close on mobile tap
        sidebar.querySelectorAll('.nav-item').forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) closeSidebar();
            });
        });

        // Also expose globally as fallback
        window.toggleSidebar = function() {
            sidebar.classList.contains('mob-open') ? closeSidebar() : openSidebar();
        };
    });
    </script>
</body>
</html>
