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

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('vaca.png') }}">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            /* Transiciones suaves para el sidebar */
            .sidebar-transition {
                transition: transform 0.3s ease-in-out;
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            <!-- Sidebar Toggle Button (oculto por defecto, el JS lo muestra al cerrar sidebar) -->
            <button id="sidebarToggleBtn"
                    class="fixed top-4 left-4 z-50 p-2 text-gray-700 bg-white rounded-lg shadow-lg hover:bg-gray-100 cursor-pointer"
                    style="transition: opacity 0.2s ease; display: none;">
                <svg class="w-6 h-6" id="toggleIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <!-- Sidebar -->
            <div id="sidebarOverlay" class="fixed inset-0 z-30 bg-black/50 hidden" style="transition: opacity 0.3s ease;"></div>
            <aside id="sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen sidebar-transition bg-gray-800 translate-x-0">
                <div class="h-full px-3 py-4 overflow-y-auto">
                    <!-- Logo/Título + botón cerrar sidebar -->
                    <div class="mb-5 px-3 flex items-center justify-between">
                        <a href="{{ route('home') }}" class="block hover:opacity-80 transition-opacity">
                            <h2 class="text-xl font-bold text-white">Liberación de Canales</h2>
                            <p class="text-xs text-gray-400 mt-1">Sistema de Calidad</p>
                        </a>
                        <button id="sidebarCloseBtn" class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg cursor-pointer" title="Ocultar menú">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Menú de Navegación -->
                    @php
                        $normalizarRol = function ($rol) {
                            $rolNormalizado = strtoupper(trim((string) $rol));
                            return $rolNormalizado === 'ADMIN' ? 'ADMINISTRADOR' : $rolNormalizado;
                        };
                        $rolActual = $normalizarRol(Auth::user()?->rol?->nombre);
                        $puedeVer = function (array $rolesPermitidos) use ($rolActual, $normalizarRol) {
                            $rolesNormalizados = array_map($normalizarRol, $rolesPermitidos);
                            return in_array($rolActual, $rolesNormalizados, true);
                        };
                    @endphp
                    <ul class="space-y-2 font-medium">
                                                <!-- SUIT PRINCIPAL -->
                                                <li>
                                                    <a href="http://192.168.20.205:8000/site.html" class="flex items-center p-2 text-white rounded-lg hover:bg-blue-700">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" />
                                                            <path stroke="currentColor" stroke-width="2" d="M8 12h8M12 8v8" />
                                                        </svg>
                                                        <span class="ml-3 font-bold">SUIT PRINCIPAL</span>
                                                    </a>
                                                </li>
                        <!-- Dashboard -->
                        @if($puedeVer(['Admin', 'Operaciones', 'Calidad', 'Gerencia']))
                        <li>
                            <a href="{{ route('dashboard') }}" 
                               class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 {{ request()->routeIs('dashboard') && !request()->routeIs('dashboard.mensual') ? 'bg-gray-700' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                <span class="ml-3">Dashboard Diario</span>
                            </a>
                        </li>
                        @endif

                        <!-- Dashboard Mensual -->
                        @if($puedeVer(['Admin', 'Operaciones', 'Calidad', 'Gerencia']))
                        <li>
                            <a href="{{ route('dashboard.mensual') }}" 
                               class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 {{ request()->routeIs('dashboard.mensual') ? 'bg-gray-700' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                <span class="ml-3">Dashboard Mensual</span>
                            </a>
                        </li>
                        @endif

                        <!-- Indicador Diario -->
                        @if($puedeVer(['Admin', 'Operaciones', 'Calidad', 'Gerencia']))
                        <li>
                            <a href="{{ route('indicadores.detalle-dia') }}" 
                               class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 {{ request()->routeIs('indicadores.detalle-dia') ? 'bg-gray-700' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                <span class="ml-3">Indicador Diario</span>
                            </a>
                        </li>
                        @endif

                        <!-- Separador -->
                        <li class="pt-4 mt-4 border-t border-gray-700">
                            <p class="px-3 text-xs font-semibold text-gray-400 uppercase">Operaciones</p>
                        </li>

                        <!-- Registro de Hallazgos -->
                        @if($puedeVer(['Admin', 'Calidad']))
                        <li>
                            <a href="{{ route('hallazgos.registrar') }}" 
                               class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 {{ request()->routeIs('hallazgos.registrar') ? 'bg-gray-700' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <span class="ml-3">Registro de Hallazgos</span>
                            </a>
                        </li>
                        @endif
                        
                        <!-- Historial de Registros -->
                        @if($puedeVer(['Admin', 'Operaciones', 'Calidad', 'Gerencia']))
                        <li>
                            <a href="{{ route('hallazgos.historial') }}" 
                               class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 {{ request()->routeIs('hallazgos.historial') ? 'bg-gray-700' : '' }}">
                                <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                </svg>
                                <span class="ml-3">Historial de Registros</span>
                            </a>
                        </li>
                        @endif

                        <!-- Registro Tolerancia Cero -->
                        @if($puedeVer(['Admin', 'Operaciones']))
                        <li>
                            <a href="{{ route('tolerancia-cero.registrar') }}" 
                               class="flex items-center p-2 text-white rounded-lg hover:bg-red-700 {{ request()->routeIs('tolerancia-cero.registrar') ? 'bg-red-700' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <span class="ml-3">Hallazgos Tolerancia Cero</span>
                            </a>
                        </li>
                        @endif

                        <!-- Historial Tolerancia Cero -->
                        @if($puedeVer(['Admin', 'Operaciones', 'Calidad', 'Gerencia']))
                        <li>
                            <a href="{{ route('tolerancia-cero.historial') }}" 
                               class="flex items-center p-2 text-white rounded-lg hover:bg-red-700 {{ request()->routeIs('tolerancia-cero.historial') ? 'bg-red-700' : '' }}">
                                <svg class="w-6 h-6 text-gray-300" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                </svg>
                                <span class="ml-3">Historial Registros TC</span>
                            </a>
                        </li>
                        @endif

                        <!-- Animales Procesados -->
                        @if($puedeVer(['Admin', 'Operaciones', 'Calidad', 'Gerencia']))
                        <li>
                            <a href="{{ route('animales.index') }}" 
                               class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 {{ request()->routeIs('animales.*') ? 'bg-gray-700' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span class="ml-3">Animales Procesados</span>
                            </a>
                        </li>
                        @endif

                        <!-- Separador -->
                        <li class="pt-4 mt-4 border-t border-gray-700">
                            <p class="px-3 text-xs font-semibold text-gray-400 uppercase">Gestión</p>
                        </li>

                        <!-- Operarios -->
                        @if($puedeVer(['Admin', 'Operaciones']))
                        <li>
                            <a href="{{ route('operarios.index') }}" 
                               class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 {{ request()->routeIs('operarios.*') ? 'bg-gray-700' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <span class="ml-3">Operarios</span>
                            </a>
                        </li>
                        @endif

                        <!-- Asignación de Operarios -->
                        @if($puedeVer(['Admin', 'Operaciones']))
                        <li>
                            <a href="{{ route('operarios-dia.index') }}" 
                               class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 {{ request()->routeIs('operarios-dia.*') ? 'bg-gray-700' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="ml-3">Asignación por Día</span>
                            </a>
                        </li>
                        @endif

                        <!-- Puestos de Trabajo -->
                        @if($puedeVer(['Admin']))
                        <li>
                            <a href="{{ route('puestos_trabajo.index') }}" 
                               class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 {{ request()->routeIs('puestos_trabajo.*') ? 'bg-gray-700' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                <span class="ml-3">Puestos de Trabajo</span>
                            </a>
                        </li>
                        @endif

                        <!-- Gestión de Usuarios -->
                        @if($puedeVer(['Admin']))
                        <li>
                            <a href="{{ route('usuarios.gestion') }}" 
                               class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 {{ request()->routeIs('usuarios.*') ? 'bg-gray-700' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <span class="ml-3">Gestión de Usuarios</span>
                            </a>
                        </li>
                        @endif

                        <!-- Tiempo de Usabilidad -->
                        @if($puedeVer(['Admin']))
                        <li>
                            <a href="{{ route('tiempo-usabilidad') }}" 
                               class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 {{ request()->routeIs('tiempo-usabilidad') ? 'bg-gray-700' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="ml-3">Tiempo de Usabilidad</span>
                            </a>
                        </li>
                        @endif

                        <!-- Separador -->
                        <li class="pt-4 mt-4 border-t border-gray-700">
                            <p class="px-3 text-xs font-semibold text-gray-400 uppercase">Cuenta</p>
                        </li>

                        <!-- Perfil -->
                        <li>
                            <a href="{{ route('profile') }}" 
                               class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 {{ request()->routeIs('profile') ? 'bg-gray-700' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span class="ml-3">Mi Perfil</span>
                            </a>
                        </li>
                    </ul>

                    <!-- Usuario y Logout en la parte inferior -->
                    <div class="mt-6 px-1 space-y-3 pb-4 border-t border-gray-700 pt-4">
                        <div class="p-3 bg-gray-700 rounded-lg">
                            <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ Auth::user()->email }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center justify-center p-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200 font-semibold">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Cerrar Sesión
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <!-- Contenido principal -->
            <div id="mainContent" class="md:ml-64 transition-all duration-300">
                <!-- Page Heading -->
                @if (isset($header))
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <!-- Page Content -->
                <main>
                    {{ $slot }}
                </main>

                <!-- Footer de derechos de autor -->
                <footer style="text-align: center; padding: 12px 16px; border-top: 1px solid #e5e7eb; margin-top: 8px; color: #9ca3af; font-size: 12px; font-family: inherit;">
                    <span>&copy; {{ date('Y') }} Colbeef &mdash; Liberación de Canales</span>
                    <span style="display: block; margin-top: 2px;">
                        Desarrollado por "<span style="color: #10b981; font-weight: 600;">Juan Pablo Carreño</span>"
                    </span>
                </footer>
            </div>
        </div>

        <!-- Script unificado para sidebar toggle -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebarOverlay');
                const mainContent = document.getElementById('mainContent');
                const openBtn = document.getElementById('sidebarToggleBtn');
                const closeBtn = document.getElementById('sidebarCloseBtn');
                let abierto = true;

                function esMobil() { return window.innerWidth < 768; }

                function actualizarUI() {
                    openBtn.style.display = abierto ? 'none' : 'block';
                    if (esMobil()) {
                        overlay.classList.toggle('hidden', !abierto);
                        mainContent.style.paddingLeft = '';
                        mainContent.classList.remove('md:ml-0');
                        mainContent.classList.add('md:ml-64');
                    } else {
                        overlay.classList.add('hidden');
                        if (abierto) {
                            mainContent.classList.add('md:ml-64');
                            mainContent.classList.remove('md:ml-0');
                            mainContent.style.paddingLeft = '';
                        } else {
                            mainContent.classList.remove('md:ml-64');
                            mainContent.classList.add('md:ml-0');
                            mainContent.style.paddingLeft = '3.5rem';
                        }
                    }
                }

                // En móvil empieza cerrado
                if (esMobil()) {
                    abierto = false;
                    sidebar.classList.remove('translate-x-0');
                    sidebar.classList.add('-translate-x-full');
                }
                actualizarUI();

                function abrir() {
                    abierto = true;
                    sidebar.classList.add('translate-x-0');
                    sidebar.classList.remove('-translate-x-full');
                    actualizarUI();
                }

                function cerrar() {
                    abierto = false;
                    sidebar.classList.remove('translate-x-0');
                    sidebar.classList.add('-translate-x-full');
                    actualizarUI();
                }

                openBtn.addEventListener('click', abrir);
                closeBtn.addEventListener('click', cerrar);
                overlay.addEventListener('click', cerrar);

                // Cerrar al hacer clic fuera en móvil
                document.addEventListener('click', function(e) {
                    if (esMobil() && abierto) {
                        if (!sidebar.contains(e.target) && !openBtn.contains(e.target)) {
                            cerrar();
                        }
                    }
                });
            });
        </script>
        @stack('scripts')
    </body>
</html>
