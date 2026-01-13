@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit User</h1>
            <p class="page-subtitle">Update user information</p>
        </div>
        <a href="{{ route('users.show', $user ?? 1) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to User
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('users.update', $user ?? 1) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2" style="gap: 1.5rem;">
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Enter full name"
                            value="{{ old('name', $user->name ?? '') }}" required>
                        @error('name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="user@example.com"
                            value="{{ old('email', $user->email ?? '') }}" required>
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="member" {{ old('role', $user->role ?? '') == 'member' ? 'selected' : '' }}>Member
                            </option>
                            <option value="manager" {{ old('role', $user->role ?? '') == 'manager' ? 'selected' : '' }}>
                                Manager</option>
                            <option value="admin" {{ old('role', $user->role ?? '') == 'admin' ? 'selected' : '' }}>Admin
                            </option>
                        </select>
                        @error('role')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div
                    style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e2e8f0; display: flex; gap: 1rem; justify-content: space-between;">
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="{{ route('users.show', $user ?? 1) }}" class="btn btn-secondary">Cancel</a>
                    </div>
                    @if(auth()->id() !== ($user->id ?? 0))
                        <form action="{{ route('users.destroy', $user ?? 1) }}" method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this user?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Delete User
                            </button>
                        </form>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Reset Password -->
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
            <i class="fas fa-key" style="margin-right: 0.5rem;"></i>
            Reset Password
        </div>
        <div class="card-body">
            <form action="#" method="POST">
                @csrf
                <div class="grid grid-cols-2" style="gap: 1.5rem;">
                    <div class="form-group">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" id="new_password" name="password" class="form-control"
                            placeholder="Enter new password">
                    </div>
                    <div class="form-group">
                        <label for="new_password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" id="new_password_confirmation" name="password_confirmation"
                            class="form-control" placeholder="Confirm new password">
                    </div>
                </div>
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-key"></i> Reset Password
                </button>
            </form>
        </div>
    </div>
@endsection