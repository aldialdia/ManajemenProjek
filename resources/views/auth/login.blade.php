<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Sistem Manajemen Proyek Nagari</title>

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

        .logo-text {
            font-size: 24px;
            font-weight: 700;
            color: #1a365d;
        }

        .logo-text span {
            color: #f7941d;
        }

        .welcome-text {
            text-align: center;
            margin-bottom: 20px;
        }

        .welcome-text h1 {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .welcome-text p {
            color: #666;
            font-size: 14px;
        }

        .alert {
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 13px;
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
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #333;
            margin-bottom: 6px;
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
            padding: 0 15px;
            color: #999;
            font-size: 18px;
        }

        .input-wrapper input {
            flex: 1;
            border: none;
            padding: 12px 12px 12px 0;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            outline: none;
            background: transparent;
        }

        .input-wrapper input::placeholder {
            color: #aaa;
        }

        .input-wrapper .toggle-password {
            padding: 0 15px;
            cursor: pointer;
            color: #999;
            font-size: 18px;
            transition: color 0.3s ease;
        }

        .input-wrapper .toggle-password:hover {
            color: #f7941d;
        }

        .error-text {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 10px;
            accent-color: #f7941d;
            cursor: pointer;
        }

        .remember-me label {
            font-size: 14px;
            color: #666;
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: #f7941d;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease;
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
            margin-top: 25px;
            font-size: 14px;
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
                <h1>Selamat Datang</h1>
                <p>Sistem Manajemen Proyek</p>
            </div>

            @if (session('Success'))
                <div class="alert alert-success">
                    {{ session('Success') }}
                </div>
            @endif
            @if (session('Failed'))
                <div class="alert alert-danger">
                    {{ session('Failed') }}
                </div>
            @endif

            <form action="/login" method="post">
                @csrf

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
                        <input type="password" name="password" id="password" placeholder="Masukkan password anda">
                        <span class="toggle-password" id="togglePassword">
                            <i class="far fa-eye" id="toggleIcon"></i>
                        </span>
                    </div>
                    @error('password')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="remember-me">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember">Remember me</label>
                </div>

                <button type="submit" class="btn-login">Sign In</button>
            </form>

            <p class="register-link">
                Belum punya akun ? <a href="/register">Daftar disini</a>
            </p>
        </div>
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        });
    </script>
</body>

</html>
