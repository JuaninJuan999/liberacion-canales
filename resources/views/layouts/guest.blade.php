<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('vaca.png') }}">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            *, *::before, *::after {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            :root {
                --color-primary: #10b981;
                --color-primary-light: #34d399;
                --color-primary-dark: #059669;
                --color-accent: #6ee7b7;
                --color-dark: #0f172a;
                --color-dark-soft: #1e293b;
                --color-gray: #64748b;
                --color-gray-light: #94a3b8;
                --color-light: #f1f5f9;
                --color-white: #ffffff;
                --color-danger: #ef4444;
                --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
                --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1);
                --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
                --shadow-xl: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
            }

            body {
                min-height: 100vh;
                display: flex;
                font-family: 'Inter', sans-serif;
                background: var(--color-dark);
                overflow: hidden;
            }

            html {
                overflow: hidden;
                height: 100%;
            }

            /* ── Panel lateral decorativo ── */
            .login-brand-panel {
                display: none;
                width: 50%;
                position: relative;
                background: linear-gradient(160deg, #059669 0%, #10b981 40%, #34d399 100%);
                overflow: hidden;
            }

            .login-brand-panel::before {
                content: '';
                position: absolute;
                inset: 0;
                background:
                    radial-gradient(circle at 20% 80%, rgba(255,255,255,0.08) 0%, transparent 50%),
                    radial-gradient(circle at 80% 20%, rgba(255,255,255,0.06) 0%, transparent 50%);
            }

            .brand-pattern {
                position: absolute;
                inset: 0;
                opacity: 0.06;
                background-image: 
                    linear-gradient(30deg, #fff 12%, transparent 12.5%, transparent 87%, #fff 87.5%, #fff),
                    linear-gradient(150deg, #fff 12%, transparent 12.5%, transparent 87%, #fff 87.5%, #fff),
                    linear-gradient(30deg, #fff 12%, transparent 12.5%, transparent 87%, #fff 87.5%, #fff),
                    linear-gradient(150deg, #fff 12%, transparent 12.5%, transparent 87%, #fff 87.5%, #fff),
                    linear-gradient(60deg, rgba(255,255,255,0.6) 25%, transparent 25.5%, transparent 75%, rgba(255,255,255,0.6) 75%),
                    linear-gradient(60deg, rgba(255,255,255,0.6) 25%, transparent 25.5%, transparent 75%, rgba(255,255,255,0.6) 75%);
                background-size: 80px 140px;
                background-position: 0 0, 0 0, 40px 70px, 40px 70px, 0 0, 40px 70px;
            }

            .brand-content {
                position: relative;
                z-index: 1;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                height: 100%;
                padding: 60px;
                text-align: center;
                color: white;
            }

            .brand-logo {
                width: 110px;
                height: 110px;
                border-radius: 28px;
                background: rgba(255,255,255,0.15);
                backdrop-filter: blur(10px);
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 32px;
                animation: floatIn 0.8s ease-out;
                border: 1px solid rgba(255,255,255,0.2);
            }

            .brand-logo img {
                max-width: 70px;
                height: auto;
                filter: brightness(0) invert(1) drop-shadow(0 2px 4px rgba(0,0,0,0.1));
            }

            .brand-title {
                font-size: 28px;
                font-weight: 700;
                letter-spacing: -0.5px;
                margin-bottom: 12px;
                animation: floatIn 0.8s ease-out 0.1s both;
            }

            .brand-subtitle {
                font-size: 16px;
                font-weight: 400;
                opacity: 0.85;
                line-height: 1.6;
                max-width: 320px;
                animation: floatIn 0.8s ease-out 0.2s both;
            }

            .brand-features {
                margin-top: 48px;
                display: flex;
                flex-direction: column;
                gap: 16px;
                animation: floatIn 0.8s ease-out 0.3s both;
            }

            .brand-feature {
                display: flex;
                align-items: center;
                gap: 12px;
                font-size: 14px;
                opacity: 0.9;
            }

            .brand-feature-icon {
                width: 36px;
                height: 36px;
                border-radius: 10px;
                background: rgba(255,255,255,0.15);
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }

            .brand-feature-icon svg {
                width: 18px;
                height: 18px;
            }

            /* ── Panel de formulario ── */
            .login-form-panel {
                width: 100%;
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                background: var(--color-dark);
                position: relative;
                overflow-y: none;
            }

            .login-form-panel::before {
                content: '';
                position: absolute;
                top: -50%;
                right: -20%;
                width: 600px;
                height: 600px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(16,185,129,0.08) 0%, transparent 70%);
                pointer-events: none;
            }

            .login-container {
                width: 100%;
                max-width: 420px;
                padding: 20px 24px;
                position: relative;
                z-index: 1;
            }

            .login-header {
                text-align: center;
                margin-bottom: 24px;
                animation: slideUp 0.6s ease-out;
            }

            .login-header-logo {
                width: 110px;
                height: 110px;
                border-radius: 26px;
                background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 16px;
                box-shadow: 0 8px 24px rgba(16,185,129,0.3);
            }

            .login-header-logo img {
                max-width: 70px;
                height: auto;
                filter: brightness(0) invert(1);
            }

            .login-header h1 {
                font-size: 22px;
                font-weight: 700;
                color: var(--color-white);
                letter-spacing: -0.5px;
                margin-bottom: 4px;
            }

            .login-header p {
                font-size: 14px;
                color: var(--color-gray-light);
                font-weight: 400;
            }

            /* Móvil: mostrar nombre de la app debajo del logo */
            .mobile-brand-name {
                display: block;
                font-size: 11px;
                color: var(--color-primary-light);
                font-weight: 500;
                letter-spacing: 1.5px;
                text-transform: uppercase;
                margin-bottom: 12px;
            }

            .login-card {
                background: var(--color-dark-soft);
                border-radius: 20px;
                border: 1px solid rgba(255,255,255,0.06);
                overflow: hidden;
                animation: slideUp 0.6s ease-out 0.1s both;
                box-shadow: var(--shadow-xl);
            }

            .login-card-body {
                padding: 28px 28px;
            }

            .form-group {
                margin-bottom: 16px;
            }

            .form-label {
                display: block;
                margin-bottom: 8px;
                color: var(--color-gray-light);
                font-weight: 500;
                font-size: 13px;
                letter-spacing: 0.3px;
            }

            .form-label svg {
                display: none;
            }

            .form-input-wrapper {
                position: relative;
            }

            .form-input-icon {
                position: absolute;
                left: 14px;
                top: 50%;
                transform: translateY(-50%);
                color: var(--color-gray);
                pointer-events: none;
                transition: color 0.2s ease;
            }

            .form-input-icon svg {
                width: 18px;
                height: 18px;
            }

            .form-input {
                width: 100%;
                padding: 11px 14px 11px 42px;
                background: rgba(255,255,255,0.04);
                border: 1.5px solid rgba(255,255,255,0.08);
                border-radius: 10px;
                font-size: 14px;
                color: var(--color-white);
                transition: all 0.25s ease;
                font-family: inherit;
            }

            .form-input:focus {
                outline: none;
                border-color: var(--color-primary);
                background: rgba(16,185,129,0.04);
                box-shadow: 0 0 0 3px rgba(16,185,129,0.12);
            }

            .form-input:focus ~ .form-input-icon,
            .form-input:focus + .form-input-icon {
                color: var(--color-primary-light);
            }

            .form-input::placeholder {
                color: var(--color-gray);
            }

            .remember-me-container {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 20px;
                margin-top: 2px;
            }

            .remember-me-left {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .remember-me-container input[type="checkbox"] {
                width: 18px;
                height: 18px;
                cursor: pointer;
                accent-color: var(--color-primary);
                border-radius: 4px;
            }

            .remember-me-container label {
                color: var(--color-gray-light);
                font-size: 13px;
                cursor: pointer;
                user-select: none;
            }

            .login-button {
                width: 100%;
                padding: 12px 24px;
                background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
                color: white;
                border: none;
                border-radius: 12px;
                font-size: 15px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                letter-spacing: 0.3px;
                position: relative;
                overflow: hidden;
            }

            .login-button::before {
                content: '';
                position: absolute;
                inset: 0;
                background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 100%);
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .login-button:hover {
                transform: translateY(-1px);
                box-shadow: 0 8px 24px rgba(16,185,129,0.35);
            }

            .login-button:hover::before {
                opacity: 1;
            }

            .login-button:active {
                transform: translateY(0);
            }

            .forgot-password-link {
                text-align: center;
                margin-top: 16px;
            }

            .forgot-password-link a {
                color: var(--color-gray-light);
                text-decoration: none;
                font-size: 13px;
                font-weight: 500;
                transition: color 0.25s ease;
            }

            .forgot-password-link a:hover {
                color: var(--color-primary-light);
            }

            .eye-toggle-btn {
                position: absolute;
                right: 12px;
                top: 50%;
                transform: translateY(-50%);
                background: none;
                border: none;
                cursor: pointer;
                color: var(--color-gray);
                padding: 5px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: color 0.2s ease;
            }

            .eye-toggle-btn:hover {
                color: var(--color-primary-light);
            }

            .password-input-wrapper {
                position: relative;
            }

            .password-input-wrapper .form-input {
                padding-right: 44px;
            }

            .login-footer {
                text-align: center;
                margin-top: 20px;
                animation: slideUp 0.6s ease-out 0.2s both;
            }

            .login-footer p {
                font-size: 13px;
                color: var(--color-gray);
            }

            .login-footer a {
                color: var(--color-primary-light);
                text-decoration: none;
                font-weight: 600;
                transition: color 0.2s ease;
            }

            .login-footer a:hover {
                color: var(--color-primary);
            }

            .divider {
                display: flex;
                align-items: center;
                gap: 16px;
                margin: 16px 0;
            }

            .divider::before, .divider::after {
                content: '';
                flex: 1;
                height: 1px;
                background: rgba(255,255,255,0.06);
            }

            .divider span {
                font-size: 12px;
                color: var(--color-gray);
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            /* ── Animaciones ── */
            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(24px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes floatIn {
                from {
                    opacity: 0;
                    transform: translateY(16px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .error-message {
                color: #fca5a5;
                font-size: 13px;
                margin-top: 8px;
                padding: 10px 14px;
                background: rgba(239,68,68,0.1);
                border-radius: 8px;
                border-left: 3px solid var(--color-danger);
            }

            .status-message {
                background: rgba(16,185,129,0.1);
                color: var(--color-primary-light);
                padding: 12px 16px;
                border-radius: 10px;
                margin-bottom: 20px;
                border-left: 3px solid var(--color-primary);
                font-size: 14px;
            }

            /* ── Responsive ── */
            @media (min-width: 1024px) {
                .login-brand-panel {
                    display: flex;
                }
                .login-form-panel {
                    width: 50%;
                }
                .mobile-brand-name {
                    display: none;
                }
                .login-container {
                    padding: 20px 48px;
                }
            }

            @media (max-height: 700px) {
                .login-header {
                    margin-bottom: 16px;
                }
                .login-header-logo {
                    width: 90px;
                    height: 90px;
                    margin-bottom: 12px;
                }
                .login-header-logo img {
                    max-width: 56px;
                }
                .login-header h1 {
                    font-size: 20px;
                }
                .login-card-body {
                    padding: 22px 24px;
                }
                .form-group {
                    margin-bottom: 12px;
                }
                .form-input {
                    padding: 9px 14px 9px 42px;
                }
                .login-footer {
                    margin-top: 14px;
                }
                .brand-logo {
                    width: 80px;
                    height: 80px;
                    margin-bottom: 20px;
                }
                .brand-logo img {
                    max-width: 50px;
                }
                .brand-title {
                    font-size: 24px;
                }
                .brand-features {
                    margin-top: 28px;
                }
            }

            @media (max-width: 480px) {
                .login-container {
                    padding: 16px 16px;
                }
                .login-card-body {
                    padding: 24px 20px;
                }
            }
        </style>
    </head>
    <body>
                <!-- Botón SUIT PRINCIPAL en la parte superior izquierda -->
                <a href="http://192.168.20.177:8000/site.html" target="_blank" rel="noopener" style="
                    position: fixed;
                    top: 18px;
                    left: 18px;
                    z-index: 1000;
                    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
                    color: #fff;
                    padding: 10px 18px;
                    border-radius: 8px;
                    font-weight: bold;
                    font-size: 15px;
                    box-shadow: 0 2px 8px rgba(16,185,129,0.18);
                    text-decoration: none;
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    transition: background 0.2s;
                " onmouseover="this.style.background='#13bd7f'" onmouseout="this.style.background='linear-gradient(135deg, #0eeea7 0%, #c93131 100%)'">
                    <svg style="width:20px;height:20px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" /><path stroke="currentColor" stroke-width="2" d="M8 12h8M12 8v8" /></svg>
                    SUIT PRINCIPAL
                </a>
        <!-- Panel lateral decorativo (visible solo en desktop) -->
        <div class="login-brand-panel">
        <div class="brand-pattern"></div>
            <div class="brand-content">
                <div class="brand-logo">
                    <img src="/logo.png" alt="Logo">
                </div>
                <div class="brand-title">Sistema de Liberación de Canales </div>
                <div class="brand-subtitle">Control de calidad en tiempo real</div>
                <div class="brand-features">
                    <div class="brand-feature">
                        <div class="brand-feature-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span>Indicadores de calidad diarios y mensuales</span>
                    </div>
                    <div class="brand-feature">
                        <div class="brand-feature-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <span>Registro y seguimiento en tiempo real</span>
                    </div>
                    <div class="brand-feature">
                        <div class="brand-feature-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <span>Dashboards y gráficos interactivos</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel de formulario -->
        <div class="login-form-panel">
            <div class="login-container">
                <div class="login-header">
                    <div class="login-header-logo">
                        <img src="/logo.png" alt="Logo">
                    </div>
                    <span class="mobile-brand-name">Colbeef</span>
                    <h1>Bienvenido</h1>
                    <p>Ingresa tus credenciales para acceder al sistema</p>
                </div>

                <div class="login-card">
                    <div class="login-card-body">
                        {{ $slot }}
                    </div>
                </div>

                <div class="login-footer">
                    <p>&copy; {{ date('Y') }} Colbeef &mdash; Liberación de Canales</p>
                    <p>Desarrollado por "<span style="color: var(--color-primary-light);">Juan Pablo Carreño</span>"</p>
                </div>
            </div>
        </div>
    </body>
</html>
