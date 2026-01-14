@extends('layouts.app')

@section('title', 'Undangan Project')

@section('content')
    <div class="invitation-page">
        <div class="invitation-card">
            <div class="invitation-icon">
                <i class="fas fa-envelope-open-text"></i>
            </div>

            <h1 class="invitation-title">Undangan Bergabung</h1>

            <div class="invitation-details">
                <div class="project-info">
                    <span class="label">Project</span>
                    <span class="value">{{ $invitation->project->name }}</span>
                </div>
                <div class="inviter-info">
                    <span class="label">Diundang oleh</span>
                    <div class="inviter">
                        <div class="inviter-avatar">
                            {{ $invitation->inviter->initials }}
                        </div>
                        <span class="inviter-name">{{ $invitation->inviter->name }}</span>
                    </div>
                </div>
                <div class="role-info">
                    <span class="label">Sebagai</span>
                    <span class="role-badge role-{{ $invitation->role }}">{{ ucfirst($invitation->role) }}</span>
                </div>
            </div>

            <p class="invitation-message">
                Anda diundang untuk bergabung ke project <strong>{{ $invitation->project->name }}</strong>
                sebagai <strong>{{ ucfirst($invitation->role) }}</strong>.
                Apakah Anda ingin menerima undangan ini?
            </p>

            <div class="invitation-actions">
                <form action="{{ route('invitations.accept', $invitation->token) }}" method="POST" style="flex: 1;">
                    @csrf
                    <button type="submit" class="btn btn-success btn-lg" style="width: 100%;">
                        <i class="fas fa-check"></i>
                        Terima Undangan
                    </button>
                </form>
                <form action="{{ route('invitations.decline', $invitation->token) }}" method="POST" style="flex: 1;">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-lg" style="width: 100%;">
                        <i class="fas fa-times"></i>
                        Tolak
                    </button>
                </form>
            </div>

            <div class="invitation-footer">
                <span class="expires">
                    <i class="fas fa-clock"></i>
                    Undangan berlaku hingga {{ $invitation->expires_at->format('d M Y, H:i') }}
                </span>
            </div>
        </div>
    </div>

    <style>
        .invitation-page {
            min-height: calc(100vh - 200px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .invitation-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            max-width: 500px;
            width: 100%;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        }

        .invitation-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .invitation-icon i {
            font-size: 2rem;
            color: white;
        }

        .invitation-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 2rem 0;
        }

        .invitation-details {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .invitation-details>div {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .invitation-details>div:last-child {
            border-bottom: none;
        }

        .invitation-details .label {
            color: #64748b;
            font-size: 0.875rem;
        }

        .invitation-details .value {
            font-weight: 600;
            color: #1e293b;
        }

        .inviter {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .inviter-avatar {
            width: 28px;
            height: 28px;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .inviter-name {
            font-weight: 600;
            color: #1e293b;
        }

        .role-badge {
            padding: 0.375rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .role-manager {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
        }

        .role-admin {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
        }

        .role-member {
            background: #e2e8f0;
            color: #475569;
        }

        .invitation-message {
            color: #475569;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .invitation-actions {
            display: flex;
            gap: 1rem;
        }

        .btn-lg {
            padding: 1rem 1.5rem;
            font-size: 1rem;
        }

        .btn-success {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            border: none;
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        }

        .invitation-footer {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }

        .expires {
            color: #94a3b8;
            font-size: 0.8rem;
        }

        .expires i {
            margin-right: 0.5rem;
        }

        @media (max-width: 480px) {
            .invitation-card {
                padding: 2rem;
            }

            .invitation-actions {
                flex-direction: column;
            }
        }
    </style>
@endsection