@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Profile</h1>
            <p class="page-subtitle">Update your account information</p>
        </div>
        <a href="{{ route('profile.show') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Profile
        </a>
    </div>

    <div class="grid grid-cols-3">
        <!-- Profile Picture -->
        <div class="card" style="grid-column: span 1;">
            <div class="card-header">Profile Picture</div>
            <div class="card-body" style="text-align: center;">
                <div class="avatar avatar-lg" style="width: 120px; height: 120px; font-size: 3rem; margin: 0 auto 1.5rem;">
                    @if(auth()->user()->avatar ?? false)
                        <img src="{{ auth()->user()->avatar }}" alt="Avatar"
                            style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    @else
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    @endif
                </div>
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <input type="file" name="avatar" id="avatar" class="form-control" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-secondary" style="width: 100%;">
                        <i class="fas fa-upload"></i> Upload Photo
                    </button>
                </form>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="card" style="grid-column: span 2;">
            <div class="card-header">Account Information</div>
            <div class="card-body">
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control"
                            value="{{ old('name', auth()->user()->name ?? '') }}" required>
                        @error('name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" class="form-control"
                            value="{{ auth()->user()->email ?? '' }}" disabled readonly
                            style="background-color: #f1f5f9; cursor: not-allowed;">
                        <small class="text-muted">Email tidak dapat diubah</small>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Password -->
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
            <i class="fas fa-lock" style="margin-right: 0.5rem;"></i>
            Change Password
        </div>
        <div class="card-body">
            <form action="{{ route('profile.password') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-3" style="gap: 1.5rem;">
                    <div class="form-group">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-control"
                            placeholder="Enter current password" required>
                        @error('current_password')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" id="password" name="password" class="form-control"
                            placeholder="Enter new password" required>
                        @error('password')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                            placeholder="Confirm new password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-key"></i> Update Password
                </button>
            </form>
        </div>
    </div>


@endsection