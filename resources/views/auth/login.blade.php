@extends('layouts.guest')

@section('title', 'Login')
@section('subtitle', 'Sign in to your account')

@section('content')
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label for="email" class="form-label">Email Address</label>
            <div class="input-icon-wrapper">
                <i class="fas fa-envelope"></i>
                <input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com"
                    value="{{ old('email') }}" required autofocus>
            </div>
            @error('email')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <div class="input-icon-wrapper">
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            @error('password')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-check">
            <input type="checkbox" id="remember" name="remember">
            <label for="remember">Remember me</label>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-sign-in-alt"></i>
            Sign In
        </button>

        <div style="text-align: center; margin-top: 1rem;">
            <a href="#" class="form-link">Forgot your password?</a>
        </div>

        <div class="form-footer">
            <p>Don't have an account? <a href="{{ route('register') }}">Sign Up</a></p>
        </div>
    </form>

    <div style="margin-top: 1.5rem; padding: 1rem; background: #f1f5f9; border-radius: 12px;">
        <p style="font-size: 0.75rem; color: #64748b; text-align: center; margin-bottom: 0.5rem;">Demo Accounts:</p>
        <div style="font-size: 0.75rem; color: #475569;">
            <p><strong>Admin:</strong> admin@example.com / password</p>
            <p><strong>Manager:</strong> manager@example.com / password</p>
            <p><strong>Member:</strong> member@example.com / password</p>
        </div>
    </div>
@endsection