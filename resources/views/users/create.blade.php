@extends('layouts.app')

@section('title', 'Add Team Member')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Add Team Member</h1>
            <p class="page-subtitle">Invite a new user to the team</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Team
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-2" style="gap: 1.5rem;">
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Enter full name"
                            value="{{ old('name') }}" required>
                        @error('name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="user@example.com"
                            value="{{ old('email') }}" required>
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" id="password" name="password" class="form-control"
                            placeholder="Min. 8 characters" required>
                        @error('password')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Confirm Password <span
                                class="text-danger">*</span></label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                            placeholder="Repeat password" required>
                    </div>

                    <div class="form-group">
                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="member" {{ old('role', 'member') == 'member' ? 'selected' : '' }}>Member</option>
                            <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        @error('role')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div style="margin-top: 2rem; padding: 1rem; background: #f1f5f9; border-radius: 12px;">
                    <h4 style="font-weight: 600; margin-bottom: 0.5rem;">
                        <i class="fas fa-info-circle" style="color: var(--info); margin-right: 0.5rem;"></i>
                        Role Permissions
                    </h4>
                    <ul style="font-size: 0.875rem; color: var(--secondary); margin-left: 1.5rem;">
                        <li><strong>Member:</strong> Can view and work on assigned tasks and projects</li>
                        <li><strong>Manager:</strong> Can create projects, manage tasks, and assign team members</li>
                        <li><strong>Admin:</strong> Full access including user management and system settings</li>
                    </ul>
                </div>

                <div
                    style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e2e8f0; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Add Team Member
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection