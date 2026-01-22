@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Profile</h1>
            <p class="page-subtitle">Update your account information</p>
        </div>
        <a href="{{ route('profile.show') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Profile
        </a>
    </div>

    <div class="grid grid-cols-3">
        <!-- Profile Picture -->
        <div class="card" style="grid-column: span 1;">
            <div class="card-header">Profile Picture</div>
            <div class="card-body" style="text-align: center;">
                <div class="avatar-upload-container" style="position: relative; margin-bottom: 1.5rem;">
                    <div class="avatar avatar-lg" id="avatarPreviewContainer" 
                        style="width: 120px; height: 120px; font-size: 3rem; margin: 0 auto; cursor: pointer; position: relative; overflow: hidden; border-radius: 50%;">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" id="avatarPreview"
                                style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        @else
                            <span id="avatarInitials">{{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}</span>
                            <img src="" alt="Avatar" id="avatarPreview" style="display: none; width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        @endif
                        <div class="avatar-overlay" onclick="document.getElementById('avatarInput').click()"
                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s; border-radius: 50%; cursor: pointer;">
                            <i class="fas fa-camera" style="color: white; font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
                <p class="text-muted" style="font-size: 0.75rem; margin-bottom: 1rem;">
                    Klik foto untuk mengganti<br>
                    Max 2MB (JPG, PNG, GIF, WebP)
                </p>
                @error('avatar')
                    <div class="alert alert-danger" style="padding: 0.5rem; font-size: 0.8rem; margin-bottom: 1rem;">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>

        <!-- Edit Form -->
        <div class="card" style="grid-column: span 2;">
            <div class="card-header">Account Information</div>
            <div class="card-body">
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="profileForm">
                    @csrf
                    @method('PUT')
                    
                    <!-- Hidden file input for avatar -->
                    <input type="file" name="avatar" id="avatarInput" 
                        accept="image/jpeg,image/png,image/gif,image/webp" 
                        style="display: none;">

                    <div class="form-group">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control"
                            value="{{ old('name', $user->name ?? '') }}" required>
                        @error('name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" class="form-control"
                            value="{{ $user->email ?? '' }}" disabled readonly
                            style="background-color: #f1f5f9; cursor: not-allowed;">
                        <small class="text-muted">Email tidak dapat diubah</small>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Password -->
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
            <i class="fas fa-lock" style="margin-right: 0.5rem;"></i>
            Change Password
        </div>
        <div class="card-body">
            <form action="{{ route('profile.password') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-3" style="gap: 1.5rem;">
                    <div class="form-group">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-control"
                            placeholder="Enter current password" required>
                        @error('current_password')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" id="password" name="password" class="form-control"
                            placeholder="Enter new password" required>
                        @error('password')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                            placeholder="Confirm new password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-key"></i> Update Password
                </button>
            </form>
        </div>
    </div>

<style>
    .avatar-upload-container:hover .avatar-overlay {
        opacity: 1 !important;
    }
    
    .avatar-upload-container .avatar {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');
    const avatarInitials = document.getElementById('avatarInitials');
    const maxSize = 2 * 1024 * 1024; // 2MB
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
    avatarInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        
        if (!file) return;
        
        // Validate file type
        if (!allowedTypes.includes(file.type)) {
            alert('Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP.');
            this.value = '';
            return;
        }
        
        // Validate file size
        if (file.size > maxSize) {
            alert('Ukuran file terlalu besar. Maksimal 2MB.');
            this.value = '';
            return;
        }
        
        // Preview the image
        const reader = new FileReader();
        reader.onload = function(e) {
            avatarPreview.src = e.target.result;
            avatarPreview.style.display = 'block';
            if (avatarInitials) {
                avatarInitials.style.display = 'none';
            }
        };
        reader.readAsDataURL(file);
    });
});
</script>

@endsection