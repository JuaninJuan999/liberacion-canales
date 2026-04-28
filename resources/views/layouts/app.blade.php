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

                    <!-- Menú de navegación: mismo origen que la pantalla de inicio (menu_modulos + rol) -->
                    @php
                        use App\Models\MenuModulo;
                        use Illuminate\Support\Facades\Route;

                        $rolNav = Auth::user()->rolNormalizado();
                        $menuSidebar = MenuModulo::ordenado()->get()
                            ->filter(fn (MenuModulo $m) => $m->visibleParaRol($rolNav))
                            ->filter(fn (MenuModulo $m) => Route::has((string) $m->vista))
                            ->values();
                    @endphp
                    <ul class="space-y-2 font-medium">
                        <!-- SUIT PRINCIPAL (enlace externo; visible para todos los autenticados) -->
                        <li>
                            <a href="http://192.168.20.205:8000/site.html" class="flex items-center p-2 text-white rounded-lg hover:bg-blue-700">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" />
                                    <path stroke="currentColor" stroke-width="2" d="M8 12h8M12 8v8" />
                                </svg>
                                <span class="ml-3 font-bold">SUIT PRINCIPAL</span>
                            </a>
                        </li>

                        @forelse ($menuSidebar as $modulo)
                            @php
                                $esTc = str_contains((string) $modulo->vista, 'tolerancia-cero');
                                $activoNav = $modulo->esRutaActiva();
                                $hoverNav = $esTc ? 'hover:bg-red-700' : 'hover:bg-gray-700';
                                $bgNav = $activoNav ? ($esTc ? 'bg-red-700' : 'bg-gray-700') : '';
                            @endphp
                            <li>
                                <a href="{{ route($modulo->vista) }}"
                                   class="flex items-center p-2 text-white rounded-lg {{ $hoverNav }} {{ $bgNav }}">
                                    <x-sidebar-menu-icon :name="$modulo->icono ?: 'clipboard-document-check'" />
                                    <span class="ml-3">{{ $modulo->nombre }}</span>
                                </a>
                            </li>
                        @empty
                            <li class="px-3 py-2 text-xs text-gray-400 rounded-lg bg-gray-700/40">
                                No hay módulos habilitados para tu rol.
                            </li>
                        @endforelse

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
                const STORAGE_KEY = 'liberacion_canales_sidebar_desktop_open';
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebarOverlay');
                const mainContent = document.getElementById('mainContent');
                const openBtn = document.getElementById('sidebarToggleBtn');
                const closeBtn = document.getElementById('sidebarCloseBtn');
                let abierto = true;

                function esMobil() { return window.innerWidth < 768; }

                function persistirPreferenciaEscritorio() {
                    if (esMobil()) return;
                    try {
                        localStorage.setItem(STORAGE_KEY, abierto ? '1' : '0');
                    } catch (e) { /* ignorar */ }
                }

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

                // En móvil empieza cerrado (sin leer localStorage)
                if (esMobil()) {
                    abierto = false;
                    sidebar.classList.remove('translate-x-0');
                    sidebar.classList.add('-translate-x-full');
                } else {
                    // Escritorio: restaurar tras recarga / auto-refresh (p. ej. dashboard mensual)
                    try {
                        if (localStorage.getItem(STORAGE_KEY) === '0') {
                            abierto = false;
                            sidebar.classList.remove('translate-x-0');
                            sidebar.classList.add('-translate-x-full');
                        }
                    } catch (e) { /* ignorar */ }
                }
                actualizarUI();

                function abrir() {
                    abierto = true;
                    sidebar.classList.add('translate-x-0');
                    sidebar.classList.remove('-translate-x-full');
                    actualizarUI();
                    persistirPreferenciaEscritorio();
                }

                function cerrar() {
                    abierto = false;
                    sidebar.classList.remove('translate-x-0');
                    sidebar.classList.add('-translate-x-full');
                    actualizarUI();
                    persistirPreferenciaEscritorio();
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
