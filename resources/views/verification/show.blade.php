<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masukkan Kode OTP - Sistem Manajemen Proyek</title>

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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .verification-card {
            background: #fff;
            border-radius: 16px;
            padding: 30px;
            max-width: 380px;
            width: 100%;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .icon-wrapper {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #f7941d 0%, #ff6b35 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 8px 20px rgba(247, 148, 29, 0.3);
        }

        .icon-wrapper i {
            font-size: 26px;
            color: #fff;
        }

        h1 {
            font-size: 20px;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #666;
            font-size: 12px;
            margin-bottom: 18px;
            line-height: 1.6;
        }

        .alert {
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 12px;
            text-align: left;
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

        .otp-input-wrapper {
            margin-bottom: 15px;
        }

        .otp-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 20px;
            font-weight: 600;
            text-align: center;
            letter-spacing: 8px;
            font-family: 'Poppins', sans-serif;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .otp-input:focus {
            border-color: #f7941d;
        }

        .otp-input::placeholder {
            letter-spacing: 2px;
            font-size: 12px;
            color: #aaa;
        }

        .btn-verify {
            width: 100%;
            padding: 10px 20px;
            background: linear-gradient(135deg, #f7941d 0%, #ff6b35 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(247, 148, 29, 0.4);
        }

        .btn-verify:active {
            transform: translateY(0);
        }

        .btn-verify i {
            font-size: 14px;
        }

        .resend-section {
            margin-top: 18px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .resend-text {
            color: #666;
            font-size: 11px;
            margin-bottom: 8px;
        }

        .btn-resend {
            background: none;
            border: 1px solid #f7941d;
            color: #f7941d;
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-resend:hover {
            background: #f7941d;
            color: #fff;
        }

        .back-link {
            margin-top: 20px;
        }

        .back-link a {
            color: #999;
            font-size: 13px;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
            color: #f7941d;
        }

        @error('otp')
            .otp-input {
                border-color: #dc3545;
            }

        @enderror .error-text {
            color: #dc3545;
            font-size: 12px;
            margin-top: 8px;
        }
    </style>
</head>

<body>
    <div class="verification-card">
        <div class="icon-wrapper">
            <i class="fas fa-shield-alt"></i>
        </div>

        <h1>Masukkan Kode OTP</h1>
        <p class="subtitle">Kami telah mengirim kode verifikasi ke email Anda. Masukkan kode tersebut di bawah ini.</p>

        @if (session('Success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('Success') }}
            </div>
        @endif
        @if (session('Failed'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> {{ session('Failed') }}
            </div>
        @endif

        <form action="/verify/{{ $unique_id }}" method="post">
            @method('put')
            @csrf

            <div class="otp-input-wrapper">
                <input type="text" name="otp" class="otp-input" placeholder="Masukkan OTP" maxlength="6"
                    autocomplete="off">
                @error('otp')
                    <p class="error-text">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-verify">
                <i class="fas fa-check"></i>
                Verifikasi
            </button>
        </form>

        <div class="resend-section">
            <p class="resend-text">Tidak menerima kode?</p>
            <form action="/verify" method="post" style="display: inline;">
                @csrf
                <input type="hidden" value="resend" name="type">
                <button type="submit" class="btn-resend">
                    <i class="fas fa-redo"></i> Kirim Ulang OTP
                </button>
            </form>
        </div>

        <div class="back-link">
            <a href="/verify"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </div>

    <script>
        // Auto focus and format OTP input
        const otpInput = document.querySelector('.otp-input');
        otpInput.addEventListener('input', function (e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>

</html>