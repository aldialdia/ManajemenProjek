@extends('layouts.guest')

@section('title', 'Register')
@section('subtitle', 'Create your account')

@section('content')
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="form-group">
            <label for="name" class="form-label">Full Name</label>
            <div class="input-icon-wrapper">
                <i class="fas fa-user"></i>
                <input type="text" id="name" name="name" class="form-control" placeholder="John Doe"
                    value="{{ old('name') }}" required autofocus>
            </div>
            @error('name')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email Address</label>
            <div class="input-icon-wrapper">
                <i class="fas fa-envelope"></i>
                <input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com"
                    value="{{ old('email') }}" required>
            </div>
            @error('email')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <div class="input-icon-wrapper">
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" class="form-control" placeholder="Min. 8 characters"
                    required>
            </div>
            @error('password')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <div class="input-icon-wrapper">
                <i class="fas fa-lock"></i>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                    placeholder="Repeat your password" required>
            </div>
        </div>

        <div class="form-check">
            <input type="checkbox" id="terms" name="terms" required>
            <label for="terms">I agree to the <a href="#" class="form-link">Terms & Conditions</a></label>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-user-plus"></i>
            Create Account
        </button>

        <div class="form-footer">
            <p>Already have an account? <a href="{{ route('login') }}">Sign In</a></p>
        </div>
    </form>
@endsection