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

        <!-- Pending Invitations (Only visible to managers/admins who can invite) -->
        @if($canInvite && $pendingInvitations->count() > 0)
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
                                onsubmit="return confirmSubmit(this, 'Batalkan undangan ini?')">
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
                        @php
                            // Warna konsisten berdasarkan user ID
                            $colorIndex = $member->id % 4;
                            $colors = [
                                ['start' => '#6366f1', 'end' => '#4f46e5'],
                                ['start' => '#f97316', 'end' => '#ea580c'],
                                ['start' => '#22c55e', 'end' => '#16a34a'],
                                ['start' => '#ec4899', 'end' => '#db2777'],
                            ];
                            $userColor = $colors[$colorIndex];
                        @endphp
                        <div class="member-item">
                            @if($member->avatar)
                                <div class="member-avatar"
                                    style="background-image: url('{{ asset('storage/' . $member->avatar) }}'); background-size: cover; background-position: center;">
                                </div>
                            @else
                                <div class="member-avatar"
                                    style="background: linear-gradient(135deg, {{ $userColor['start'] }} 0%, {{ $userColor['end'] }} 100%);">
                                    {{ $member->initials }}
                                </div>
                            @endif
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
                            <button type="button" class="btn-icon-view"
                                onclick="showMemberProfile({{ $project->id }}, {{ $member->id }}, {{ $colorIndex }}, {{ $member->avatar ? 'true' : 'false' }})"
                                title="Lihat Profil">
                                <i class="fas fa-search"></i>
                            </button>
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
                                        onsubmit="return confirmSubmit(this, 'Hapus {{ $member->name }} dari project?')">
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
                                        onsubmit="return confirmSubmit(this, 'Hapus {{ $member->name }} dari project?')">
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

        .btn-icon-view {
            width: 36px;
            height: 36px;
            border: none;
            background: #e0e7ff;
            color: #6366f1;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .btn-icon-view:hover {
            background: #6366f1;
            color: white;
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

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            width: 100%;
            max-width: 480px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .modal-header h3 {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modal-close {
            width: 32px;
            height: 32px;
            border: none;
            background: #f1f5f9;
            color: #64748b;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .modal-close:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .profile-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.75rem;
            margin-bottom: 1rem;
        }

        .profile-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .profile-email {
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 0.75rem;
        }

        .profile-info {
            display: grid;
            gap: 1rem;
        }

        .profile-info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem;
            background: #f8fafc;
            border-radius: 8px;
        }

        .profile-info-label {
            color: #64748b;
            font-size: 0.875rem;
        }

        .profile-info-value {
            font-weight: 600;
            color: #1e293b;
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .stat-item {
            text-align: center;
            padding: 1rem 0.5rem;
            background: #f8fafc;
            border-radius: 8px;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #6366f1;
        }

        .stat-value.success {
            color: #22c55e;
        }

        .stat-value.warning {
            color: #f59e0b;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.25rem;
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

    <!-- Member Profile Modal -->
    <div id="profileModal" class="modal-overlay" onclick="closeProfileModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h3><i class="fas fa-user-circle"></i> Profil Anggota</h3>
                <button type="button" class="modal-close" onclick="closeProfileModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="profileLoading" style="text-align: center; padding: 2rem;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #6366f1;"></i>
                    <p style="margin-top: 1rem; color: #64748b;">Memuat profil...</p>
                </div>
                <div id="profileContent" style="display: none;">
                    <div class="profile-header">
                        <div class="profile-avatar" id="profileAvatar"></div>
                        <div class="profile-name" id="profileName"></div>
                        <div class="profile-email" id="profileEmail"></div>
                        <span class="badge" id="profileRole"></span>
                    </div>
                    <div class="profile-info">
                        <div class="profile-info-item">
                            <span class="profile-info-label">Bergabung Sejak</span>
                            <span class="profile-info-value" id="profileJoined"></span>
                        </div>
                    </div>
                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-value" id="statTotalTasks">0</div>
                            <div class="stat-label">Total Tugas</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value success" id="statCompletedTasks">0</div>
                            <div class="stat-label">Selesai</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value warning" id="statPendingTasks">0</div>
                            <div class="stat-label">Pending</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const avatarColors = [
            ['#6366f1', '#4f46e5'],
            ['#f97316', '#ea580c'],
            ['#22c55e', '#16a34a'],
            ['#ec4899', '#db2777']
        ];

        function showMemberProfile(projectId, userId, colorIndex, hasAvatar) {
            const modal = document.getElementById('profileModal');
            const loading = document.getElementById('profileLoading');
            const content = document.getElementById('profileContent');

            modal.classList.add('active');
            loading.style.display = 'block';
            content.style.display = 'none';

            fetch(`/projects/${projectId}/team/${userId}/profile`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        closeProfileModal();
                        return;
                    }

                    // Update avatar
                    const avatar = document.getElementById('profileAvatar');
                    if (data.avatar) {
                        // User has profile picture
                        avatar.style.backgroundImage = `url('/storage/${data.avatar}')`;
                        avatar.style.backgroundSize = 'cover';
                        avatar.style.backgroundPosition = 'center';
                        avatar.textContent = '';
                    } else {
                        // Use gradient with initials
                        const colors = avatarColors[colorIndex % 4];
                        avatar.style.backgroundImage = 'none';
                        avatar.style.background = `linear-gradient(135deg, ${colors[0]} 0%, ${colors[1]} 100%)`;
                        avatar.textContent = data.initials;
                    }

                    // Update info
                    document.getElementById('profileName').textContent = data.name;
                    document.getElementById('profileEmail').textContent = data.email;
                    document.getElementById('profileJoined').textContent = data.joined_at;

                    // Update role badge
                    const roleBadge = document.getElementById('profileRole');
                    roleBadge.textContent = data.role.charAt(0).toUpperCase() + data.role.slice(1);
                    roleBadge.className = 'badge';
                    if (data.role === 'manager') {
                        roleBadge.classList.add('badge-primary');
                    } else if (data.role === 'admin') {
                        roleBadge.classList.add('badge-warning');
                    } else {
                        roleBadge.classList.add('badge-secondary');
                    }

                    // Update stats
                    document.getElementById('statTotalTasks').textContent = data.stats.total_tasks;
                    document.getElementById('statCompletedTasks').textContent = data.stats.completed_tasks;
                    document.getElementById('statPendingTasks').textContent = data.stats.pending_tasks;

                    loading.style.display = 'none';
                    content.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Gagal memuat profil anggota');
                    closeProfileModal();
                });
        }

        function closeProfileModal(event) {
            if (event && event.target !== event.currentTarget) return;
            document.getElementById('profileModal').classList.remove('active');
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeProfileModal();
            }
        });
    </script>
@endsection