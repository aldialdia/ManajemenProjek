<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Project Manager') }} - @yield('title', 'Welcome')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .guest-container {
            width: 100%;
            max-width: 440px;
        }

        .guest-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            padding: 3rem;
            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        .guest-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .guest-logo i {
            font-size: 3rem;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .guest-logo h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-top: 0.75rem;
        }

        .guest-logo p {
            color: #64748b;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-weight: 500;
            font-size: 0.875rem;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.875rem;
            transition: all 0.2s;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: #f97316;
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1);
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .input-icon-wrapper {
            position: relative;
        }

        .input-icon-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .input-icon-wrapper .form-control {
            padding-left: 2.75rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.875rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(249, 115, 22, 0.3);
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .form-check input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #f97316;
            cursor: pointer;
        }

        .form-check label {
            font-size: 0.875rem;
            color: #64748b;
            cursor: pointer;
        }

        .form-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }

        .form-footer p {
            color: #64748b;
            font-size: 0.875rem;
        }

        .form-footer a {
            color: #f97316;
            font-weight: 600;
            text-decoration: none;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        .form-link {
            color: #f97316;
            font-size: 0.875rem;
            text-decoration: none;
        }

        .form-link:hover {
            text-decoration: underline;
        }

        .error-message {
            color: #ef4444;
            font-size: 0.75rem;
            margin-top: 0.375rem;
        }

        .alert {
            padding: 0.875rem 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
    </style>
</head>

<body>
    <div class="guest-container">
        <div class="guest-card">
            <div class="guest-logo">
                <i class="fas fa-cubes"></i>
                <h1>{{ config('app.name', 'Project Manager') }}</h1>
                <p>@yield('subtitle', 'Manage your projects efficiently')</p>
            </div>

            @if(session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</body>

</html>