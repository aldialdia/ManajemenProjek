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

    <div class="grid grid-cols-3">
        <!-- Profile Card -->
        <div class="card" style="grid-column: span 1;">
            <div class="card-body" style="text-align: center; padding: 2rem;">
                <div class="avatar avatar-lg"
                    style="width: 100px; height: 100px; font-size: 2.5rem; margin: 0 auto 1.5rem;">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar"
                            style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    @else
                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                    @endif
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.25rem;">
                    {{ auth()->user()->name ?? 'User Name' }}
                </h3>
                <p class="text-muted" style="font-size: 0.875rem;">
                    {{ auth()->user()->email ?? 'user@example.com' }}
                </p>
            </div>
        </div>

        <!-- Profile Details -->
        <div class="card" style="grid-column: span 2;">
            <div class="card-header">
                <i class="fas fa-user-circle" style="margin-right: 0.5rem;"></i>
                Account Information
            </div>
            <div class="card-body">
                <div class="grid grid-cols-2" style="gap: 1.5rem;">
                    <div>
                        <label class="text-muted text-sm" style="display: block; margin-bottom: 0.25rem;">Full Name</label>
                        <p style="font-weight: 500;">{{ auth()->user()->name ?? 'Not set' }}</p>
                    </div>
                    <div>
                        <label class="text-muted text-sm" style="display: block; margin-bottom: 0.25rem;">Email
                            Address</label>
                        <p style="font-weight: 500;">{{ auth()->user()->email ?? 'Not set' }}</p>
                    </div>
                    <div>
                        <label class="text-muted text-sm" style="display: block; margin-bottom: 0.25rem;">Member
                            Since</label>
                        <p style="font-weight: 500;">{{ auth()->user()->created_at?->format('d M Y') ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection