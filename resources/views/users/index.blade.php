@extends('layouts.app')

@section('title', 'Team Members')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Team Members</h1>
            <p class="page-subtitle">Manage your team and their roles</p>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Add Member
        </a>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <form action="{{ route('users.index') }}" method="GET">
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or email..."
                        value="{{ request('search') }}" style="flex: 1;">
                    <select name="role" class="form-control" style="width: 150px;">
                        <option value="">All Roles</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="manager" {{ request('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                        <option value="member" {{ request('role') === 'member' ? 'selected' : '' }}>Member</option>
                    </select>
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-filter"></i>
                        Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Role</th>
                        <th>Projects</th>
                        <th>Tasks</th>
                        <th>Joined</th>
                        <th style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div class="avatar">{{ $user->initials }}</div>
                                    <div>
                                        <div class="user-name">{{ $user->name }}</div>
                                        <div class="user-email text-muted text-sm">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="role-badge role-{{ $user->role }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>
                                <span class="stat-number">{{ $user->projects_count ?? 0 }}</span>
                            </td>
                            <td>
                                <span class="stat-number">{{ $user->tasks_count ?? 0 }}</span>
                            </td>
                            <td>
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="{{ route('users.show', $user) }}" class="btn-icon" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('users.edit', $user) }}" class="btn-icon" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(auth()->id() !== $user->id)
                                        <form action="{{ route('users.destroy', $user) }}" method="POST"
                                            onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-icon text-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 3rem;">
                                <i class="fas fa-users" style="font-size: 2rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
                                <p class="text-muted">No team members found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($users->hasPages())
        <div style="margin-top: 2rem; display: flex; justify-content: center;">
            {{ $users->withQueryString()->links() }}
        </div>
    @endif

    <style>
        .user-name {
            font-weight: 600;
            color: #1e293b;
        }

        .role-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .role-admin {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
        }

        .role-manager {
            background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
            color: #3730a3;
        }

        .role-member {
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            color: #475569;
        }

        .stat-number {
            font-weight: 600;
            color: #1e293b;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            background: #f1f5f9;
            color: #64748b;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-icon:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .btn-icon.text-danger:hover {
            background: #fee2e2;
            color: #ef4444;
        }
    </style>
@endsection