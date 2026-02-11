<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ app()->getLocale() == 'ar' ? 'نظام حاضر - إدارة الحضور الذكية' : 'Dawam - Smart Attendance Management' }}</title>
    <meta name="description" content="{{ app()->getLocale() == 'ar' ? 'نظام حضور وانصراف متكامل مع أجهزة البصمة ZKTeco - حلول ذكية للشركات' : 'Complete attendance management system with ZKTeco biometric integration - Smart solutions for enterprises' }}">
    
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
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
        
        html { scroll-behavior: smooth; }
        
        body {
            font-family: {!! "'Tajawal', -apple-system, BlinkMacSystemFont, sans-serif" !!};
            background: var(--bg-dark);
            color: var(--text-light);
            line-height: 1.6;
            overflow-x: hidden;
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
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
        }
        
        .feature-card {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.6), rgba(15, 23, 42, 0.6));
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            padding: 36px;
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
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
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
        
        /* Responsive */
        @media (max-width: 1024px) {
            .hero-container { grid-template-columns: 1fr; text-align: center; }
            .hero-content p { margin: 0 auto 40px; }
            .hero-buttons { justify-content: center; }
            .hero-stats { justify-content: center; }
            .hero-visual { display: none; }
            .features-grid { grid-template-columns: repeat(2, 1fr); }
            .devices-showcase { grid-template-columns: repeat(2, 1fr); }
            .pricing-grid { grid-template-columns: 1fr; max-width: 400px; margin-left: auto; margin-right: auto; }
            .pricing-card.featured { transform: scale(1); }
        }
        
        @media (max-width: 768px) {
            .nav { padding: 16px 20px; }
            .nav-links { display: none; }
            .hero { padding: 120px 20px 60px; }
            .hero-content h1 { font-size: 36px; }
            .section-title { font-size: 32px; }
            .features-grid { grid-template-columns: 1fr; }
            .devices-showcase { grid-template-columns: 1fr; }
            .cta-box { padding: 40px 24px; }
            .cta-title { font-size: 28px; }
        }
    </style>
</head>
<body>
    <div class="bg-pattern"></div>
    <div class="grid-overlay"></div>
    
    <!-- Navigation -->
    <nav class="nav">
        <div class="logo">
            <img src="{{ asset('logo.png') }}" alt="Hadir" class="logo-img">
            <span class="logo-text">{{ app()->getLocale() == 'ar' ? 'نظام حاضر' : 'Hadir' }}</span>
        </div>
        
        <div class="nav-links">
            <a href="#features">{{ app()->getLocale() == 'ar' ? 'المميزات' : 'Features' }}</a>
            <a href="#devices">{{ app()->getLocale() == 'ar' ? 'الأجهزة' : 'Devices' }}</a>
        </div>
        
        <div class="nav-buttons">
            <div class="lang-switch">
                <a href="{{ route('lang.switch', 'ar') }}" class="lang-btn {{ app()->getLocale() == 'ar' ? 'active' : '' }}">العربية</a>
                <a href="{{ route('lang.switch', 'en') }}" class="lang-btn {{ app()->getLocale() == 'en' ? 'active' : '' }}">EN</a>
            </div>
            <a href="{{ route('login') }}" class="btn btn-outline">{{ app()->getLocale() == 'ar' ? 'تسجيل الدخول' : 'Login' }}</a>
            <a href="{{ route('register') }}" class="btn btn-primary">{{ app()->getLocale() == 'ar' ? 'ابدأ مجاناً' : 'Start Free' }}</a>
        </div>
    </nav>
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1>
                    {{ app()->getLocale() == 'ar' ? 'إدارة الحضور' : 'Attendance Management' }}<br>
                    <span>{{ app()->getLocale() == 'ar' ? 'بذكاء ودقة' : 'Smart & Precise' }}</span>
                </h1>
                <p>
                    {{ app()->getLocale() == 'ar' 
                        ? 'نظام متكامل لإدارة الحضور والانصراف مع دعم كامل لأجهزة البصمة ZKTeco. راقب موظفيك، أنشئ التقارير، وأدر فروعك من مكان واحد.'
                        : 'Complete attendance management system with full ZKTeco biometric device integration. Monitor your employees, generate reports, and manage branches from one place.' 
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
                <h2 class="section-title">{{ app()->getLocale() == 'ar' ? 'كل ما تحتاجه لإدارة الحضور' : 'Everything You Need' }}</h2>
                <p class="section-desc">{{ app()->getLocale() == 'ar' ? 'أدوات متكاملة وقوية لإدارة حضور وانصراف موظفيك بكفاءة عالية' : 'Comprehensive and powerful tools to manage your employee attendance efficiently' }}</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon purple">🔗</div>
                    <h3 class="feature-title">{{ app()->getLocale() == 'ar' ? 'تكامل ZKTeco' : 'ZKTeco Integration' }}</h3>
                    <p class="feature-desc">{{ app()->getLocale() == 'ar' ? 'ربط تلقائي مع جميع أجهزة ZKTeco للبصمة والوجه مع مزامنة فورية للبيانات' : 'Automatic integration with all ZKTeco fingerprint and face recognition devices with real-time data sync' }}</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon orange">📊</div>
                    <h3 class="feature-title">{{ app()->getLocale() == 'ar' ? 'تقارير متقدمة' : 'Advanced Reports' }}</h3>
                    <p class="feature-desc">{{ app()->getLocale() == 'ar' ? 'تقارير تفصيلية ورسوم بيانية تفاعلية مع إمكانية التصدير بصيغ متعددة' : 'Detailed reports and interactive charts with multi-format export capabilities' }}</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon green">🏢</div>
                    <h3 class="feature-title">{{ app()->getLocale() == 'ar' ? 'إدارة الفروع' : 'Branch Management' }}</h3>
                    <p class="feature-desc">{{ app()->getLocale() == 'ar' ? 'إدارة متعددة الفروع مع لوحة تحكم مركزية وصلاحيات مخصصة لكل فرع' : 'Multi-branch management with centralized dashboard and custom permissions per branch' }}</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon blue">📱</div>
                    <h3 class="feature-title">{{ app()->getLocale() == 'ar' ? 'تطبيق الجوال' : 'Mobile App' }}</h3>
                    <p class="feature-desc">{{ app()->getLocale() == 'ar' ? 'تطبيق للجوال للحضور عن بعد مع GPS ومراقبة مباشرة للمديرين' : 'Mobile app for remote attendance with GPS and real-time monitoring for managers' }}</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon pink">⏰</div>
                    <h3 class="feature-title">{{ app()->getLocale() == 'ar' ? 'جداول الورديات' : 'Shift Schedules' }}</h3>
                    <p class="feature-desc">{{ app()->getLocale() == 'ar' ? 'إنشاء وإدارة جداول الورديات المرنة مع دعم المناوبات والعمل الإضافي' : 'Create and manage flexible shift schedules with overtime and rotation support' }}</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon cyan">🔔</div>
                    <h3 class="feature-title">{{ app()->getLocale() == 'ar' ? 'التنبيهات الذكية' : 'Smart Alerts' }}</h3>
                    <p class="feature-desc">{{ app()->getLocale() == 'ar' ? 'إشعارات فورية للتأخير والغياب والأحداث المهمة عبر البريد والتطبيق' : 'Instant notifications for lateness, absence, and important events via email and app' }}</p>
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
    
    <!-- Pricing Section - Hidden for now -->
    {{--
    <section class="pricing" id="pricing">
        <div class="section-container">
            <div class="section-header">
                <span class="section-badge">{{ app()->getLocale() == 'ar' ? '💰 الأسعار' : '💰 Pricing' }}</span>
                <h2 class="section-title">{{ app()->getLocale() == 'ar' ? 'خطط تناسب احتياجاتك' : 'Plans That Fit Your Needs' }}</h2>
                <p class="section-desc">{{ app()->getLocale() == 'ar' ? 'ابدأ مجاناً لمدة 14 يوم بدون بطاقة ائتمان' : 'Start free for 14 days without a credit card' }}</p>
            </div>
            
            <div class="pricing-grid">
                <div class="pricing-card">
                    <div class="pricing-name">{{ app()->getLocale() == 'ar' ? 'أساسي' : 'Starter' }}</div>
                    <div class="pricing-desc">{{ app()->getLocale() == 'ar' ? 'للشركات الصغيرة' : 'For small businesses' }}</div>
                    <div class="pricing-price">$29<span>/{{ app()->getLocale() == 'ar' ? 'شهر' : 'mo' }}</span></div>
                    <ul class="pricing-features">
                        <li><span class="check-icon">✓</span> {{ app()->getLocale() == 'ar' ? 'حتى 25 موظف' : 'Up to 25 employees' }}</li>
                        <li><span class="check-icon">✓</span> {{ app()->getLocale() == 'ar' ? 'جهاز واحد' : '1 device' }}</li>
                        <li><span class="check-icon">✓</span> {{ app()->getLocale() == 'ar' ? 'تقارير أساسية' : 'Basic reports' }}</li>
                        <li><span class="check-icon">✓</span> {{ app()->getLocale() == 'ar' ? 'دعم بالبريد' : 'Email support' }}</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-outline" style="width: 100%;">{{ app()->getLocale() == 'ar' ? 'ابدأ الآن' : 'Get Started' }}</a>
                </div>
                
                <div class="pricing-card featured">
                    <div class="pricing-name">{{ app()->getLocale() == 'ar' ? 'احترافي' : 'Professional' }}</div>
                    <div class="pricing-desc">{{ app()->getLocale() == 'ar' ? 'للشركات المتوسطة' : 'For growing companies' }}</div>
                    <div class="pricing-price">$79<span>/{{ app()->getLocale() == 'ar' ? 'شهر' : 'mo' }}</span></div>
                    <ul class="pricing-features">
                        <li><span class="check-icon">✓</span> {{ app()->getLocale() == 'ar' ? 'حتى 100 موظف' : 'Up to 100 employees' }}</li>
                        <li><span class="check-icon">✓</span> {{ app()->getLocale() == 'ar' ? '5 أجهزة' : '5 devices' }}</li>
                        <li><span class="check-icon">✓</span> {{ app()->getLocale() == 'ar' ? 'تقارير متقدمة' : 'Advanced reports' }}</li>
                        <li><span class="check-icon">✓</span> {{ app()->getLocale() == 'ar' ? 'تطبيق الجوال' : 'Mobile app' }}</li>
                        <li><span class="check-icon">✓</span> {{ app()->getLocale() == 'ar' ? 'دعم مباشر' : 'Live chat support' }}</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-primary" style="width: 100%;">{{ app()->getLocale() == 'ar' ? 'ابدأ الآن' : 'Get Started' }}</a>
                </div>
                
                <div class="pricing-card">
                    <div class="pricing-name">{{ app()->getLocale() == 'ar' ? 'المؤسسات' : 'Enterprise' }}</div>
                    <div class="pricing-desc">{{ app()->getLocale() == 'ar' ? 'للشركات الكبيرة' : 'For large organizations' }}</div>
                    <div class="pricing-price">{{ app()->getLocale() == 'ar' ? 'مخصص' : 'Custom' }}</div>
                    <ul class="pricing-features">
                        <li><span class="check-icon">✓</span> {{ app()->getLocale() == 'ar' ? 'موظفين غير محدود' : 'Unlimited employees' }}</li>
                        <li><span class="check-icon">✓</span> {{ app()->getLocale() == 'ar' ? 'أجهزة غير محدودة' : 'Unlimited devices' }}</li>
                        <li><span class="check-icon">✓</span> {{ app()->getLocale() == 'ar' ? 'API مخصص' : 'Custom API' }}</li>
                        <li><span class="check-icon">✓</span> {{ app()->getLocale() == 'ar' ? 'مدير حساب خاص' : 'Dedicated account manager' }}</li>
                        <li><span class="check-icon">✓</span> {{ app()->getLocale() == 'ar' ? 'SLA مضمون' : 'Guaranteed SLA' }}</li>
                    </ul>
                    <a href="mailto:sales@dawam.io" class="btn btn-outline" style="width: 100%;">{{ app()->getLocale() == 'ar' ? 'تواصل معنا' : 'Contact Sales' }}</a>
                </div>
            </div>
        </div>
    </section>
    --}}
    
    <!-- CTA Section -->
    <section class="cta">
        <div class="cta-box">
            <div class="cta-content">
                <h2 class="cta-title">{{ app()->getLocale() == 'ar' ? 'جاهز للبدء؟' : 'Ready to Get Started?' }}</h2>
                <p class="cta-desc">{{ app()->getLocale() == 'ar' ? 'انضم لأكثر من 500 شركة تدير حضورها بذكاء مع دوام' : 'Join over 500 companies managing their attendance smartly with Dawam' }}</p>
                <a href="{{ route('register') }}" class="btn btn-secondary" style="font-size: 18px; padding: 18px 40px;">
                    🚀 {{ app()->getLocale() == 'ar' ? 'ابدأ تجربتك المجانية' : 'Start Your Free Trial' }}
                </a>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-text">© {{ date('Y') }} {{ app()->getLocale() == 'ar' ? 'دوام. جميع الحقوق محفوظة.' : 'Dawam. All rights reserved.' }}</div>
            <div class="footer-links">
                <a href="#">{{ app()->getLocale() == 'ar' ? 'سياسة الخصوصية' : 'Privacy Policy' }}</a>
                <a href="#">{{ app()->getLocale() == 'ar' ? 'الشروط والأحكام' : 'Terms of Service' }}</a>
                <a href="mailto:support@dawam.io">{{ app()->getLocale() == 'ar' ? 'تواصل معنا' : 'Contact Us' }}</a>
            </div>
        </div>
    </footer>
</body>
</html>
