@extends('layouts.app')

@section('title', 'Edit Client')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Client</h1>
            <p class="page-subtitle">Update client information</p>
        </div>
        <a href="{{ route('clients.show', $client ?? 1) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Client
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('clients.update', $client ?? 1) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2" style="gap: 1.5rem;">
                    <div class="form-group">
                        <label for="name" class="form-label">Client Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control"
                            placeholder="Enter client or company name" value="{{ old('name', $client->name ?? '') }}"
                            required>
                        @error('name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="company" class="form-label">Company</label>
                        <input type="text" id="company" name="company" class="form-control"
                            placeholder="Company name (if applicable)" value="{{ old('company', $client->company ?? '') }}">
                        @error('company')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="client@example.com"
                            value="{{ old('email', $client->email ?? '') }}">
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" placeholder="+62 xxx xxxx xxxx"
                            value="{{ old('phone', $client->phone ?? '') }}">
                        @error('phone')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label for="address" class="form-label">Address</label>
                        <textarea id="address" name="address" class="form-control" rows="3"
                            placeholder="Full address...">{{ old('address', $client->address ?? '') }}</textarea>
                        @error('address')
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
                        <a href="{{ route('clients.show', $client ?? 1) }}" class="btn btn-secondary">Cancel</a>
                    </div>
                    <form action="{{ route('clients.destroy', $client ?? 1) }}" method="POST"
                        onsubmit="return confirmSubmit(this, 'Apakah Anda yakin ingin menghapus client ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete Client
                        </button>
                    </form>
                </div>
            </form>
        </div>
    </div>
@endsection