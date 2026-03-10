<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ app()->getLocale() == 'ar' ? 'نظام حاضر - نظام موارد بشرية وحضور متكامل' : 'Hadir - Complete HR & Attendance System' }}</title>
    <meta name="description" content="{{ app()->getLocale() == 'ar' ? 'نظام حاضر - نظام موارد بشرية متكامل: حضور وانصراف، رواتب، إجازات، سلف، ورديات، عقود، أذونات، إدارة موظفين وأقسام مع أجهزة ZKTeco. حلول ذكية للشركات.' : 'Hadir - Complete HR system: attendance, payroll, leaves, advances, shifts, contracts, permissions, employee & department management with ZKTeco biometric devices. Smart enterprise solutions.' }}">
    <meta name="keywords" content="{{ app()->getLocale() == 'ar' ? 'نظام موارد بشرية، نظام حضور وانصراف، بصمة، حاضر، إدارة موظفين، ZKTeco، رواتب، إجازات، ورديات، سلف، عقود، أذونات، تصحيح حضور، إدارة أقسام' : 'HR system, attendance system, biometric, hadir, employee management, ZKTeco, payroll, leaves, shifts, salary advances, contracts, permissions, attendance corrections, department management' }}">
    <meta name="author" content="Hadir">
    <meta name="robots" content="index, follow">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ app()->getLocale() == 'ar' ? 'نظام حاضر - نظام موارد بشرية وحضور متكامل' : 'Hadir - Complete HR & Attendance System' }}">
    <meta property="og:description" content="{{ app()->getLocale() == 'ar' ? 'نظام موارد بشرية متكامل: حضور، رواتب، إجازات، سلف، ورديات، عقود - حلول ذكية للشركات' : 'Complete HR system: attendance, payroll, leaves, advances, shifts, contracts - Smart enterprise solutions' }}">
    <meta property="og:image" content="/logo.png">
    
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #f59e0b;
            --bg-dark: #0a0f1e;
            --bg-card: #111827;
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
        }
        
        html { scroll-behavior: smooth; overflow-x: hidden; }
        
        body {
            font-family: {!! "'Tajawal', -apple-system, BlinkMacSystemFont, sans-serif" !!};
            background: var(--bg-dark);
            color: var(--text-light);
            line-height: 1.6;
            overflow-x: hidden;
            max-width: 100vw;
        }
        
        /* Animated Background */
        .bg-pattern {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: 
                radial-gradient(ellipse at 20% 20%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 80%, rgba(245, 158, 11, 0.1) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 50%, rgba(99, 102, 241, 0.05) 0%, transparent 70%);
        }
        
        .grid-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background-image: 
                linear-gradient(rgba(99, 102, 241, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99, 102, 241, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
        }
        
        /* Navigation */
        .nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(10, 15, 30, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .logo-img {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            object-fit: contain;
        }
        
        .logo-text {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(135deg, #fff, #a5b4fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-links {
            display: flex;
            align-items: center;
            gap: 32px;
        }
        
        .nav-links a {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            transition: color 0.3s;
        }
        
        .nav-links a:hover { color: #fff; }
        
        .nav-buttons {
            display: flex;
            gap: 12px;
        }
        
        .btn {
            padding: 12px 28px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            border: none;
            font-family: inherit;
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
        }
        
        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.35);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(99, 102, 241, 0.45);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, var(--secondary), #d97706);
            color: #fff;
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.35);
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(245, 158, 11, 0.45);
        }
        
        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 140px 40px 80px;
        }
        
        .hero-container {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            align-items: center;
        }
        
        .hero-content h1 {
            font-size: 56px;
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 24px;
        }
        
        .hero-content h1 span {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero-content p {
            font-size: 20px;
            color: var(--text-muted);
            margin-bottom: 40px;
            max-width: 500px;
        }
        
        .hero-buttons {
            display: flex;
            gap: 16px;
            margin-bottom: 48px;
        }
        
        .hero-stats {
            display: flex;
            gap: 48px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: 800;
            color: var(--primary);
        }
        
        .stat-label {
            font-size: 14px;
            color: var(--text-muted);
        }
        
        /* Hero Image */
        .hero-visual {
            position: relative;
        }
        
        .device-showcase {
            position: relative;
            padding: 40px;
        }
        
        .device-card {
            background: linear-gradient(145deg, #1e293b, #0f172a);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 32px;
            position: relative;
            overflow: hidden;
        }
        
        .device-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }
        
        .device-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .device-title {
            font-size: 18px;
            font-weight: 700;
        }
        
        .device-status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #34d399;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            background: #34d399;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .device-image {
            background: linear-gradient(135deg, #1e3a5f, #0f172a);
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            margin-bottom: 24px;
            border: 1px solid rgba(99, 102, 241, 0.2);
        }
        
        .fingerprint-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            box-shadow: 0 20px 50px rgba(99, 102, 241, 0.4);
            animation: glow 3s infinite;
        }
        
        @keyframes glow {
            0%, 100% { box-shadow: 0 20px 50px rgba(99, 102, 241, 0.4); }
            50% { box-shadow: 0 25px 60px rgba(99, 102, 241, 0.6); }
        }
        
        .device-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }
        
        .device-stat {
            background: rgba(99, 102, 241, 0.1);
            border-radius: 12px;
            padding: 16px;
            text-align: center;
        }
        
        .device-stat-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
        }
        
        .device-stat-label {
            font-size: 12px;
            color: var(--text-muted);
        }
        
        /* Floating Elements */
        .float-card {
            position: absolute;
            background: rgba(30, 41, 59, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: float 4s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .float-card.top-right {
            top: 20px;
            right: -20px;
            animation-delay: 0s;
        }
        
        .float-card.bottom-left {
            bottom: 40px;
            left: -40px;
            animation-delay: 1s;
        }
        
        .float-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .float-icon.green { background: rgba(34, 197, 94, 0.2); }
        .float-icon.blue { background: rgba(99, 102, 241, 0.2); }
        
        .float-text strong {
            display: block;
            font-size: 14px;
            color: #fff;
        }
        
        .float-text span {
            font-size: 12px;
            color: var(--text-muted);
        }
        
        /* Features Section */
        .features {
            padding: 120px 40px;
            background: linear-gradient(180deg, transparent, rgba(99, 102, 241, 0.03));
        }
        
        .section-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 64px;
        }
        
        .section-badge {
            display: inline-block;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.2);
            color: var(--primary);
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 44px;
            font-weight: 800;
            margin-bottom: 16px;
        }
        
        .section-desc {
            font-size: 18px;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }
        
        .feature-card {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.6), rgba(15, 23, 42, 0.6));
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            padding: 28px;
            transition: all 0.4s;
        }
        
        .feature-card:hover {
            transform: translateY(-8px);
            border-color: rgba(99, 102, 241, 0.3);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }
        
        .feature-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 24px;
        }
        
        .feature-icon.purple { background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(139, 92, 246, 0.2)); }
        .feature-icon.orange { background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(234, 88, 12, 0.2)); }
        .feature-icon.green { background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(16, 185, 129, 0.2)); }
        .feature-icon.blue { background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(99, 102, 241, 0.2)); }
        .feature-icon.pink { background: linear-gradient(135deg, rgba(236, 72, 153, 0.2), rgba(219, 39, 119, 0.2)); }
        .feature-icon.cyan { background: linear-gradient(135deg, rgba(34, 211, 238, 0.2), rgba(6, 182, 212, 0.2)); }
        .feature-icon.red { background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(220, 38, 38, 0.2)); }
        .feature-icon.amber { background: linear-gradient(135deg, rgba(251, 191, 36, 0.2), rgba(245, 158, 11, 0.2)); }
        .feature-icon.teal { background: linear-gradient(135deg, rgba(20, 184, 166, 0.2), rgba(13, 148, 136, 0.2)); }
        .feature-icon.indigo { background: linear-gradient(135deg, rgba(129, 140, 248, 0.2), rgba(99, 102, 241, 0.2)); }
        .feature-icon.emerald { background: linear-gradient(135deg, rgba(52, 211, 153, 0.2), rgba(16, 185, 129, 0.2)); }
        .feature-icon.violet { background: linear-gradient(135deg, rgba(167, 139, 250, 0.2), rgba(139, 92, 246, 0.2)); }
        
        .feature-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        
        .feature-desc {
            color: var(--text-muted);
            font-size: 15px;
            line-height: 1.7;
        }
        
        /* Devices Section */
        .devices {
            padding: 120px 40px;
        }
        
        .devices-showcase {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-top: 48px;
        }
        
        .device-item {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.8));
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 32px 24px;
            text-align: center;
            transition: all 0.4s;
        }
        
        .device-item:hover {
            transform: translateY(-5px);
            border-color: var(--secondary);
            box-shadow: 0 20px 40px rgba(245, 158, 11, 0.15);
        }
        
        .device-icon-large {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #1e3a5f, #0f172a);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            border: 1px solid rgba(99, 102, 241, 0.2);
        }
        
        .device-name {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        
        .device-type {
            font-size: 13px;
            color: var(--text-muted);
        }
        
        /* Pricing Section */
        .pricing {
            padding: 120px 40px;
            background: linear-gradient(180deg, rgba(99, 102, 241, 0.03), transparent);
        }
        
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-top: 48px;
        }
        
        .pricing-card {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.6), rgba(15, 23, 42, 0.6));
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            padding: 40px;
            position: relative;
            transition: all 0.4s;
        }
        
        .pricing-card.featured {
            border-color: var(--primary);
            transform: scale(1.05);
        }
        
        .pricing-card.featured::before {
            content: '{{ app()->getLocale() == "ar" ? "الأكثر شعبية" : "Most Popular" }}';
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
            padding: 6px 20px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .pricing-name {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .pricing-desc {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 24px;
        }
        
        .pricing-price {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 8px;
        }
        
        .pricing-price span {
            font-size: 16px;
            font-weight: 500;
            color: var(--text-muted);
        }
        
        .pricing-features {
            list-style: none;
            margin: 32px 0;
        }
        
        .pricing-features li {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 14px;
            color: var(--text-muted);
        }
        
        .pricing-features li:last-child { border-bottom: none; }
        
        .check-icon {
            color: #34d399;
        }
        
        /* CTA Section */
        .cta {
            padding: 120px 40px;
        }
        
        .cta-box {
            max-width: 900px;
            margin: 0 auto;
            background: linear-gradient(135deg, var(--primary), #7c3aed);
            border-radius: 32px;
            padding: 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .cta-box::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 50%);
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .cta-content {
            position: relative;
            z-index: 1;
        }
        
        .cta-title {
            font-size: 40px;
            font-weight: 800;
            margin-bottom: 16px;
        }
        
        .cta-desc {
            font-size: 18px;
            opacity: 0.9;
            margin-bottom: 40px;
        }
        
        /* Footer */
        .footer {
            padding: 60px 40px 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .footer-text {
            color: var(--text-muted);
            font-size: 14px;
        }
        
        .footer-links {
            display: flex;
            gap: 32px;
        }
        
        .footer-links a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }
        
        .footer-links a:hover { color: #fff; }
        
        /* Language Switcher */
        .lang-switch {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        
        .lang-btn {
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .lang-btn.active {
            background: var(--primary);
            color: #fff;
        }
        
        .lang-btn:not(.active) {
            color: var(--text-muted);
        }
        
        .lang-btn:not(.active):hover {
            color: #fff;
        }
        
        /* ==========================================
         * RESPONSIVE DESIGN — Landing Page
         * ========================================== */

        /* Hamburger button (hidden on desktop) */
        .hamburger-menu {
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 5px;
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 10px;
            cursor: pointer;
            padding: 0;
            transition: background 0.2s;
            flex-shrink: 0;
        }
        .hamburger-menu:hover { background: rgba(255,255,255,0.14); }
        .hamburger-menu span {
            display: block;
            width: 18px;
            height: 2px;
            background: rgba(255,255,255,0.85);
            border-radius: 2px;
            transition: all 0.3s ease;
        }
        .hamburger-menu.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
        .hamburger-menu.open span:nth-child(2) { opacity: 0; transform: scaleX(0); }
        .hamburger-menu.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

        /* Mobile full-screen nav */
        .mobile-nav {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(10, 15, 30, 0.97);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            z-index: 1001;
            padding: 90px 32px 40px;
            flex-direction: column;
            gap: 4px;
            overflow-y: auto;
        }
        .mobile-nav.open { display: flex; animation: mobileNavIn 0.28s ease; }
        @keyframes mobileNavIn {
            from { opacity: 0; transform: translateY(-12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .mobile-nav a.mnav-link {
            color: rgba(255,255,255,0.75);
            text-decoration: none;
            font-size: 22px;
            font-weight: 600;
            padding: 16px 0;
            border-bottom: 1px solid rgba(255,255,255,0.07);
            transition: color 0.2s;
            display: block;
        }
        .mobile-nav a.mnav-link:hover { color: #fff; }
        .mobile-nav-btns {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 28px;
        }
        .mobile-nav-btns a { text-align: center; justify-content: center; }
        .mobile-nav-lang {
            display: flex;
            gap: 10px;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.07);
        }
        .mobile-nav-close {
            position: absolute;
            top: 20px;
            {{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 20px;
            width: 44px;
            height: 44px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 22px;
            color: rgba(255,255,255,0.6);
            transition: all 0.2s;
            line-height: 1;
        }
        .mobile-nav-close:hover { background: rgba(255,255,255,0.15); color: #fff; }

        /* Pricing comparison table: horizontal scroll on mobile */
        .pricing-comparison-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }

        /* Billing cycle toggle: wrap on mobile */
        .billing-cycle-wrap { flex-wrap: wrap; justify-content: center; }

        /* Billing cycle buttons */
        .lp-cycle-btn {
            padding: 10px 20px;
            font-size: 13px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            font-family: inherit;
            white-space: nowrap;
        }

        /* ========================
         * TABLET (≤ 1024px)
         * ======================== */
        @media (max-width: 1024px) {
            .hero-container { grid-template-columns: 1fr; text-align: center; gap: 40px; }
            .hero-content p { margin: 0 auto 40px; }
            .hero-buttons { justify-content: center; flex-wrap: wrap; }
            .hero-stats { justify-content: center; flex-wrap: wrap; }
            .hero-visual { display: none; }
            .features-grid { grid-template-columns: repeat(2, 1fr); }
            .devices-showcase { grid-template-columns: repeat(2, 1fr); }
            .pricing-grid { grid-template-columns: repeat(2, 1fr); }
            .pricing-card.featured { transform: scale(1); }
            .features { padding: 80px 32px; }
            .devices  { padding: 80px 32px; }
            .pricing  { padding: 80px 32px; }
            .cta      { padding: 80px 32px; }
        }

        /* ========================
         * MOBILE (≤ 768px)
         * ======================== */
        @media (max-width: 768px) {
            /* Nav: hide everything except logo + hamburger */
            .nav { padding: 14px 20px; gap: 8px; }
            .nav-links { display: none; }
            .nav-buttons { display: none; }   /* hide lang-switch + all buttons */
            .hamburger-menu { display: flex; }

            /* Hero */
            .hero { padding: 100px 20px 56px; }
            .hero-content h1 { font-size: 36px; }
            .hero-buttons { gap: 12px; }
            .hero-buttons .btn { width: 100%; justify-content: center; }
            .hero-stats { gap: 28px; flex-wrap: wrap; }
            .stat-value { font-size: 30px; }

            /* Sections */
            .section-title { font-size: 30px; }
            .section-desc  { font-size: 16px; }
            .features { padding: 60px 20px; }
            .devices  { padding: 60px 20px; }
            .pricing  { padding: 60px 20px; }
            .cta      { padding: 60px 20px; }
            .section-header { margin-bottom: 40px; }

            /* Grids */
            .features-grid    { grid-template-columns: 1fr; gap: 16px; }
            .devices-showcase { grid-template-columns: repeat(2, 1fr); gap: 16px; }
            .pricing-grid     { grid-template-columns: 1fr; }

            /* CTA */
            .cta-box   { padding: 40px 24px; border-radius: 20px; }
            .cta-title { font-size: 26px; }
            .cta-desc  { font-size: 16px; }
            .cta-box .btn { font-size: 16px !important; padding: 14px 28px !important; }

            /* Footer */
            .footer         { padding: 40px 20px 20px; }
            .footer-content { flex-direction: column; gap: 20px; text-align: center; }
            .footer-links   { flex-wrap: wrap; justify-content: center; gap: 16px; }

            /* Pricing card */
            .pricing-card { padding: 28px; }
        }

        /* ========================
         * SMALL MOBILE (≤ 480px)
         * ======================== */
        @media (max-width: 480px) {
            /* Nav */
            .nav { padding: 12px 16px; }
            .logo-text { font-size: 20px; }
            .logo-img  { width: 36px; height: 36px; }

            /* Hero */
            .hero { padding: 85px 16px 48px; }
            .hero-content h1 { font-size: 28px; line-height: 1.2; }
            .hero-content p  { font-size: 16px; }
            .hero-stats { gap: 20px; }
            .stat-value { font-size: 26px; }

            /* Sections */
            .section-title  { font-size: 24px; }
            .section-badge  { font-size: 12px; padding: 6px 14px; }
            .features { padding: 48px 16px; }
            .devices  { padding: 48px 16px; }
            .pricing  { padding: 48px 16px; }
            .cta      { padding: 48px 16px; }

            /* Grids */
            .devices-showcase { grid-template-columns: 1fr; }

            /* Billing cycle buttons */
            .lp-cycle-btn { padding: 7px 10px; font-size: 11px; }

            /* CTA */
            .cta-box   { padding: 30px 16px; border-radius: 16px; }
            .cta-title { font-size: 22px; }

            /* Footer */
            .footer      { padding: 32px 16px 16px; }
            .footer-links { gap: 12px; font-size: 12px; }
            .footer-text  { font-size: 12px; }
        }
    </style>
</head>
<body>
    <div class="bg-pattern"></div>
    <div class="grid-overlay"></div>
    
    <!-- Navigation -->
    <nav class="nav">
        <div class="logo">
            <img src="/logo.png" alt="Hadir" class="logo-img">
            <span class="logo-text">{{ app()->getLocale() == 'ar' ? 'نظام حاضر' : 'Hadir' }}</span>
        </div>
        
        <div class="nav-links">
            <a href="#features">{{ app()->getLocale() == 'ar' ? 'المميزات' : 'Features' }}</a>
            <a href="#devices">{{ app()->getLocale() == 'ar' ? 'الأجهزة' : 'Devices' }}</a>
            <a href="#pricing">{{ app()->getLocale() == 'ar' ? 'الأسعار' : 'Pricing' }}</a>
        </div>
        
        <div class="nav-buttons">
            <div class="lang-switch">
                <a href="{{ route('lang.switch', 'ar') }}" class="lang-btn {{ app()->getLocale() == 'ar' ? 'active' : '' }}">العربية</a>
                <a href="{{ route('lang.switch', 'en') }}" class="lang-btn {{ app()->getLocale() == 'en' ? 'active' : '' }}">EN</a>
            </div>
            <a href="{{ route('login') }}" class="btn btn-outline">{{ app()->getLocale() == 'ar' ? 'تسجيل الدخول' : 'Login' }}</a>
            <a href="{{ route('register') }}" class="btn btn-primary">{{ app()->getLocale() == 'ar' ? 'ابدأ مجاناً' : 'Start Free' }}</a>
        </div>
        <!-- Hamburger (mobile only) -->
        <button class="hamburger-menu" id="hamburgerBtn" onclick="openMobileNav()" aria-label="Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </nav>

    <!-- Mobile Navigation Drawer -->
    <div class="mobile-nav" id="mobileNav">
        <button class="mobile-nav-close" onclick="closeMobileNav()" aria-label="Close">✕</button>
        <a href="#features" class="mnav-link" onclick="closeMobileNav()">{{ app()->getLocale() == 'ar' ? '✨ المميزات' : '✨ Features' }}</a>
        <a href="#devices"  class="mnav-link" onclick="closeMobileNav()">{{ app()->getLocale() == 'ar' ? '🔧 الأجهزة' : '🔧 Devices' }}</a>
        <a href="#pricing"  class="mnav-link" onclick="closeMobileNav()">{{ app()->getLocale() == 'ar' ? '💰 الأسعار' : '💰 Pricing' }}</a>
        <div class="mobile-nav-btns">
            <a href="{{ route('login') }}"    class="btn btn-outline">{{ app()->getLocale() == 'ar' ? 'تسجيل الدخول' : 'Login' }}</a>
            <a href="{{ route('register') }}" class="btn btn-primary">🚀 {{ app()->getLocale() == 'ar' ? 'ابدأ مجاناً' : 'Start Free' }}</a>
        </div>
        <div class="mobile-nav-lang">
            <a href="{{ route('lang.switch', 'ar') }}" class="lang-btn {{ app()->getLocale() == 'ar' ? 'active' : '' }}">العربية</a>
            <a href="{{ route('lang.switch', 'en') }}" class="lang-btn {{ app()->getLocale() == 'en' ? 'active' : '' }}">English</a>
        </div>
    </div>
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1>
                    {{ app()->getLocale() == 'ar' ? 'نظام موارد بشرية' : 'Complete HR System' }}<br>
                    <span>{{ app()->getLocale() == 'ar' ? 'متكامل وذكي' : 'Smart & Integrated' }}</span>
                </h1>
                <p>
                    {{ app()->getLocale() == 'ar' 
                        ? 'نظام متكامل لإدارة الموارد البشرية: حضور وانصراف، رواتب، إجازات، سلف، ورديات، عقود، وأذونات مع دعم أجهزة ZKTeco. كل ما تحتاجه في مكان واحد.'
                        : 'All-in-one HR management: attendance, payroll, leaves, advances, shifts, contracts, and permissions with ZKTeco device support. Everything you need in one place.' 
                    }}
                </p>
                <div class="hero-buttons">
                    <a href="{{ route('register') }}" class="btn btn-secondary">
                        🚀 {{ app()->getLocale() == 'ar' ? 'تجربة مجانية 14 يوم' : '14-Day Free Trial' }}
                    </a>
                    <a href="#features" class="btn btn-outline">
                        {{ app()->getLocale() == 'ar' ? 'اكتشف المزيد' : 'Learn More' }}
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-value">500+</div>
                        <div class="stat-label">{{ app()->getLocale() == 'ar' ? 'شركة' : 'Companies' }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">50K+</div>
                        <div class="stat-label">{{ app()->getLocale() == 'ar' ? 'موظف' : 'Employees' }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">99.9%</div>
                        <div class="stat-label">{{ app()->getLocale() == 'ar' ? 'وقت التشغيل' : 'Uptime' }}</div>
                    </div>
                </div>
            </div>
            
            <div class="hero-visual">
                <div class="device-showcase">
                    <div class="device-card">
                        <div class="device-header">
                            <span class="device-title">{{ app()->getLocale() == 'ar' ? 'جهاز البصمة الرئيسي' : 'Main Biometric Device' }}</span>
                            <span class="device-status">
                                <span class="status-dot"></span>
                                {{ app()->getLocale() == 'ar' ? 'متصل' : 'Online' }}
                            </span>
                        </div>
                        <div class="device-image">
                            <div class="fingerprint-icon">👆</div>
                        </div>
                        <div class="device-stats">
                            <div class="device-stat">
                                <div class="device-stat-value">248</div>
                                <div class="device-stat-label">{{ app()->getLocale() == 'ar' ? 'حضور اليوم' : 'Today' }}</div>
                            </div>
                            <div class="device-stat">
                                <div class="device-stat-value">12</div>
                                <div class="device-stat-label">{{ app()->getLocale() == 'ar' ? 'متأخرين' : 'Late' }}</div>
                            </div>
                            <div class="device-stat">
                                <div class="device-stat-value">5</div>
                                <div class="device-stat-label">{{ app()->getLocale() == 'ar' ? 'غائبين' : 'Absent' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="float-card top-right">
                        <div class="float-icon green">✓</div>
                        <div class="float-text">
                            <strong>{{ app()->getLocale() == 'ar' ? 'تسجيل حضور' : 'Check-in' }}</strong>
                            <span>{{ app()->getLocale() == 'ar' ? 'محمد أحمد - 08:02' : 'John Doe - 08:02 AM' }}</span>
                        </div>
                    </div>
                    
                    <div class="float-card bottom-left">
                        <div class="float-icon blue">📊</div>
                        <div class="float-text">
                            <strong>{{ app()->getLocale() == 'ar' ? 'تقرير جاهز' : 'Report Ready' }}</strong>
                            <span>{{ app()->getLocale() == 'ar' ? 'تقرير الحضور الشهري' : 'Monthly attendance report' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="features" id="features">
        <div class="section-container">
            <div class="section-header">
                <span class="section-badge">{{ app()->getLocale() == 'ar' ? '✨ المميزات' : '✨ Features' }}</span>
                <h2 class="section-title">{{ app()->getLocale() == 'ar' ? 'نظام موارد بشرية شامل' : 'Complete HR Platform' }}</h2>
                <p class="section-desc">{{ app()->getLocale() == 'ar' ? 'كل ما تحتاجه لإدارة الموارد البشرية والحضور والرواتب في منصة واحدة متكاملة' : 'Everything you need to manage HR, attendance, and payroll in one integrated platform' }}</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon purple">🔗</div>
                    <h3 class="feature-title">{{ app()->getLocale() == 'ar' ? 'تكامل ZKTeco' : 'ZKTeco Integration' }}</h3>
                    <p class="feature-desc">{{ app()->getLocale() == 'ar' ? 'ربط تلقائي مع أجهزة البصمة والوجه مع مزامنة فورية' : 'Auto-sync with fingerprint and face recognition devices' }}</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon green">💰</div>
                    <h3 class="feature-title">{{ app()->getLocale() == 'ar' ? 'نظام الرواتب' : 'Payroll System' }}</h3>
                    <p class="feature-desc">{{ app()->getLocale() == 'ar' ? 'حساب تلقائي للرواتب مع البدلات والخصومات والسلف والعمل الإضافي' : 'Auto salary calculation with allowances, deductions, advances, and overtime' }}</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon blue">🏖️</div>
                    <h3 class="feature-title">{{ app()->getLocale() == 'ar' ? 'إدارة الإجازات' : 'Leave Management' }}</h3>
                    <p class="feature-desc">{{ app()->getLocale() == 'ar' ? 'طلبات إجازة إلكترونية مع أرصدة تلقائية وسير عمل للموافقات' : 'Digital leave requests with auto balances and approval workflows' }}</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon orange">👥</div>
                    <h3 class="feature-title">{{ app()->getLocale() == 'ar' ? 'إدارة الموظفين' : 'Employee Management' }}</h3>
                    <p class="feature-desc">{{ app()->getLocale() == 'ar' ? 'ملفات شاملة للموظفين مع الوثائق والبيانات الشخصية والوظيفية' : 'Complete employee profiles with documents and personal data' }}</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon pink">⏰</div>
                    <h3 class="feature-title">{{ app()->getLocale() == 'ar' ? 'جداول الورديات' : 'Shift Schedules' }}</h3>
                    <p class="feature-desc">{{ app()->getLocale() == 'ar' ? 'ورديات مرنة مع مناوبات ودعم العمل الإضافي والتبديل' : 'Flexible shifts with rotations, overtime, and swap support' }}</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon amber">💵</div>
                    <h3 class="feature-title">{{ app()->getLocale() == 'ar' ? 'السلف والمقدمات' : 'Salary Advances' }}</h3>
                    <p class="feature-desc">{{ app()->getLocale() == 'ar' ? 'نظام سلف متكامل مع أقساط وخصم تلقائي من الراتب' : 'Advance system with installments and auto payroll deduction' }}</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon cyan">📊</div>
                    <h3 class="feature-title">{{ app()->getLocale() == 'ar' ? 'تقارير متقدمة' : 'Advanced Reports' }}</h3>
                    <p class="feature-desc">{{ app()->getLocale() == 'ar' ? 'تقارير تفصيلية ورسوم بيانية تفاعلية مع تصدير متعدد' : 'Detailed reports with interactive charts and multi-format export' }}</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon red">📝</div>
                    <h3 class="feature-title">{{ app()->getLocale() == 'ar' ? 'إدارة العقود' : 'Contract Management' }}</h3>
                    <p class="feature-desc">{{ app()->getLocale() == 'ar' ? 'تتبع عقود الموظفين مع تنبيهات الانتهاء والتجديد التلقائي' : 'Track employee contracts with expiry alerts and auto-renewal' }}</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon teal">🏢</div>
                    <h3 class="feature-title">{{ app()->getLocale() == 'ar' ? 'إدارة الفروع والأقسام' : 'Branches & Departments' }}</h3>
                    <p class="feature-desc">{{ app()->getLocale() == 'ar' ? 'إدارة متعددة الفروع والأقسام مع لوحة تحكم مركزية' : 'Multi-branch and department management with central dashboard' }}</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon indigo">✋</div>
                    <h3 class="feature-title">{{ app()->getLocale() == 'ar' ? 'الأذونات والتصحيحات' : 'Permissions & Corrections' }}</h3>
                    <p class="feature-desc">{{ app()->getLocale() == 'ar' ? 'طلبات أذونات خروج وتصحيح حضور إلكترونية مع موافقات' : 'Digital permission requests and attendance corrections with approvals' }}</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon emerald">🔔</div>
                    <h3 class="feature-title">{{ app()->getLocale() == 'ar' ? 'التنبيهات والإشعارات' : 'Smart Notifications' }}</h3>
                    <p class="feature-desc">{{ app()->getLocale() == 'ar' ? 'إشعارات فورية للتأخير والغياب والطلبات عبر البريد والنظام' : 'Instant alerts for lateness, absence, and requests via email & app' }}</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon violet">⭐</div>
                    <h3 class="feature-title">{{ app()->getLocale() == 'ar' ? 'نقاط العمل والمهام' : 'Work Points & Tasks' }}</h3>
                    <p class="feature-desc">{{ app()->getLocale() == 'ar' ? 'نظام تقييم أداء بالنقاط وتوزيع مهام العمل ومتابعتها' : 'Performance scoring system with task assignment and tracking' }}</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Devices Section -->
    <section class="devices" id="devices">
        <div class="section-container">
            <div class="section-header">
                <span class="section-badge">{{ app()->getLocale() == 'ar' ? '🔧 الأجهزة المدعومة' : '🔧 Supported Devices' }}</span>
                <h2 class="section-title">{{ app()->getLocale() == 'ar' ? 'متوافق مع جميع أجهزة ZKTeco' : 'Compatible with All ZKTeco Devices' }}</h2>
                <p class="section-desc">{{ app()->getLocale() == 'ar' ? 'دعم كامل لأحدث أجهزة البصمة والتعرف على الوجه' : 'Full support for the latest fingerprint and face recognition devices' }}</p>
            </div>
            
            <div class="devices-showcase">
                <div class="device-item">
                    <div class="device-icon-large">👆</div>
                    <div class="device-name">ZK-F18</div>
                    <div class="device-type">{{ app()->getLocale() == 'ar' ? 'بصمة الإصبع' : 'Fingerprint' }}</div>
                </div>
                <div class="device-item">
                    <div class="device-icon-large">👤</div>
                    <div class="device-name">SpeedFace V5L</div>
                    <div class="device-type">{{ app()->getLocale() == 'ar' ? 'التعرف على الوجه' : 'Face Recognition' }}</div>
                </div>
                <div class="device-item">
                    <div class="device-icon-large">🖐️</div>
                    <div class="device-name">ZK-G3 Pro</div>
                    <div class="device-type">{{ app()->getLocale() == 'ar' ? 'بصمة وكرت' : 'Fingerprint + Card' }}</div>
                </div>
                <div class="device-item">
                    <div class="device-icon-large">📷</div>
                    <div class="device-name">ProFace X</div>
                    <div class="device-type">{{ app()->getLocale() == 'ar' ? 'وجه + حرارة' : 'Face + Thermal' }}</div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Pricing Section -->
    <section class="pricing" id="pricing">
        <div class="section-container">
            <div class="section-header">
                <span class="section-badge">{{ app()->getLocale() == 'ar' ? '💰 الأسعار' : '💰 Pricing' }}</span>
                <h2 class="section-title">{{ app()->getLocale() == 'ar' ? 'خطط تناسب احتياجاتك' : 'Plans That Fit Your Needs' }}</h2>
                <p class="section-desc">{{ app()->getLocale() == 'ar' ? 'ابدأ بتجربة مجانية 14 يوم - جميع الأسعار بالريال السعودي' : 'Start with a 14-day free trial - All prices in SAR' }}</p>
            </div>

            {{-- Billing Cycle Toggle --}}
            <div style="display: flex; justify-content: center; margin-bottom: 40px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 4px; background: rgba(255,255,255,0.05); padding: 4px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); flex-wrap: wrap; max-width: 100%;">
                    <button onclick="switchLandingCycle('monthly')" id="lp-btn-monthly" class="lp-cycle-btn" style="background: var(--primary); color: white;">
                        {{ app()->getLocale() == 'ar' ? 'شهري' : 'Monthly' }}
                    </button>
                    <button onclick="switchLandingCycle('quarterly')" id="lp-btn-quarterly" class="lp-cycle-btn" style="background: transparent; color: rgba(255,255,255,0.6);">
                        {{ app()->getLocale() == 'ar' ? '3 أشهر' : '3 Months' }}
                    </button>
                    <button onclick="switchLandingCycle('semi_annual')" id="lp-btn-semi_annual" class="lp-cycle-btn" style="background: transparent; color: rgba(255,255,255,0.6);">
                        {{ app()->getLocale() == 'ar' ? '6 أشهر' : '6 Months' }}
                    </button>
                    <button onclick="switchLandingCycle('yearly')" id="lp-btn-yearly" class="lp-cycle-btn" style="background: transparent; color: rgba(255,255,255,0.6);">
                        {{ app()->getLocale() == 'ar' ? 'سنوي' : 'Yearly' }}
                        <span style="background: rgba(52,211,153,0.3); color: #34d399; padding: 2px 8px; border-radius: 6px; font-size: 10px; margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 6px;">{{ app()->getLocale() == 'ar' ? 'أفضل قيمة' : 'Best Value' }}</span>
                    </button>
                </div>
            </div>
            
            @php
            $isAr = app()->getLocale() == 'ar';
            $featureLabels = [
                'email_support' => $isAr ? 'دعم بريد إلكتروني' : 'Email Support',
                'api_access' => $isAr ? 'وصول API' : 'API Access',
                'advanced_reports' => $isAr ? 'تقارير متقدمة' : 'Advanced Reports',
                'shift_management' => $isAr ? 'إدارة الورديات' : 'Shift Management',
                'chat_support' => $isAr ? 'دعم محادثة مباشرة' : 'Live Chat Support',
                'time_off_management' => $isAr ? 'إدارة الإجازات والأذونات' : 'Leaves & Permissions',
                'payroll' => $isAr ? 'نظام الرواتب' : 'Payroll System',
                'salary_advances' => $isAr ? 'السلف والمقدمات' : 'Salary Advances',
                'contracts' => $isAr ? 'إدارة العقود' : 'Contract Management',
                'work_points' => $isAr ? 'نقاط العمل' : 'Work Points',
                'work_tasks' => $isAr ? 'مهام العمل' : 'Work Tasks',
                'priority_support' => $isAr ? 'دعم أولوية' : 'Priority Support',
                'custom_branding' => $isAr ? 'علامة تجارية مخصصة' : 'Custom Branding',
                'white_label' => $isAr ? 'العلامة البيضاء' : 'White Label',
                'dedicated_support' => $isAr ? 'دعم فني مخصص' : 'Dedicated Support',
                'sla_guarantee' => $isAr ? 'ضمان مستوى الخدمة' : 'SLA Guarantee',
                'onboarding_assistance' => $isAr ? 'مساعدة في التفعيل' : 'Onboarding Assistance',
            ];
            $planDescEn = [
                'basic' => 'Perfect for startups & small businesses',
                'advanced' => 'Ideal for growing businesses',
                'professional' => 'For large multi-branch companies',
                'enterprise' => 'Complete solution for enterprises',
            ];
            @endphp

            <div class="pricing-grid" style="grid-template-columns: repeat({{ $plans->count() > 0 ? min($plans->count(), 4) : 3 }}, 1fr);">
                @foreach($plans as $plan)
                <div class="pricing-card {{ $plan->is_featured ? 'featured' : '' }}">
                    <div class="pricing-name">{{ $plan->name }}</div>
                    <div class="pricing-desc">
                        {{ $isAr ? $plan->description : ($planDescEn[$plan->slug] ?? 'Up to ' . $plan->max_devices . ' devices & ' . $plan->max_employees . ' employees') }}
                    </div>
                    <div class="lp-price-monthly" style="display: block;">
                        <div class="pricing-price">{{ number_format($plan->price_monthly, 0) }}<span> {{ $plan->currency ?? 'SAR' }}/{{ $isAr ? 'شهر' : 'mo' }}</span></div>
                    </div>
                    <div class="lp-price-quarterly" style="display: none;">
                        <div class="pricing-price">{{ number_format($plan->price_quarterly, 0) }}<span> {{ $plan->currency ?? 'SAR' }}/{{ $isAr ? '3 أشهر' : '3 mo' }}</span></div>
                        @if($plan->price_monthly > 0)
                        <div style="color: rgba(255,255,255,0.5); font-size: 13px; margin-bottom: 4px;">≈ {{ number_format(round($plan->price_quarterly / 3), 0) }} {{ $plan->currency ?? 'SAR' }}/{{ $isAr ? 'شهر' : 'mo' }}</div>
                        @endif
                        @if($plan->getSavingsForCycle('quarterly') > 0)
                        <div style="color: #34d399; font-size: 13px; font-weight: 600; margin-bottom: 8px;">{{ $isAr ? 'وفّر' : 'Save' }} {{ number_format($plan->getSavingsForCycle('quarterly'), 0) }} {{ $plan->currency ?? 'SAR' }}</div>
                        @endif
                    </div>
                    <div class="lp-price-semi_annual" style="display: none;">
                        <div class="pricing-price">{{ number_format($plan->price_semi_annual, 0) }}<span> {{ $plan->currency ?? 'SAR' }}/{{ $isAr ? '6 أشهر' : '6 mo' }}</span></div>
                        @if($plan->price_monthly > 0)
                        <div style="color: rgba(255,255,255,0.5); font-size: 13px; margin-bottom: 4px;">≈ {{ number_format(round($plan->price_semi_annual / 6), 0) }} {{ $plan->currency ?? 'SAR' }}/{{ $isAr ? 'شهر' : 'mo' }}</div>
                        @endif
                        @if($plan->getSavingsForCycle('semi_annual') > 0)
                        <div style="color: #34d399; font-size: 13px; font-weight: 600; margin-bottom: 8px;">{{ $isAr ? 'وفّر' : 'Save' }} {{ number_format($plan->getSavingsForCycle('semi_annual'), 0) }} {{ $plan->currency ?? 'SAR' }}</div>
                        @endif
                    </div>
                    <div class="lp-price-yearly" style="display: none;">
                        <div class="pricing-price">{{ number_format($plan->price_yearly, 0) }}<span> {{ $plan->currency ?? 'SAR' }}/{{ $isAr ? 'سنة' : 'yr' }}</span></div>
                        @if($plan->price_monthly > 0)
                        <div style="color: rgba(255,255,255,0.5); font-size: 13px; margin-bottom: 4px;">≈ {{ number_format(round($plan->price_yearly / 12), 0) }} {{ $plan->currency ?? 'SAR' }}/{{ $isAr ? 'شهر' : 'mo' }}</div>
                        @endif
                        @if($plan->getYearlySavings() > 0)
                        <div style="color: #34d399; font-size: 13px; font-weight: 600; margin-bottom: 8px;">{{ $isAr ? 'وفّر' : 'Save' }} {{ number_format($plan->getYearlySavings(), 0) }} {{ $plan->currency ?? 'SAR' }}</div>
                        @endif
                    </div>
                    <ul class="pricing-features">
                        <li><span class="check-icon">✓</span> {{ $isAr ? 'حتى ' . $plan->max_devices . ' أجهزة' : 'Up to ' . $plan->max_devices . ' devices' }}</li>
                        <li><span class="check-icon">✓</span> {{ $isAr ? 'حتى ' . $plan->max_employees . ' موظف' : 'Up to ' . $plan->max_employees . ' employees' }}</li>
                        <li><span class="check-icon">✓</span> {{ $isAr ? $plan->retention_days . ' يوم أرشيف' : $plan->retention_days . ' days retention' }}</li>
                        @foreach($plan->features ?? [] as $feature)
                            @if(isset($featureLabels[$feature]))
                            <li><span class="check-icon">✓</span> {{ $featureLabels[$feature] }}</li>
                            @endif
                        @endforeach
                    </ul>
                    <a href="{{ route('register') }}" class="btn {{ $plan->is_featured ? 'btn-primary' : 'btn-outline' }}" style="width: 100%;">{{ $isAr ? 'ابدأ الآن' : 'Get Started' }}</a>
                </div>
                @endforeach
            </div>

            {{-- Plan Comparison Table --}}
            @if($plans->count() > 1)
            <div style="margin-top: 60px; max-width: 1000px; margin-left: auto; margin-right: auto;">
                <h3 style="text-align: center; font-size: 24px; font-weight: 700; margin-bottom: 32px; color: #fff;">
                    {{ $isAr ? 'مقارنة الباقات' : 'Compare Plans' }}
                </h3>
                <div style="overflow-x: auto; border-radius: 16px; border: 1px solid rgba(255,255,255,0.08);">
                    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                        <thead>
                            <tr style="background: rgba(255,255,255,0.05);">
                                <th style="padding: 16px 20px; text-align: {{ $isAr ? 'right' : 'left' }}; color: rgba(255,255,255,0.6); font-weight: 600; border-bottom: 1px solid rgba(255,255,255,0.08);">
                                    {{ $isAr ? 'الميزة' : 'Feature' }}
                                </th>
                                @foreach($plans as $plan)
                                <th style="padding: 16px 20px; text-align: center; color: {{ $plan->is_featured ? 'var(--primary-light)' : '#fff' }}; font-weight: 700; border-bottom: 1px solid rgba(255,255,255,0.08); {{ $plan->is_featured ? 'background: rgba(99,102,241,0.08);' : '' }}">
                                    {{ $plan->name }}
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding: 12px 20px; color: rgba(255,255,255,0.7); border-bottom: 1px solid rgba(255,255,255,0.05);">{{ $isAr ? 'الأجهزة' : 'Devices' }}</td>
                                @foreach($plans as $plan)
                                <td style="padding: 12px 20px; text-align: center; color: #fff; font-weight: 600; border-bottom: 1px solid rgba(255,255,255,0.05); {{ $plan->is_featured ? 'background: rgba(99,102,241,0.04);' : '' }}">{{ $plan->max_devices }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td style="padding: 12px 20px; color: rgba(255,255,255,0.7); border-bottom: 1px solid rgba(255,255,255,0.05);">{{ $isAr ? 'الموظفين' : 'Employees' }}</td>
                                @foreach($plans as $plan)
                                <td style="padding: 12px 20px; text-align: center; color: #fff; font-weight: 600; border-bottom: 1px solid rgba(255,255,255,0.05); {{ $plan->is_featured ? 'background: rgba(99,102,241,0.04);' : '' }}">{{ $plan->max_employees }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td style="padding: 12px 20px; color: rgba(255,255,255,0.7); border-bottom: 1px solid rgba(255,255,255,0.05);">{{ $isAr ? 'أرشيف البيانات' : 'Data Retention' }}</td>
                                @foreach($plans as $plan)
                                <td style="padding: 12px 20px; text-align: center; color: #fff; font-weight: 600; border-bottom: 1px solid rgba(255,255,255,0.05); {{ $plan->is_featured ? 'background: rgba(99,102,241,0.04);' : '' }}">{{ $plan->retention_days }} {{ $isAr ? 'يوم' : 'days' }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td style="padding: 12px 20px; color: rgba(255,255,255,0.7); border-bottom: 1px solid rgba(255,255,255,0.05);">{{ $isAr ? 'شهري' : 'Monthly' }}</td>
                                @foreach($plans as $plan)
                                <td style="padding: 12px 20px; text-align: center; color: var(--primary-light); font-weight: 700; border-bottom: 1px solid rgba(255,255,255,0.05); {{ $plan->is_featured ? 'background: rgba(99,102,241,0.04);' : '' }}">{{ number_format($plan->price_monthly, 0) }} {{ $plan->currency ?? 'SAR' }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td style="padding: 12px 20px; color: rgba(255,255,255,0.7); border-bottom: 1px solid rgba(255,255,255,0.05);">{{ $isAr ? '3 أشهر' : 'Quarterly' }}</td>
                                @foreach($plans as $plan)
                                <td style="padding: 12px 20px; text-align: center; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.05); {{ $plan->is_featured ? 'background: rgba(99,102,241,0.04);' : '' }}">{{ number_format($plan->price_quarterly, 0) }} {{ $plan->currency ?? 'SAR' }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td style="padding: 12px 20px; color: rgba(255,255,255,0.7); border-bottom: 1px solid rgba(255,255,255,0.05);">{{ $isAr ? '6 أشهر' : 'Semi-Annual' }}</td>
                                @foreach($plans as $plan)
                                <td style="padding: 12px 20px; text-align: center; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.05); {{ $plan->is_featured ? 'background: rgba(99,102,241,0.04);' : '' }}">{{ number_format($plan->price_semi_annual, 0) }} {{ $plan->currency ?? 'SAR' }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td style="padding: 12px 20px; color: rgba(255,255,255,0.7); border-bottom: 1px solid rgba(255,255,255,0.05);">{{ $isAr ? 'سنوي' : 'Yearly' }}</td>
                                @foreach($plans as $plan)
                                <td style="padding: 12px 20px; text-align: center; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.05); {{ $plan->is_featured ? 'background: rgba(99,102,241,0.04);' : '' }}">{{ number_format($plan->price_yearly, 0) }} {{ $plan->currency ?? 'SAR' }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td style="padding: 12px 20px; color: rgba(255,255,255,0.7); border-bottom: 1px solid rgba(255,255,255,0.05);">{{ $isAr ? 'إدارة الورديات' : 'Shift Management' }}</td>
                                @foreach($plans as $plan)
                                <td style="padding: 12px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.05); {{ $plan->is_featured ? 'background: rgba(99,102,241,0.04);' : '' }}">{!! $plan->hasFeature('shift_management') ? '<span style="color:#34d399;">✓</span>' : '<span style="color:rgba(255,255,255,0.2);">—</span>' !!}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td style="padding: 12px 20px; color: rgba(255,255,255,0.7); border-bottom: 1px solid rgba(255,255,255,0.05);">{{ $isAr ? 'إدارة الإجازات والأذونات' : 'Leaves & Permissions' }}</td>
                                @foreach($plans as $plan)
                                <td style="padding: 12px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.05); {{ $plan->is_featured ? 'background: rgba(99,102,241,0.04);' : '' }}">{!! $plan->hasFeature('time_off_management') ? '<span style="color:#34d399;">✓</span>' : '<span style="color:rgba(255,255,255,0.2);">—</span>' !!}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td style="padding: 12px 20px; color: rgba(255,255,255,0.7); border-bottom: 1px solid rgba(255,255,255,0.05);">{{ $isAr ? 'نظام الرواتب' : 'Payroll System' }}</td>
                                @foreach($plans as $plan)
                                <td style="padding: 12px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.05); {{ $plan->is_featured ? 'background: rgba(99,102,241,0.04);' : '' }}">{!! $plan->hasFeature('payroll') ? '<span style="color:#34d399;">✓</span>' : '<span style="color:rgba(255,255,255,0.2);">—</span>' !!}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td style="padding: 12px 20px; color: rgba(255,255,255,0.7); border-bottom: 1px solid rgba(255,255,255,0.05);">{{ $isAr ? 'السلف والمقدمات' : 'Salary Advances' }}</td>
                                @foreach($plans as $plan)
                                <td style="padding: 12px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.05); {{ $plan->is_featured ? 'background: rgba(99,102,241,0.04);' : '' }}">{!! $plan->hasFeature('salary_advances') ? '<span style="color:#34d399;">✓</span>' : '<span style="color:rgba(255,255,255,0.2);">—</span>' !!}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td style="padding: 12px 20px; color: rgba(255,255,255,0.7); border-bottom: 1px solid rgba(255,255,255,0.05);">{{ $isAr ? 'إدارة العقود' : 'Contract Management' }}</td>
                                @foreach($plans as $plan)
                                <td style="padding: 12px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.05); {{ $plan->is_featured ? 'background: rgba(99,102,241,0.04);' : '' }}">{!! $plan->hasFeature('contracts') ? '<span style="color:#34d399;">✓</span>' : '<span style="color:rgba(255,255,255,0.2);">—</span>' !!}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td style="padding: 12px 20px; color: rgba(255,255,255,0.7); border-bottom: 1px solid rgba(255,255,255,0.05);">{{ $isAr ? 'نقاط ومهام العمل' : 'Work Points & Tasks' }}</td>
                                @foreach($plans as $plan)
                                <td style="padding: 12px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.05); {{ $plan->is_featured ? 'background: rgba(99,102,241,0.04);' : '' }}">{!! $plan->hasFeature('work_points') ? '<span style="color:#34d399;">✓</span>' : '<span style="color:rgba(255,255,255,0.2);">—</span>' !!}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td style="padding: 12px 20px; color: rgba(255,255,255,0.7); border-bottom: 1px solid rgba(255,255,255,0.05);">{{ $isAr ? 'تقارير متقدمة' : 'Advanced Reports' }}</td>
                                @foreach($plans as $plan)
                                <td style="padding: 12px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.05); {{ $plan->is_featured ? 'background: rgba(99,102,241,0.04);' : '' }}">{!! $plan->hasFeature('advanced_reports') ? '<span style="color:#34d399;">✓</span>' : '<span style="color:rgba(255,255,255,0.2);">—</span>' !!}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td style="padding: 12px 20px; color: rgba(255,255,255,0.7); border-bottom: 1px solid rgba(255,255,255,0.05);">{{ $isAr ? 'وصول API' : 'API Access' }}</td>
                                @foreach($plans as $plan)
                                <td style="padding: 12px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.05); {{ $plan->is_featured ? 'background: rgba(99,102,241,0.04);' : '' }}">{!! $plan->hasFeature('api_access') ? '<span style="color:#34d399;">✓</span>' : '<span style="color:rgba(255,255,255,0.2);">—</span>' !!}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td style="padding: 12px 20px; color: rgba(255,255,255,0.7); border-bottom: 1px solid rgba(255,255,255,0.05);">{{ $isAr ? 'دعم أولوية' : 'Priority Support' }}</td>
                                @foreach($plans as $plan)
                                <td style="padding: 12px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.05); {{ $plan->is_featured ? 'background: rgba(99,102,241,0.04);' : '' }}">{!! $plan->hasFeature('priority_support') ? '<span style="color:#34d399;">✓</span>' : '<span style="color:rgba(255,255,255,0.2);">—</span>' !!}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td style="padding: 12px 20px; color: rgba(255,255,255,0.7);">{{ $isAr ? 'علامة تجارية مخصصة' : 'Custom Branding' }}</td>
                                @foreach($plans as $plan)
                                <td style="padding: 12px 20px; text-align: center; {{ $plan->is_featured ? 'background: rgba(99,102,241,0.04);' : '' }}">{!! $plan->hasFeature('custom_branding') ? '<span style="color:#34d399;">✓</span>' : '<span style="color:rgba(255,255,255,0.2);">—</span>' !!}</td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </section>

    <script>
    // Mobile nav toggle
    function openMobileNav() {
        var nav = document.getElementById('mobileNav');
        var btn = document.getElementById('hamburgerBtn');
        nav.classList.add('open');
        btn.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeMobileNav() {
        var nav = document.getElementById('mobileNav');
        var btn = document.getElementById('hamburgerBtn');
        nav.classList.remove('open');
        btn.classList.remove('open');
        document.body.style.overflow = '';
    }
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) closeMobileNav();
    });
    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeMobileNav();
    });

    function switchLandingCycle(cycle) {
        const cycles = ['monthly', 'quarterly', 'semi_annual', 'yearly'];
        cycles.forEach(c => {
            const btn = document.getElementById('lp-btn-' + c);
            if (btn) {
                if (c === cycle) {
                    btn.style.background = 'var(--primary)';
                    btn.style.color = 'white';
                } else {
                    btn.style.background = 'transparent';
                    btn.style.color = 'rgba(255,255,255,0.6)';
                }
            }
            document.querySelectorAll('.lp-price-' + c).forEach(el => {
                el.style.display = (c === cycle) ? 'block' : 'none';
            });
        });
    }
    </script>
    
    <!-- CTA Section -->
    <section class="cta">
        <div class="cta-box">
            <div class="cta-content">
                <h2 class="cta-title">{{ app()->getLocale() == 'ar' ? 'جاهز للبدء؟' : 'Ready to Get Started?' }}</h2>
                <p class="cta-desc">{{ app()->getLocale() == 'ar' ? 'انضم لأكثر من 500 شركة تدير مواردها البشرية بذكاء مع حاضر' : 'Join over 500 companies managing their HR smartly with Hadir' }}</p>
                <a href="{{ route('register') }}" class="btn btn-secondary" style="font-size: 18px; padding: 18px 40px;">
                    🚀 {{ app()->getLocale() == 'ar' ? 'ابدأ تجربتك المجانية' : 'Start Your Free Trial' }}
                </a>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-text">© {{ date('Y') }} {{ app()->getLocale() == 'ar' ? 'حاضر. جميع الحقوق محفوظة.' : 'Hadir. All rights reserved.' }}</div>
            <div class="footer-links">
                <a href="tel:+966542999195">📱 +966 54 299 9195</a>
                <a href="https://wa.me/966542999195" target="_blank" style="color: #25d366;">💬 {{ app()->getLocale() == 'ar' ? 'واتساب' : 'WhatsApp' }}</a>
                <a href="#">{{ app()->getLocale() == 'ar' ? 'سياسة الخصوصية' : 'Privacy Policy' }}</a>
                <a href="#">{{ app()->getLocale() == 'ar' ? 'الشروط والأحكام' : 'Terms of Service' }}</a>
                <a href="mailto:support@uhdor.com">{{ app()->getLocale() == 'ar' ? 'تواصل معنا' : 'Contact Us' }}</a>
            </div>
        </div>
    </footer>

    <!-- WhatsApp Floating Button -->
    <a href="https://wa.me/966542999195" target="_blank" class="whatsapp-float" title="{{ app()->getLocale() == 'ar' ? 'تواصل معنا عبر واتساب' : 'Chat with us on WhatsApp' }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="white">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
    </a>

    <style>
    .whatsapp-float {
        position: fixed;
        bottom: 30px;
        {{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 30px;
        width: 60px;
        height: 60px;
        background: #25d366;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 20px rgba(37, 211, 102, 0.4);
        z-index: 9999;
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .whatsapp-float:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 30px rgba(37, 211, 102, 0.6);
    }
    </style>
</body>
</html>
