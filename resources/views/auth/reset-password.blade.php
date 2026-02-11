@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: radial-gradient(circle at top right, #1e293b, #0f172a);">
    <div style="width: 100%; max-width: 400px; padding: 20px;">
        
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="font-size: 24px; font-weight: bold; color: #fff; margin-bottom: 10px;">Reset Password</h1>
            <p style="color: #94a3b8;">Enter your new password below.</p>
        </div>

        <div class="card" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1);">
            <div class="card-body">
                <form method="POST" action="{{ route('password.store') }}">
                    @csrf

                    <!-- Password Reset Token -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $request->email) }}" required autofocus>
                        @error('email')
                            <span style="color: var(--danger-color); font-size: 0.85em;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                        @error('password')
                            <span style="color: var(--danger-color); font-size: 0.85em;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>

                    <div style="margin-top: 20px;">
                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                            Reset Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
