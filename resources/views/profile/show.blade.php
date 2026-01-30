@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">My Profile</h1>
            <p class="page-subtitle">View and manage your account information</p>
        </div>
        <a href="{{ route('profile.edit') }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit Profile
        </a>
    </div>

    <div class="grid grid-cols-1">
        <!-- Profile Card with Account Information -->
        <div class="card">
            <div class="card-body" style="padding: 2.5rem;">
                <!-- Avatar Section - Centered at Top -->
                <div style="text-align: center; margin-bottom: 2rem;">
                    <div class="avatar avatar-lg"
                        style="width: 120px; height: 120px; font-size: 3rem; margin: 0 auto 1rem;">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar"
                                style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        @else
                            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                        @endif
                    </div>
                    <h3 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 0.25rem; color: #1e293b;">
                        {{ auth()->user()->name ?? 'User Name' }}
                    </h3>
                    <p class="text-muted" style="font-size: 0.95rem;">
                        {{ auth()->user()->email ?? 'user@example.com' }}
                    </p>
                </div>

                <!-- Account Information Section -->
                <div style="border-top: 1px solid #e2e8f0; padding-top: 2rem;">
                    <h4
                        style="font-size: 1rem; font-weight: 600; margin-bottom: 1.5rem; color: #1e293b; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-user-circle"></i>
                        Account Information
                    </h4>
                    <div class="grid grid-cols-2" style="gap: 1.5rem;">
                        <div>
                            <label class="text-muted text-sm"
                                style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Full Name</label>
                            <p style="font-weight: 500; color: #1e293b;">{{ auth()->user()->name ?? 'Not set' }}</p>
                        </div>
                        <div>
                            <label class="text-muted text-sm"
                                style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Email Address</label>
                            <p style="font-weight: 500; color: #1e293b;">{{ auth()->user()->email ?? 'Not set' }}</p>
                        </div>
                        <div>
                            <label class="text-muted text-sm"
                                style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Member Since</label>
                            <p style="font-weight: 500; color: #1e293b;">
                                {{ auth()->user()->created_at?->format('d M Y') ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .avatar-edit-btn {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
            transition: all 0.3s ease;
            border: 3px solid white;
        }

        .avatar-edit-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.5);
        }

        .avatar-edit-btn i {
            font-size: 0.875rem;
        }

        .avatar {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
    </style>

@endsection