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
                        style="width: 120px; height: 120px; font-size: 3rem; margin: 0 auto; position: relative; overflow: hidden; border-radius: 50%;">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" id="avatarPreview"
                                style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        @else
                            <span id="avatarInitials">{{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}</span>
                            <img src="" alt="Avatar" id="avatarPreview"
                                style="display: none; width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        @endif
                    </div>
                </div>

                <p class="text-muted" style="font-size: 0.75rem; margin-bottom: 1rem;">
                    Max 2MB (JPG, PNG, GIF, WebP)
                </p>

                <!-- Hidden file inputs for photo selection -->
                <input type="file" id="galleryInput" accept="image/jpeg,image/png,image/gif,image/webp" style="display: none;">
                <input type="file" id="cameraInput" accept="image/*" capture="user" style="display: none;">

                <!-- Hidden canvas for cropped image -->
                <canvas id="cropCanvas" style="display: none;"></canvas>

                <!-- Status indicator for pending photo change -->
                <div id="photoChangeStatus" style="display: none; margin-bottom: 1rem; padding: 0.5rem 0.75rem; background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; font-size: 0.8rem; color: #92400e;">
                    <i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i>
                    Foto baru akan tersimpan setelah klik "Save Changes"
                </div>

                @error('avatar')
                    <div class="alert alert-danger" style="padding: 0.5rem; font-size: 0.8rem; margin-bottom: 1rem;">
                        {{ $message }}
                    </div>
                @enderror

                <!-- Edit Photo Button -->
                <button type="button" id="editPhotoBtn" class="btn btn-primary"
                    style="padding: 0.5rem 1.25rem; font-size: 0.875rem; display: block; margin: 0 auto;">
                    <i class="fas fa-camera"></i> Edit Foto
                </button>
            </div>
        </div>

    <!-- Photo Source Selection Modal -->
    <div id="photoSourceModal" class="photo-modal-overlay" style="display: none;">
        <div class="photo-modal-content photo-source-modal">
            <div class="photo-modal-header">
                <h3>Pilih Sumber Foto</h3>
                <button type="button" class="photo-modal-close" onclick="closePhotoSourceModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="photo-modal-body">
                <div class="photo-source-options">
                    <button type="button" class="photo-source-btn" onclick="openCamera()">
                        <div class="photo-source-icon">
                            <i class="fas fa-camera"></i>
                        </div>
                        <span>Ambil Foto</span>
                        <small>Gunakan kamera perangkat</small>
                    </button>
                    <button type="button" class="photo-source-btn" onclick="openGallery()">
                        <div class="photo-source-icon">
                            <i class="fas fa-images"></i>
                        </div>
                        <span>Pilih dari Galeri</span>
                        <small>Pilih dari file perangkat</small>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Photo Adjustment Modal -->
    <div id="photoAdjustModal" class="photo-modal-overlay" style="display: none;">
        <div class="photo-modal-content photo-adjust-modal">
            <div class="photo-modal-header">
                <h3>Sesuaikan Foto</h3>
                <button type="button" class="photo-modal-close" onclick="closePhotoAdjustModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="photo-modal-body">
                <div class="crop-container">
                    <div class="crop-area" id="cropArea">
                        <img id="cropImage" src="" alt="Crop Preview" draggable="false">
                        <div class="crop-overlay"></div>
                        <div class="crop-circle"></div>
                    </div>
                </div>
                <div class="zoom-controls">
                    <i class="fas fa-search-minus"></i>
                    <input type="range" id="zoomSlider" min="100" max="300" value="100">
                    <i class="fas fa-search-plus"></i>
                </div>
                <p class="crop-hint">Geser gambar untuk mengatur posisi</p>
            </div>
            <div class="photo-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closePhotoAdjustModal()">
                    Batal
                </button>
                <button type="button" class="btn btn-primary" onclick="saveCroppedImage()">
                    <i class="fas fa-check"></i> Simpan
                </button>
            </div>
        </div>
    </div>

        <!-- Edit Form -->
        <div class="card" style="grid-column: span 2;">
            <div class="card-header">Account Information</div>
            <div class="card-body">
                <form action="{{ route('profile.update') }}" method="POST" id="profileForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Hidden file input for avatar (populated by crop function) -->
                    <input type="file" name="avatar" id="avatarInput" style="display: none;">

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


    <!-- Floating Password Error Notification -->
    @if($errors->has('current_password') || $errors->has('password'))
        <div id="passwordErrorToast" class="password-error-toast">
            <i class="fas fa-exclamation-circle"></i>
            <span>
                @php
                    $errorMessages = [];
                    if($errors->has('current_password')) {
                        $errorMessages[] = 'Password terkini tidak sesuai';
                    }
                    if($errors->has('password')) {
                        $errorMessages[] = 'Password baru dan konfirmasi tidak sama';
                    }
                @endphp
                {{ implode(' • ', $errorMessages) }}
            </span>
            <button type="button" onclick="closePasswordErrorToast()" class="password-error-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <!-- Change Password -->
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
            <i class="fas fa-lock" style="margin-right: 0.5rem;"></i>
            Change Password
        </div>
        <div class="card-body">
            <form action="{{ route('profile.password') }}" method="POST" id="passwordForm">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-3" style="gap: 1.5rem;">
                    <div class="form-group">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-control @error('current_password') is-invalid @enderror"
                            placeholder="Enter current password" required>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror"
                            placeholder="Enter new password" required>
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
        .avatar-upload-container .avatar {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Password Error Toast */
        .password-error-toast {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            padding: 0.875rem 1.25rem;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(220, 38, 38, 0.4);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            z-index: 10000;
            animation: slideDown 0.4s ease, shake 0.5s ease 0.4s;
            max-width: 90%;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(-50%) translateY(0); }
            25% { transform: translateX(-50%) translateX(-5px); }
            75% { transform: translateX(-50%) translateX(5px); }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
            to {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px);
            }
        }

        .password-error-toast i.fa-exclamation-circle {
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .password-error-toast span {
            font-size: 0.9rem;
            font-weight: 500;
        }

        .password-error-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s;
            flex-shrink: 0;
            margin-left: 0.5rem;
        }

        .password-error-close:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .password-error-close i {
            font-size: 0.75rem;
        }

        /* Invalid input styling */
        .form-control.is-invalid {
            border-color: #dc2626 !important;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1) !important;
        }

        /* Photo Modal Overlay */
        .photo-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            animation: fadeIn 0.2s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Photo Modal Content */
        .photo-modal-content {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 90%;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .photo-source-modal {
            width: 360px;
        }

        .photo-adjust-modal {
            width: 420px;
        }

        .photo-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .photo-modal-header h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
        }

        .photo-modal-close {
            background: none;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #64748b;
            transition: all 0.2s;
        }

        .photo-modal-close:hover {
            background: #f1f5f9;
            color: #1e293b;
        }

        .photo-modal-body {
            padding: 1.5rem;
        }

        .photo-modal-footer {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            padding: 1rem 1.5rem;
            border-top: 1px solid #e2e8f0;
        }

        /* Photo Source Options */
        .photo-source-options {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .photo-source-btn {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.25rem;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: left;
        }

        .photo-source-btn:hover {
            border-color: #6366f1;
            background: #f1f5f9;
        }

        .photo-source-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .photo-source-btn span {
            display: block;
            font-weight: 600;
            color: #1e293b;
            font-size: 0.95rem;
        }

        .photo-source-btn small {
            display: block;
            color: #64748b;
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }

        /* Crop Container */
        .crop-container {
            display: flex;
            justify-content: center;
            margin-bottom: 1.25rem;
        }

        .crop-area {
            position: relative;
            width: 280px;
            height: 280px;
            background: #1e293b;
            border-radius: 12px;
            overflow: hidden;
            cursor: grab;
        }

        .crop-area:active {
            cursor: grabbing;
        }

        .crop-area img {
            position: absolute;
            max-width: none;
            user-select: none;
            -webkit-user-drag: none;
        }

        .crop-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            pointer-events: none;
        }

        .crop-circle {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 220px;
            height: 220px;
            border-radius: 50%;
            box-shadow: 0 0 0 2000px rgba(0, 0, 0, 0.5);
            border: 3px solid white;
            pointer-events: none;
        }

        /* Zoom Controls */
        .zoom-controls {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0 1rem;
        }

        .zoom-controls i {
            color: #64748b;
            font-size: 0.9rem;
        }

        .zoom-controls input[type="range"] {
            flex: 1;
            height: 6px;
            -webkit-appearance: none;
            appearance: none;
            background: #e2e8f0;
            border-radius: 3px;
            outline: none;
        }

        .zoom-controls input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 18px;
            height: 18px;
            background: #6366f1;
            border-radius: 50%;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .zoom-controls input[type="range"]::-webkit-slider-thumb:hover {
            transform: scale(1.1);
        }

        .zoom-controls input[type="range"]::-moz-range-thumb {
            width: 18px;
            height: 18px;
            background: #6366f1;
            border-radius: 50%;
            cursor: pointer;
            border: none;
        }

        .crop-hint {
            text-align: center;
            color: #64748b;
            font-size: 0.8rem;
            margin: 1rem 0 0 0;
        }
    </style>

    <script>
        // Global variables for image cropping
        let cropImage = null;
        let imageScale = 1;
        let imageX = 0;
        let imageY = 0;
        let isDragging = false;
        let dragStartX = 0;
        let dragStartY = 0;
        let originalFile = null;

        document.addEventListener('DOMContentLoaded', function () {
            const editPhotoBtn = document.getElementById('editPhotoBtn');
            const galleryInput = document.getElementById('galleryInput');
            const cameraInput = document.getElementById('cameraInput');
            const zoomSlider = document.getElementById('zoomSlider');
            const cropArea = document.getElementById('cropArea');
            cropImage = document.getElementById('cropImage');

            // Edit Photo Button - opens source selection modal
            editPhotoBtn.addEventListener('click', function () {
                openPhotoSourceModal();
            });

            // Handle file selection from gallery
            galleryInput.addEventListener('change', function (e) {
                handleFileSelection(e.target.files[0]);
            });

            // Handle file selection from camera
            cameraInput.addEventListener('change', function (e) {
                handleFileSelection(e.target.files[0]);
            });

            // Zoom slider
            zoomSlider.addEventListener('input', function () {
                imageScale = this.value / 100;
                updateCropImage();
            });

            // Mouse drag for positioning
            cropArea.addEventListener('mousedown', function (e) {
                e.preventDefault();
                isDragging = true;
                dragStartX = e.clientX - imageX;
                dragStartY = e.clientY - imageY;
            });

            document.addEventListener('mousemove', function (e) {
                if (!isDragging) return;
                imageX = e.clientX - dragStartX;
                imageY = e.clientY - dragStartY;
                updateCropImage();
            });

            document.addEventListener('mouseup', function () {
                isDragging = false;
            });

            // Touch drag for mobile
            cropArea.addEventListener('touchstart', function (e) {
                e.preventDefault();
                isDragging = true;
                const touch = e.touches[0];
                dragStartX = touch.clientX - imageX;
                dragStartY = touch.clientY - imageY;
            });

            document.addEventListener('touchmove', function (e) {
                if (!isDragging) return;
                const touch = e.touches[0];
                imageX = touch.clientX - dragStartX;
                imageY = touch.clientY - dragStartY;
                updateCropImage();
            });

            document.addEventListener('touchend', function () {
                isDragging = false;
            });
        });

        function openPhotoSourceModal() {
            document.getElementById('photoSourceModal').style.display = 'flex';
        }

        function closePhotoSourceModal() {
            document.getElementById('photoSourceModal').style.display = 'none';
        }

        function openCamera() {
            closePhotoSourceModal();
            document.getElementById('cameraInput').click();
        }

        function openGallery() {
            closePhotoSourceModal();
            document.getElementById('galleryInput').click();
        }

        function handleFileSelection(file) {
            if (!file) return;

            const maxSize = 2 * 1024 * 1024; // 2MB
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            // Validate file type
            if (!allowedTypes.includes(file.type)) {
                if (typeof showInfoModal === 'function') {
                    showInfoModal('Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP.');
                } else {
                    alert('Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP.');
                }
                return;
            }

            // Validate file size
            if (file.size > maxSize) {
                if (typeof showInfoModal === 'function') {
                    showInfoModal('Ukuran file terlalu besar. Maksimal 2MB.');
                } else {
                    alert('Ukuran file terlalu besar. Maksimal 2MB.');
                }
                return;
            }

            originalFile = file;

            // Load image for cropping
            const reader = new FileReader();
            reader.onload = function (e) {
                cropImage.src = e.target.result;
                cropImage.onload = function () {
                    // Reset crop settings
                    imageScale = 1;
                    imageX = 0;
                    imageY = 0;
                    document.getElementById('zoomSlider').value = 100;

                    // Center the image initially
                    const cropAreaSize = 280;
                    const imgWidth = cropImage.naturalWidth;
                    const imgHeight = cropImage.naturalHeight;

                    // Calculate initial scale to fit the crop circle (220px)
                    const minDimension = Math.min(imgWidth, imgHeight);
                    const initialScale = 220 / minDimension;
                    imageScale = initialScale;
                    document.getElementById('zoomSlider').value = initialScale * 100;
                    document.getElementById('zoomSlider').min = Math.floor(initialScale * 100);
                    document.getElementById('zoomSlider').max = Math.floor(initialScale * 300);

                    // Center the image
                    const scaledWidth = imgWidth * imageScale;
                    const scaledHeight = imgHeight * imageScale;
                    imageX = (cropAreaSize - scaledWidth) / 2;
                    imageY = (cropAreaSize - scaledHeight) / 2;

                    updateCropImage();
                    openPhotoAdjustModal();
                };
            };
            reader.readAsDataURL(file);
        }

        function openPhotoAdjustModal() {
            document.getElementById('photoAdjustModal').style.display = 'flex';
        }

        function closePhotoAdjustModal() {
            document.getElementById('photoAdjustModal').style.display = 'none';
            // Clear temporary file inputs
            document.getElementById('galleryInput').value = '';
            document.getElementById('cameraInput').value = '';
        }

        function updateCropImage() {
            if (!cropImage) return;
            cropImage.style.width = (cropImage.naturalWidth * imageScale) + 'px';
            cropImage.style.height = (cropImage.naturalHeight * imageScale) + 'px';
            cropImage.style.left = imageX + 'px';
            cropImage.style.top = imageY + 'px';
        }

        function saveCroppedImage() {
            const canvas = document.getElementById('cropCanvas');
            const ctx = canvas.getContext('2d');

            // Set canvas size to crop circle size
            const cropSize = 220;
            canvas.width = cropSize;
            canvas.height = cropSize;

            // Calculate the visible area in the crop circle
            const cropAreaSize = 280;
            const circleOffset = (cropAreaSize - cropSize) / 2;

            // Source coordinates on the scaled image
            const sourceX = (circleOffset - imageX) / imageScale;
            const sourceY = (circleOffset - imageY) / imageScale;
            const sourceWidth = cropSize / imageScale;
            const sourceHeight = cropSize / imageScale;

            // Draw cropped image to canvas
            ctx.drawImage(
                cropImage,
                sourceX, sourceY, sourceWidth, sourceHeight,
                0, 0, cropSize, cropSize
            );

            // Convert canvas to blob and create file
            canvas.toBlob(function (blob) {
                // Create a new file from the blob
                const croppedFile = new File([blob], 'avatar.jpg', { type: 'image/jpeg' });

                // Create a DataTransfer to set the file input
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(croppedFile);
                document.getElementById('avatarInput').files = dataTransfer.files;

                // Update preview in the main avatar
                const avatarPreview = document.getElementById('avatarPreview');
                const avatarInitials = document.getElementById('avatarInitials');

                avatarPreview.src = canvas.toDataURL('image/jpeg');
                avatarPreview.style.display = 'block';
                if (avatarInitials) {
                    avatarInitials.style.display = 'none';
                }

                // Close modal
                document.getElementById('photoAdjustModal').style.display = 'none';

                // Show status indicator
                document.getElementById('photoChangeStatus').style.display = 'block';

                // Clear temporary file inputs (but keep avatarInput which has the cropped file)
                document.getElementById('galleryInput').value = '';
                document.getElementById('cameraInput').value = '';
            }, 'image/jpeg', 0.9);
        }

        // Password Error Toast Functions
        function closePasswordErrorToast() {
            const toast = document.getElementById('passwordErrorToast');
            if (toast) {
                toast.style.animation = 'fadeOut 0.3s ease forwards';
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }
        }

        // Auto-hide password error toast after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.getElementById('passwordErrorToast');
            if (toast) {
                setTimeout(() => {
                    closePasswordErrorToast();
                }, 5000);
            }
        });
    </script>

@endsection