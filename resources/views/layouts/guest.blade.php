<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            :root {
                --color-primary: #7ce8ad;
                --color-secondary: #f9dff8;
                --color-dark: #1a1a1a;
                --color-light: #f8f9fa;
            }
            
            body {
                background: linear-gradient(135deg, #7ce8ad 0%, #5dd89f 50%, #f9dff8 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'figtree', sans-serif;
            }
            
            .login-container {
                width: 100%;
                max-width: 500px;
                padding: 20px;
            }
            
            .logo-container {
                text-align: center;
                margin-bottom: 40px;
                animation: fadeInDown 0.6s ease-out;
            }
            
            .logo-container img {
                max-width: 120px;
                height: auto;
                filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
            }
            
            .institution-name {
                margin-top: 12px;
                color: #fff;
                font-size: 18px;
                font-weight: 600;
                letter-spacing: 0.5px;
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
            
            .login-card {
                background: white;
                border-radius: 12px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
                overflow: hidden;
                animation: fadeInUp 0.6s ease-out;
            }
            
            .login-card-header {
                background: linear-gradient(90deg, #7ce8ad 0%, #5dd89f 100%);
                padding: 30px 20px;
                color: white;
                text-align: center;
            }
            
            .login-card-header h2 {
                font-size: 22px;
                font-weight: 600;
                margin: 0;
                letter-spacing: 0.3px;
            }
            
            .login-card-body {
                padding: 40px;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .form-label {
                display: block;
                margin-bottom: 8px;
                color: #333;
                font-weight: 500;
                font-size: 14px;
            }
            
            .form-input {
                width: 100%;
                padding: 12px 15px;
                border: 2px solid #e0e0e0;
                border-radius: 8px;
                font-size: 14px;
                transition: all 0.3s ease;
                font-family: inherit;
            }
            
            .form-input:focus {
                outline: none;
                border-color: #7ce8ad;
                box-shadow: 0 0 0 3px rgba(124, 232, 173, 0.1);
            }
            
            .form-input::placeholder {
                color: #999;
            }
            
            .remember-me-container {
                display: flex;
                align-items: center;
                margin-bottom: 25px;
                margin-top: 15px;
            }
            
            .remember-me-container input[type="checkbox"] {
                width: 18px;
                height: 18px;
                cursor: pointer;
                accent-color: #7ce8ad;
            }
            
            .remember-me-container label {
                margin-left: 8px;
                color: #666;
                font-size: 14px;
                cursor: pointer;
                user-select: none;
            }
            
            .login-button {
                width: 100%;
                padding: 12px 20px;
                background: linear-gradient(90deg, #7ce8ad 0%, #5dd89f 100%);
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            .login-button:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 16px rgba(124, 232, 173, 0.3);
            }
            
            .login-button:active {
                transform: translateY(0);
            }
            
            .forgot-password-link {
                text-align: center;
                margin-top: 15px;
            }
            
            .forgot-password-link a {
                color: #7ce8ad;
                text-decoration: none;
                font-size: 14px;
                font-weight: 500;
                transition: color 0.3s ease;
            }
            
            .forgot-password-link a:hover {
                color: #5dd89f;
                text-decoration: underline;
            }
            
            .eye-toggle-btn {
                position: absolute;
                right: 12px;
                top: 50%;
                transform: translateY(-50%);
                background: none;
                border: none;
                cursor: pointer;
                color: #666;
                padding: 5px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .eye-toggle-btn:hover {
                color: #7ce8ad;
            }
            
            .password-input-wrapper {
                position: relative;
            }
            
            @keyframes fadeInDown {
                from {
                    opacity: 0;
                    transform: translateY(-20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .error-message {
                color: #e74c3c;
                font-size: 13px;
                margin-top: 6px;
                padding: 8px 12px;
                background-color: #fadbd8;
                border-radius: 4px;
                border-left: 3px solid #e74c3c;
            }
            
            .status-message {
                background-color: #d4edda;
                color: #155724;
                padding: 12px 15px;
                border-radius: 8px;
                margin-bottom: 20px;
                border-left: 4px solid #7ce8ad;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="logo-container">
                <a href="/" wire:navigate>
                    <x-application-logo />
                </a>
                <div class="institution-name">Colbeef - Liberación de Canales</div>
            </div>

            <div class="login-card">
                <div class="login-card-header">
                    <h2>Acceso al Sistema</h2>
                </div>
                <div class="login-card-body">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
