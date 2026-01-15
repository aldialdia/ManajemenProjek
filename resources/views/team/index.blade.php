@extends('layouts.app')

@section('title', 'Manajemen Tim - ' . $project->name)

@section('content')
    <div class="page-header">
        <div>
            <div class="breadcrumb" style="margin-bottom: 0.5rem;">
                <a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a>
                <i class="fas fa-chevron-right" style="margin: 0 0.5rem; font-size: 0.75rem; color: #94a3b8;"></i>
                <span>Manajemen Tim</span>
            </div>
            <h1 class="page-title">Manajemen Tim</h1>
            <p class="page-subtitle">Kelola anggota tim project {{ $project->name }}</p>
        </div>
    </div>

    <div class="team-container">
        @if($canInvite)
            <!-- Invite Member Card -->
            <div class="card invite-card">
                <div class="card-header">
                    <i class="fas fa-user-plus"></i>
                    Undang Anggota Baru
                </div>
                <div class="card-body">
                    <form action="{{ route('projects.invitations.store', $project) }}" method="POST" class="invite-form">
                        @csrf
                        <div class="form-row">
                            <div class="form-group" style="flex: 2;">
                                <label for="email" class="form-label">Email User</label>
                                <input type="email" name="email" id="email" class="form-control"
                                    placeholder="Masukkan email user yang terdaftar" required>
                                @error('email')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label for="role" class="form-label">Role</label>
                                <select name="role" id="role" class="form-control" required>
                                    <option value="member">Member</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div class="form-group" style="align-self: flex-end;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i>
                                    Kirim Undangan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Pending Invitations -->
        @if($pendingInvitations->count() > 0)
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-clock"></i>
                    Undangan Tertunda ({{ $pendingInvitations->count() }})
                </div>
                <div class="card-body">
                    @foreach($pendingInvitations as $invitation)
                        <div class="invitation-item">
                            <div class="invitation-info">
                                <span class="invitation-email">{{ $invitation->email }}</span>
                                <span class="invitation-role">{{ ucfirst($invitation->role) }}</span>
                            </div>
                            <div class="invitation-meta">
                                <span>Diundang oleh {{ $invitation->inviter->name }}</span>
                                <span>•</span>
                                <span>Kadaluarsa {{ $invitation->expires_at->diffForHumans() }}</span>
                            </div>
                            <form action="{{ route('projects.team.cancelInvitation', $invitation) }}" method="POST"
                                onsubmit="return confirm('Batalkan undangan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-times"></i> Batalkan
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Team Members -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-users"></i>
                Anggota Tim ({{ $members->count() }})
            </div>
            <div class="card-body">
                <div class="members-list">
                    @foreach($members as $member)
                        <div class="member-item">
                            <div class="member-avatar"
                                style="background: linear-gradient(135deg, {{ ['#6366f1', '#f97316', '#22c55e', '#ec4899'][$loop->index % 4] }} 0%, {{ ['#4f46e5', '#ea580c', '#16a34a', '#db2777'][$loop->index % 4] }} 100%);">
                                {{ $member->initials }}
                            </div>
                            <div class="member-info">
                                <div class="member-name">
                                    {{ $member->name }}
                                    @if($member->id === auth()->id())
                                        <span class="badge badge-info" style="font-size: 0.65rem;">Anda</span>
                                    @endif
                                </div>
                                <div class="member-email">{{ $member->email }}</div>
                            </div>
                            <div class="member-role">
                                @php
                                    $roleClass = match ($member->pivot->role) {
                                        'manager' => 'badge-primary',
                                        'admin' => 'badge-warning',
                                        default => 'badge-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $roleClass }}">{{ ucfirst($member->pivot->role) }}</span>
                            </div>
                            @if($isManager && $member->id !== auth()->id() && $member->pivot->role !== 'manager')
                                <div class="member-actions">
                                    <form action="{{ route('projects.team.updateRole', [$project, $member]) }}" method="POST"
                                        class="role-form">
                                        @csrf
                                        @method('PATCH')
                                        <select name="role" class="form-control form-control-sm" onchange="this.form.submit()">
                                            <option value="admin" {{ $member->pivot->role === 'admin' ? 'selected' : '' }}>Admin
                                            </option>
                                            <option value="member" {{ $member->pivot->role === 'member' ? 'selected' : '' }}>Member
                                            </option>
                                        </select>
                                    </form>
                                    <form action="{{ route('projects.team.remove', [$project, $member]) }}" method="POST"
                                        onsubmit="return confirm('Hapus {{ $member->name }} dari project?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon-delete" title="Hapus dari project">
                                            <i class="fas fa-user-minus"></i>
                                        </button>
                                    </form>
                                </div>
                            @elseif($userRole === 'admin' && $member->pivot->role === 'member' && $member->id !== auth()->id())
                                <div class="member-actions">
                                    <form action="{{ route('projects.team.remove', [$project, $member]) }}" method="POST"
                                        onsubmit="return confirm('Hapus {{ $member->name }} dari project?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon-delete" title="Hapus dari project">
                                            <i class="fas fa-user-minus"></i>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <style>
        .team-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .invite-card {
            border-left: 4px solid #6366f1;
        }

        .invite-form .form-row {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }

        .invitation-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #fef3c7;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        .invitation-info {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .invitation-email {
            font-weight: 600;
            color: #1e293b;
        }

        .invitation-role {
            padding: 0.25rem 0.75rem;
            background: white;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #b45309;
        }

        .invitation-meta {
            display: flex;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: #92400e;
        }

        .members-list {
            display: flex;
            flex-direction: column;
        }

        .member-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s;
        }

        .member-item:last-child {
            border-bottom: none;
        }

        .member-item:hover {
            background: #f8fafc;
        }

        .member-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .member-info {
            flex: 1;
        }

        .member-name {
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .member-email {
            font-size: 0.8rem;
            color: #64748b;
        }

        .member-role {
            min-width: 80px;
            text-align: center;
        }

        .member-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .role-form select {
            width: 100px;
            font-size: 0.8rem;
        }

        .btn-icon-delete {
            width: 36px;
            height: 36px;
            border: none;
            background: #fee2e2;
            color: #ef4444;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .btn-icon-delete:hover {
            background: #ef4444;
            color: white;
        }

        .badge-primary {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
        }

        .badge-warning {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
        }

        .badge-secondary {
            background: #e2e8f0;
            color: #475569;
        }

        @media (max-width: 768px) {
            .invite-form .form-row {
                flex-direction: column;
            }

            .member-item {
                flex-wrap: wrap;
            }

            .member-actions {
                width: 100%;
                justify-content: flex-end;
                margin-top: 0.5rem;
            }
        }
    </style>
@endsection