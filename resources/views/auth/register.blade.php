<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - Sistem Manajemen Proyek</title>

    <!-- Google Font: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            overflow: hidden;
        }

        .login-container {
            display: flex;
            max-width: 1000px;
            width: 100%;
            max-height: calc(100vh - 30px);
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .login-image {
            flex: 1;
            position: relative;
            display: none;
        }

        @media (min-width: 768px) {
            .login-image {
                display: block;
            }
        }

        .login-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .login-form {
            flex: 1;
            padding: 20px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .logo {
            position: absolute;
            top: 20px;
            right: 40px;
        }

        .logo img {
            height: 40px;
        }

        .welcome-text {
            text-align: center;
            margin-bottom: 10px;
        }

        .welcome-text h1 {
            font-size: 22px;
            font-weight: 700;
            color: #333;
            margin-bottom: 3px;
        }

        .welcome-text p {
            color: #666;
            font-size: 12px;
        }

        .alert {
            padding: 8px 10px;
            border-radius: 6px;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-group {
            margin-bottom: 8px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: #333;
            margin-bottom: 3px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            background: #fff;
            transition: border-color 0.3s ease;
        }

        .input-wrapper:focus-within {
            border-color: #f7941d;
        }

        .input-wrapper .icon {
            padding: 0 12px;
            color: #999;
            font-size: 14px;
        }

        .input-wrapper input {
            flex: 1;
            border: none;
            padding: 8px 8px 8px 0;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            outline: none;
            background: transparent;
        }

        .input-wrapper input::placeholder {
            color: #aaa;
        }

        .input-wrapper .toggle-password {
            padding: 0 12px;
            cursor: pointer;
            color: #999;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .input-wrapper .toggle-password:hover {
            color: #f7941d;
        }

        .error-text {
            color: #dc3545;
            font-size: 10px;
            margin-top: 2px;
        }

        .btn-login {
            width: 100%;
            padding: 10px;
            background: #f7941d;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease;
            margin-top: 8px;
        }

        .btn-login:hover {
            background: #e08517;
            transform: translateY(-2px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .register-link {
            text-align: center;
            margin-top: 10px;
            font-size: 12px;
            color: #666;
        }

        .register-link a {
            color: #f7941d;
            text-decoration: none;
            font-weight: 500;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <!-- Left Side - Image -->
        <div class="login-image">
            <img src="{{ asset('images/gedung.png') }}" alt="Bank Nagari Building">
        </div>

        <!-- Right Side - Form -->
        <div class="login-form">
            <div class="logo">
                <img src="{{ asset('images/logo.png') }}" alt="Bank Nagari Logo">
            </div>

            <div class="welcome-text">
                <h1>Daftar Akun</h1>
                <p>Sistem Manajemen Proyek</p>
            </div>

            @if (session('Failed'))
                <div class="alert alert-danger">
                    {{ session('Failed') }}
                </div>
            @endif

            <form action="/register" method="post">
                @csrf

                <div class="form-group">
                    <label for="name">Nama Lengkap</label>
                    <div class="input-wrapper">
                        <span class="icon"><i class="fas fa-user"></i></span>
                        <input type="text" name="name" id="name" placeholder="Masukkan nama lengkap"
                            value="{{ old('name') }}">
                    </div>
                    @error('name')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email address</label>
                    <div class="input-wrapper">
                        <span class="icon"><i class="far fa-envelope"></i></span>
                        <input type="email" name="email" id="email" placeholder="Masukkan email anda"
                            value="{{ old('email') }}">
                    </div>
                    @error('email')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <span class="icon"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" id="password" placeholder="Masukkan password">
                        <span class="toggle-password" data-target="password">
                            <i class="far fa-eye" id="icon-password"></i>
                        </span>
                    </div>
                    @error('password')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <div class="input-wrapper">
                        <span class="icon"><i class="fas fa-lock"></i></span>
                        <input type="password" name="confirm_password" id="confirm_password"
                            placeholder="Konfirmasi password anda">
                        <span class="toggle-password" data-target="confirm_password">
                            <i class="far fa-eye" id="icon-confirm"></i>
                        </span>
                    </div>
                    @error('confirm_password')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn-login">Sign Up</button>
            </form>

            <p class="register-link">
                Sudah punya akun ? <a href="/login">Masuk disini</a>
            </p>
        </div>
    </div>

    <script>
        document.querySelectorAll('.toggle-password').forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const icon = this.querySelector('i');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    </script>
</body>

</html>