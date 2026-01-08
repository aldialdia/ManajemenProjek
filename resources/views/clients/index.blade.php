@extends('layouts.app')

@section('title', 'Clients')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Clients</h1>
            <p class="page-subtitle">Manage your client database</p>
        </div>
        <a href="{{ route('clients.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Add Client
        </a>
    </div>

    <!-- Search -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <form action="{{ route('clients.index') }}" method="GET">
                <div style="display: flex; gap: 1rem;">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, company, or email..."
                        value="{{ request('search') }}" style="flex: 1;">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i>
                        Search
                    </button>
                    @if(request('search'))
                        <a href="{{ route('clients.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Clients Grid -->
    <div class="grid grid-cols-3">
        @forelse($clients as $client)
            <div class="card client-card">
                <div class="card-body">
                    <div class="client-header">
                        <div class="client-avatar">
                            {{ strtoupper(substr($client->name, 0, 1)) }}
                        </div>
                        <div class="dropdown" id="clientMenu{{ $client->id }}">
                            <button class="btn-icon" onclick="toggleDropdown('clientMenu{{ $client->id }}')">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a href="{{ route('clients.show', $client) }}" class="dropdown-item">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('clients.edit', $client) }}" class="dropdown-item">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('clients.destroy', $client) }}" method="POST"
                                    onsubmit="return confirm('Delete this client?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <h3 class="client-name">
                        <a href="{{ route('clients.show', $client) }}">{{ $client->name }}</a>
                    </h3>

                    @if($client->company)
                        <p class="client-company">
                            <i class="fas fa-building"></i>
                            {{ $client->company }}
                        </p>
                    @endif

                    <div class="client-contacts">
                        @if($client->email)
                            <a href="mailto:{{ $client->email }}" class="contact-item">
                                <i class="fas fa-envelope"></i>
                                {{ $client->email }}
                            </a>
                        @endif
                        @if($client->phone)
                            <a href="tel:{{ $client->phone }}" class="contact-item">
                                <i class="fas fa-phone"></i>
                                {{ $client->phone }}
                            </a>
                        @endif
                    </div>

                    <div class="client-stats">
                        <span>
                            <i class="fas fa-folder"></i>
                            {{ $client->projects_count ?? 0 }} Projects
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state" style="grid-column: span 3;">
                <i class="fas fa-building"></i>
                <h3>No Clients Found</h3>
                <p>Add your first client to get started</p>
                <a href="{{ route('clients.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Add Client
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($clients->hasPages())
        <div style="margin-top: 2rem; display: flex; justify-content: center;">
            {{ $clients->withQueryString()->links() }}
        </div>
    @endif

    <style>
        .client-card {
            transition: all 0.2s;
        }

        .client-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .client-avatar {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            background: transparent;
            color: #64748b;
            cursor: pointer;
        }

        .btn-icon:hover {
            background: #f1f5f9;
        }

        .client-name {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .client-name a {
            color: #1e293b;
            text-decoration: none;
        }

        .client-name a:hover {
            color: #6366f1;
        }

        .client-company {
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .client-company i {
            margin-right: 0.5rem;
        }

        .client-contacts {
            margin-bottom: 1rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #64748b;
            font-size: 0.875rem;
            text-decoration: none;
            margin-bottom: 0.25rem;
        }

        .contact-item:hover {
            color: #6366f1;
        }

        .client-stats {
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
            font-size: 0.875rem;
            color: #64748b;
        }

        .client-stats i {
            margin-right: 0.25rem;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 16px;
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #64748b;
            margin-bottom: 1.5rem;
        }
    </style>
@endsection