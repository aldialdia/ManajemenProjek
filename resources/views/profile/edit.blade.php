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
                <form action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data"
                    id="avatarUploadForm">
                    @csrf
                    @method('PUT')

                    <div class="avatar-upload-container" style="position: relative; margin-bottom: 1.5rem;">
                        <div class="avatar avatar-lg" id="avatarPreviewContainer"
                            style="width: 120px; height: 120px; font-size: 3rem; margin: 0 auto; cursor: pointer; position: relative; overflow: hidden; border-radius: 50%;">
                            @if($user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" id="avatarPreview"
                                    style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                            @else
                                <span id="avatarInitials">{{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}</span>
                                <img src="" alt="Avatar" id="avatarPreview"
                                    style="display: none; width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
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

                    <!-- Hidden file input for avatar -->
                    <input type="file" name="avatar" id="avatarInput" accept="image/jpeg,image/png,image/gif,image/webp"
                        style="display: none;">

                    <!-- File name display -->
                    <div id="fileNameDisplay"
                        style="display: none; margin-bottom: 1rem; padding: 0.75rem; background: #f1f5f9; border-radius: 8px; font-size: 0.85rem; color: #475569;">
                        <i class="fas fa-file-image" style="margin-right: 0.5rem; color: #6366f1;"></i>
                        <span id="fileName"></span>
                        <button type="button" onclick="clearAvatarSelection()"
                            style="float: right; background: none; border: none; color: #ef4444; cursor: pointer; padding: 0; font-size: 0.9rem;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Validation message -->
                    <div id="validationMessage"
                        style="display: none; padding: 0.75rem; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; color: #dc2626; font-size: 0.85rem; margin-bottom: 1rem;">
                        <i class="fas fa-exclamation-circle" style="margin-right: 0.5rem;"></i>
                        <span>Pilih foto terlebih dahulu!</span>
                    </div>

                    @error('avatar')
                        <div class="alert alert-danger" style="padding: 0.5rem; font-size: 0.8rem; margin-bottom: 1rem;">
                            {{ $message }}
                        </div>
                    @enderror

                    <!-- Upload Button -->
                    <button type="submit" id="uploadAvatarBtn" class="btn btn-primary"
                        style="padding: 0.5rem 1.25rem; font-size: 0.875rem; display: block; margin: 0 auto;" disabled>
                        <i class="fas fa-upload"></i> Upload Foto
                    </button>
                </form>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="card" style="grid-column: span 2;">
            <div class="card-header">Account Information</div>
            <div class="card-body">
                <form action="{{ route('profile.update') }}" method="POST" id="profileForm">
                    @csrf
                    @method('PUT')

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
                        <input type="email" id="email" class="form-control" value="{{ $user->email ?? '' }}" disabled
                            readonly style="background-color: #f1f5f9; cursor: not-allowed;">
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
        document.addEventListener('DOMContentLoaded', function () {
            const avatarInput = document.getElementById('avatarInput');
            const avatarPreview = document.getElementById('avatarPreview');
            const avatarInitials = document.getElementById('avatarInitials');
            const uploadBtn = document.getElementById('uploadAvatarBtn');
            const fileNameDisplay = document.getElementById('fileNameDisplay');
            const fileName = document.getElementById('fileName');
            const validationMessage = document.getElementById('validationMessage');
            const avatarUploadForm = document.getElementById('avatarUploadForm');

            const maxSize = 2 * 1024 * 1024; // 2MB
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            avatarInput.addEventListener('change', function (e) {
                const file = e.target.files[0];

                // Hide validation message
                validationMessage.style.display = 'none';

                if (!file) {
                    uploadBtn.disabled = true;
                    fileNameDisplay.style.display = 'none';
                    return;
                }

                // Validate file type
                if (!allowedTypes.includes(file.type)) {
                    showInfoModal('Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP.');
                    this.value = '';
                    uploadBtn.disabled = true;
                    fileNameDisplay.style.display = 'none';
                    return;
                }

                // Validate file size
                if (file.size > maxSize) {
                    showInfoModal('Ukuran file terlalu besar. Maksimal 2MB.');
                    this.value = '';
                    uploadBtn.disabled = true;
                    fileNameDisplay.style.display = 'none';
                    return;
                }

                // Show file name
                fileName.textContent = file.name;
                fileNameDisplay.style.display = 'block';

                // Enable upload button
                uploadBtn.disabled = false;

                // Preview the image
                const reader = new FileReader();
                reader.onload = function (e) {
                    avatarPreview.src = e.target.result;
                    avatarPreview.style.display = 'block';
                    if (avatarInitials) {
                        avatarInitials.style.display = 'none';
                    }
                };
                reader.readAsDataURL(file);
            });

            // Handle form submission
            avatarUploadForm.addEventListener('submit', function (e) {
                if (!avatarInput.files || !avatarInput.files[0]) {
                    e.preventDefault();
                    validationMessage.style.display = 'block';

                    // Scroll to validation message
                    validationMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });

                    // Hide after 3 seconds
                    setTimeout(() => {
                        validationMessage.style.display = 'none';
                    }, 3000);
                }
            });
        });

        // Function to clear avatar selection
        function clearAvatarSelection() {
            const avatarInput = document.getElementById('avatarInput');
            const uploadBtn = document.getElementById('uploadAvatarBtn');
            const fileNameDisplay = document.getElementById('fileNameDisplay');
            const avatarPreview = document.getElementById('avatarPreview');
            const avatarInitials = document.getElementById('avatarInitials');
            const validationMessage = document.getElementById('validationMessage');

            // Clear file input
            avatarInput.value = '';

            // Hide file name display
            fileNameDisplay.style.display = 'none';

            // Disable upload button
            uploadBtn.disabled = true;

            // Hide validation message
            validationMessage.style.display = 'none';

            // Reset preview to original avatar or initials
            @if($user->avatar)
                avatarPreview.src = "{{ asset('storage/' . $user->avatar) }}";
                avatarPreview.style.display = 'block';
                if (avatarInitials) {
                    avatarInitials.style.display = 'none';
                }
            @else
                avatarPreview.style.display = 'none';
                if (avatarInitials) {
                    avatarInitials.style.display = 'flex';
                }
            @endif
    }
    </script>

@endsection