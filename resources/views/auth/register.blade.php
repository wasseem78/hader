<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - Attendance System</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --primary-light: #6366f1;
            --bg-dark: #0f172a;
            --glass-bg: rgba(30, 41, 59, 0.7);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --success: #10b981;
            --success-bg: rgba(16, 185, 129, 0.1);
            --success-border: rgba(16, 185, 129, 0.3);
            --error: #f87171;
            --error-bg: rgba(239, 68, 68, 0.1);
            --error-border: rgba(239, 68, 68, 0.2);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { 
            font-family: 'Inter', sans-serif; 
            min-height: 100vh; 
            background-color: var(--bg-dark);
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--text-main);
        }

        .card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 480px;
            padding: 40px;
        }

        h1 {
            color: #fff;
            font-size: 24px;
            margin-bottom: 8px;
            text-align: center;
            font-weight: 700;
        }

        .subtitle {
            color: var(--text-muted);
            text-align: center;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            font-size: 14px;
            color: #fff;
            transition: all 0.2s;
            outline: none;
            font-family: 'Inter', sans-serif;
        }

        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }

        input:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn {
            display: block;
            width: 100%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 14px;
            border-radius: 12px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 16px;
            font-family: 'Inter', sans-serif;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
            transition: all 0.3s;
        }

        .btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .btn-send-code {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            color: white;
            padding: 11px 20px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            box-shadow: 0 2px 8px rgba(79, 70, 229, 0.25);
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-send-code:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
        }

        .btn-send-code:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .btn-send-code .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            display: none;
        }

        .btn-send-code.loading .spinner { display: inline-block; }
        .btn-send-code.loading .btn-text { display: none; }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* OTP Code Input Section */
        .code-section {
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transition: max-height 0.5s ease, opacity 0.4s ease, margin 0.4s ease;
            margin-top: 0;
        }

        .code-section.visible {
            max-height: 200px;
            opacity: 1;
            margin-top: 16px;
        }

        .code-inputs {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-bottom: 12px;
        }

        .code-inputs input {
            width: 48px;
            height: 56px;
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            padding: 0;
            border-radius: 12px;
            background: rgba(0, 0, 0, 0.3);
            border: 2px solid var(--glass-border);
            caret-color: var(--primary);
            transition: all 0.2s;
            letter-spacing: 0;
        }

        .code-inputs input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.3);
            background: rgba(0, 0, 0, 0.4);
        }

        .code-inputs input.filled {
            border-color: var(--primary-light);
            background: rgba(79, 70, 229, 0.1);
        }

        .code-inputs input.error {
            border-color: var(--error);
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
            animation: shake 0.4s ease;
        }

        .code-inputs input.success {
            border-color: var(--success);
            background: rgba(16, 185, 129, 0.1);
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-4px); }
            75% { transform: translateX(4px); }
        }

        .code-timer {
            text-align: center;
            font-size: 13px;
            color: var(--text-muted);
        }

        .code-timer a {
            color: var(--primary-light);
            cursor: pointer;
            text-decoration: none;
            font-weight: 500;
        }

        .code-timer a:hover { text-decoration: underline; }

        /* Alert messages */
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 16px;
            display: none;
            align-items: center;
            gap: 10px;
            animation: fadeIn 0.3s ease;
        }

        .alert.visible { display: flex; }

        .alert-error {
            background: var(--error-bg);
            border: 1px solid var(--error-border);
            color: var(--error);
        }

        .alert-success {
            background: var(--success-bg);
            border: 1px solid var(--success-border);
            color: var(--success);
        }

        .alert svg { flex-shrink: 0; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Email verified badge */
        .email-verified-badge {
            display: none;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: var(--success);
            font-weight: 500;
            margin-top: 8px;
        }

        .email-verified-badge.visible { display: flex; }

        /* Registration form fields (hidden initially) */
        .registration-fields {
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transition: max-height 0.6s ease, opacity 0.5s ease;
        }

        .registration-fields.visible {
            max-height: 600px;
            opacity: 1;
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            margin: 24px 0;
            gap: 12px;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--glass-border);
        }
        .divider span {
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 500;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: var(--text-muted);
        }

        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover { text-decoration: underline; }

        .brand-logo {
            width: 80px;
            height: 80px;
            border-radius: 16px;
            object-fit: contain;
            margin: 0 auto 20px;
            display: block;
        }

        /* Step indicator */
        .steps {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 24px;
        }

        .step {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .step .step-num {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--glass-border);
            font-size: 11px;
            font-weight: 700;
            transition: all 0.3s;
        }

        .step.active .step-num {
            border-color: var(--primary);
            background: var(--primary);
            color: white;
        }

        .step.completed .step-num {
            border-color: var(--success);
            background: var(--success);
            color: white;
        }

        .step-connector {
            width: 32px;
            height: 2px;
            background: var(--glass-border);
            transition: background 0.3s;
        }

        .step-connector.active { background: var(--primary); }
        .step-connector.completed { background: var(--success); }
    </style>
</head>
<body>
    <div class="card">
        <img src="{{ asset('logo.png') }}" alt="Logo" class="brand-logo">
        <h1>{{ __('messages.start_free_trial') }}</h1>
        <p class="subtitle">{{ __('messages.create_account_desc') }}</p>

        <!-- Step Indicator -->
        <div class="steps">
            <div class="step active" id="step1">
                <span class="step-num">1</span>
                <span>{{ __('messages.verify_email_step') }}</span>
            </div>
            <div class="step-connector" id="connector1"></div>
            <div class="step" id="step2">
                <span class="step-num">2</span>
                <span>{{ __('messages.account_details_step') }}</span>
            </div>
        </div>

        <!-- Alert Messages -->
        <div class="alert alert-error" id="alertError">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M8 1a7 7 0 100 14A7 7 0 008 1zM7 5a1 1 0 012 0v3a1 1 0 01-2 0V5zm1 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
            <span id="alertErrorText"></span>
        </div>
        <div class="alert alert-success" id="alertSuccess">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M8 1a7 7 0 100 14A7 7 0 008 1zm3.7 5.3a1 1 0 00-1.4-1.4L7 8.2 5.7 6.9a1 1 0 00-1.4 1.4l2 2a1 1 0 001.4 0l4-4z"/></svg>
            <span id="alertSuccessText"></span>
        </div>

        @if ($errors->any())
            <div class="alert alert-error visible">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M8 1a7 7 0 100 14A7 7 0 008 1zM7 5a1 1 0 012 0v3a1 1 0 01-2 0V5zm1 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" id="registerForm">
            @csrf

            <!-- STEP 1: Email Verification -->
            <div id="emailStep">
                <div class="form-group" style="margin-bottom: 0;">
                    <label>{{ __('messages.email_label') }}</label>
                    <input type="email" name="email" id="emailInput" value="{{ old('email') }}" required placeholder="e.g. john@acme.com">
                </div>

                <button type="button" class="btn-send-code" id="sendCodeBtn" onclick="sendVerificationCode()">
                    <span class="spinner"></span>
                    <span class="btn-text">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align: -2px;"><path d="M1.5 4l6.5 4 6.5-4M2 3h12a1 1 0 011 1v8a1 1 0 01-1 1H2a1 1 0 01-1-1V4a1 1 0 011-1z"/></svg>
                        {{ __('messages.send_verification_code') }}
                    </span>
                </button>

                <!-- Code Input Section -->
                <div class="code-section" id="codeSection">
                    <label style="text-align: center; margin-bottom: 12px;">{{ __('messages.enter_verification_code') }}</label>
                    <div class="code-inputs" id="codeInputs">
                        <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="code-digit" data-index="0" autocomplete="off">
                        <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="code-digit" data-index="1" autocomplete="off">
                        <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="code-digit" data-index="2" autocomplete="off">
                        <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="code-digit" data-index="3" autocomplete="off">
                        <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="code-digit" data-index="4" autocomplete="off">
                        <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="code-digit" data-index="5" autocomplete="off">
                    </div>
                    <div class="code-timer" id="codeTimer">
                        {{ __('messages.code_expires_in') }} <strong id="timerDisplay">10:00</strong>
                        <span id="resendLink" style="display:none;">
                            &mdash; <a onclick="sendVerificationCode()">{{ __('messages.resend_code') }}</a>
                        </span>
                    </div>
                </div>

                <!-- Verified Badge -->
                <div class="email-verified-badge" id="verifiedBadge">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><circle cx="9" cy="9" r="9" fill="#10b981"/><path d="M5.5 9.5l2 2 5-5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    {{ __('messages.email_verified_badge') }}
                </div>
            </div>

            <!-- STEP 2: Registration Details (hidden until verified) -->
            <div class="registration-fields" id="registrationFields">
                <div class="divider">
                    <span>{{ __('messages.account_details_step') }}</span>
                </div>

                <div class="form-group">
                    <label>{{ __('messages.company_name_label') }}</label>
                    <input type="text" name="company_name" id="companyNameInput" value="{{ old('company_name') }}" required placeholder="e.g. Acme Corp" oninput="generateSubdomain()">
                </div>

                <div class="form-group">
                    <label>{{ __('messages.subdomain_label') }}</label>
                    <div style="display: flex; align-items: center; gap: 0;">
                        <input type="text" name="subdomain" id="subdomainInput" value="{{ old('subdomain') }}" required placeholder="acme" style="border-radius: 12px 0 0 12px; text-align: right; direction: ltr;" oninput="this.value = this.value.toLowerCase().replace(/[^a-z0-9-]/g, '')">
                        <span style="background: rgba(79, 70, 229, 0.15); border: 1px solid var(--glass-border); border-left: none; border-radius: 0 12px 12px 0; padding: 12px 14px; font-size: 13px; color: var(--primary-light); white-space: nowrap; font-weight: 500;">.uhdor.com</span>
                    </div>
                </div>

                <div class="form-group">
                    <label>{{ __('messages.full_name_label') }}</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. John Doe">
                </div>

                <div class="form-group">
                    <label>{{ __('messages.password') }}</label>
                    <input type="password" name="password" required minlength="8" placeholder="{{ __('messages.password_placeholder') }}">
                </div>

                <div class="form-group">
                    <label>{{ __('messages.confirm_password') }}</label>
                    <input type="password" name="password_confirmation" required placeholder="{{ __('messages.confirm_password_placeholder') }}">
                </div>

                <input type="hidden" name="email_verification_token" id="verificationToken" value="">

                <button type="submit" class="btn" id="submitBtn">{{ __('messages.create_account_btn') }}</button>
            </div>
        </form>

        <div class="login-link">
            {{ __('messages.already_have_account') }} <a href="{{ route('login') }}">{{ __('messages.login') }}</a>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        let countdownInterval = null;
        let emailVerified = false;

        // ---- Send Verification Code ----
        async function sendVerificationCode() {
            const email = document.getElementById('emailInput').value.trim();
            if (!email || !email.includes('@')) {
                showAlert('error', '{{ __("messages.enter_valid_email") }}');
                return;
            }

            const btn = document.getElementById('sendCodeBtn');
            btn.classList.add('loading');
            btn.disabled = true;
            hideAlerts();

            try {
                const response = await fetch('{{ route("register.send-code") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ email: email }),
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('success', data.message);
                    showCodeSection();
                    startCountdown(600); // 10 minutes
                    // Disable email field while verifying
                    document.getElementById('emailInput').disabled = true;
                } else {
                    showAlert('error', data.message);
                }
            } catch (err) {
                showAlert('error', '{{ __("messages.network_error") }}');
            } finally {
                btn.classList.remove('loading');
                btn.disabled = false;
            }
        }

        // ---- Code Input Handling ----
        document.querySelectorAll('.code-digit').forEach((input, index, inputs) => {
            input.addEventListener('input', (e) => {
                const val = e.target.value.replace(/[^0-9]/g, '');
                e.target.value = val;

                if (val) {
                    e.target.classList.add('filled');
                    // Move to next
                    if (index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                } else {
                    e.target.classList.remove('filled');
                }

                // Auto-submit when all 6 digits entered
                const code = getCodeValue();
                if (code.length === 6) {
                    verifyCode(code);
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    inputs[index - 1].focus();
                    inputs[index - 1].value = '';
                    inputs[index - 1].classList.remove('filled');
                }
            });

            // Handle paste
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const paste = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
                if (paste.length === 6) {
                    inputs.forEach((inp, i) => {
                        inp.value = paste[i] || '';
                        if (inp.value) inp.classList.add('filled');
                    });
                    inputs[5].focus();
                    verifyCode(paste);
                }
            });
        });

        function getCodeValue() {
            return Array.from(document.querySelectorAll('.code-digit')).map(i => i.value).join('');
        }

        // ---- Verify Code ----
        async function verifyCode(code) {
            const email = document.getElementById('emailInput').value.trim();
            hideAlerts();

            try {
                const response = await fetch('{{ route("register.verify-code") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ email: email, code: code }),
                });

                const data = await response.json();

                if (data.success) {
                    emailVerified = true;
                    document.getElementById('verificationToken').value = data.token;

                    // Show success state on code inputs
                    document.querySelectorAll('.code-digit').forEach(i => {
                        i.classList.add('success');
                        i.disabled = true;
                    });

                    // Update steps
                    document.getElementById('step1').classList.remove('active');
                    document.getElementById('step1').classList.add('completed');
                    document.getElementById('connector1').classList.add('completed');
                    document.getElementById('step2').classList.add('active');

                    // Hide code section, show verified badge
                    document.getElementById('codeSection').classList.remove('visible');
                    document.getElementById('sendCodeBtn').style.display = 'none';
                    document.getElementById('verifiedBadge').classList.add('visible');

                    if (countdownInterval) clearInterval(countdownInterval);

                    showAlert('success', data.message);

                    // Show registration fields with a brief delay
                    setTimeout(() => {
                        document.getElementById('registrationFields').classList.add('visible');
                    }, 400);
                } else {
                    document.querySelectorAll('.code-digit').forEach(i => {
                        i.classList.add('error');
                        setTimeout(() => i.classList.remove('error'), 600);
                    });
                    showAlert('error', data.message);
                }
            } catch (err) {
                showAlert('error', '{{ __("messages.network_error") }}');
            }
        }

        // ---- UI Helpers ----
        function showCodeSection() {
            document.getElementById('codeSection').classList.add('visible');
            // Focus first code input
            setTimeout(() => {
                document.querySelector('.code-digit[data-index="0"]').focus();
            }, 400);
        }

        function showAlert(type, message) {
            hideAlerts();
            const el = type === 'error' ? document.getElementById('alertError') : document.getElementById('alertSuccess');
            const textEl = type === 'error' ? document.getElementById('alertErrorText') : document.getElementById('alertSuccessText');
            textEl.textContent = message;
            el.classList.add('visible');

            // Auto-hide success after 5s
            if (type === 'success') {
                setTimeout(() => el.classList.remove('visible'), 5000);
            }
        }

        function hideAlerts() {
            document.getElementById('alertError').classList.remove('visible');
            document.getElementById('alertSuccess').classList.remove('visible');
        }

        function startCountdown(seconds) {
            if (countdownInterval) clearInterval(countdownInterval);
            let remaining = seconds;
            const display = document.getElementById('timerDisplay');
            const resend = document.getElementById('resendLink');
            resend.style.display = 'none';

            updateTimerDisplay(remaining);

            countdownInterval = setInterval(() => {
                remaining--;
                updateTimerDisplay(remaining);
                if (remaining <= 0) {
                    clearInterval(countdownInterval);
                    display.textContent = '0:00';
                    resend.style.display = 'inline';
                }
            }, 1000);
        }

        function updateTimerDisplay(seconds) {
            const m = Math.floor(seconds / 60);
            const s = seconds % 60;
            document.getElementById('timerDisplay').textContent = m + ':' + (s < 10 ? '0' : '') + s;
        }

        // Prevent form submission if email not verified
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            if (!emailVerified) {
                e.preventDefault();
                showAlert('error', '{{ __("messages.please_verify_email_first") }}');
            }
        });

        // Auto-generate subdomain from company name
        function generateSubdomain() {
            const name = document.getElementById('companyNameInput').value;
            const subdomain = name.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '')
                .substring(0, 50);
            document.getElementById('subdomainInput').value = subdomain;
        }
    </script>
</body>
</html>
