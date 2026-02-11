@extends('layouts.app')

@section('title', 'Forgot Password')

@section('content')
<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: radial-gradient(circle at top right, #1e293b, #0f172a);">
    <div style="width: 100%; max-width: 400px; padding: 20px;">
        
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="font-size: 24px; font-weight: bold; color: #fff; margin-bottom: 10px;">Forgot Password?</h1>
            <p style="color: #94a3b8;">No problem. Just let us know your email address and we will email you a password reset link.</p>
        </div>

        <div class="card" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1);">
            <div class="card-body">
                @if (session('status'))
                    <div style="margin-bottom: 20px; color: var(--success-color); font-size: 0.9em;">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                        @error('email')
                            <span style="color: var(--danger-color); font-size: 0.85em;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div style="margin-top: 20px;">
                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                            Email Password Reset Link
                        </button>
                    </div>

                    <div style="margin-top: 20px; text-align: center;">
                        <a href="{{ route('login') }}" style="color: var(--text-muted); font-size: 0.9em; text-decoration: none;">
                            Back to Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
