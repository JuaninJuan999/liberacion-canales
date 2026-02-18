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
            /* Transiciones suaves para el sidebar */
            .sidebar-transition {
                transition: transform 0.3s ease-in-out;
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            <!-- Sidebar -->
            <aside id="sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen sidebar-transition bg-gray-800">
                <div class="h-full px-3 py-4 overflow-y-auto pb-24">
                    <!-- Logo/Título -->
                    <div class="mb-5 px-3">
                        <h2 class="text-xl font-bold text-white">Liberación de Canales</h2>
                        <p class="text-xs text-gray-400 mt-1">Sistema de Calidad</p>
                    </div>

                    <!-- Menú de Navegación -->
                    <ul class="space-y-2 font-medium">
                        <!-- Dashboard -->
                        <li>
                            <a href="{{ route('dashboard') }}" 
                               class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 {{ request()->routeIs('dashboard') && !request()->routeIs('dashboard.mensual') ? 'bg-gray-700' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                <span class="ml-3">Dashboard Diario</span>
                            </a>
                        </li>

                        <!-- Dashboard Mensual -->
                        <li>
                            <a href="{{ route('dashboard.mensual') }}" 
                               class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 {{ request()->routeIs('dashboard.mensual') ? 'bg-gray-700' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                <span class="ml-3">Dashboard Mensual</span>
                            </a>
                        </li>

                        <!-- Separador -->
                        <li class="pt-4 mt-4 border-t border-gray-700">
                            <p class="px-3 text-xs font-semibold text-gray-400 uppercase">Operaciones</p>
                        </li>

                        <!-- Hallazgos -->
                        <li>
                            <a href="{{ route('hallazgos.index') }}" 
                               class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 {{ request()->routeIs('hallazgos.*') ? 'bg-gray-700' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <span class="ml-3">Registro de Hallazgos</span>
                            </a>
                        </li>

                        <!-- Animales Procesados -->
                        <li>
                            <a href="{{ route('animales.index') }}" 
                               class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 {{ request()->routeIs('animales.*') ? 'bg-gray-700' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span class="ml-3">Animales Procesados</span>
                            </a>
                        </li>

                        <!-- Separador -->
                        <li class="pt-4 mt-4 border-t border-gray-700">
                            <p class="px-3 text-xs font-semibold text-gray-400 uppercase">Gestión</p>
                        </li>

                        <!-- Operarios -->
                        <li>
                            <a href="{{ route('operarios.index') }}" 
                               class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 {{ request()->routeIs('operarios.*') ? 'bg-gray-700' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <span class="ml-3">Operarios</span>
                            </a>
                        </li>

                        <!-- Asignación de Operarios -->
                        <li>
                            <a href="{{ route('operarios-dia.index') }}" 
                               class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 {{ request()->routeIs('operarios-dia.*') ? 'bg-gray-700' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="ml-3">Asignación por Día</span>
                            </a>
                        </li>

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

                    <!-- Usuario en la parte inferior -->
                    <div class="absolute bottom-4 left-0 right-0 px-3">
                        <div class="p-3 bg-gray-700 rounded-lg">
                            <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Botón para toggle sidebar en móvil -->
            <button id="sidebarToggle" 
                    class="fixed top-4 left-4 z-50 md:hidden p-2 text-gray-500 rounded-lg bg-white shadow-lg hover:bg-gray-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <!-- Contenido principal -->
            <div class="md:ml-64">
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
            </div>
        </div>

        <!-- Script para toggle del sidebar en móvil -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const sidebar = document.getElementById('sidebar');
                const sidebarToggle = document.getElementById('sidebarToggle');
                
                // Ocultar sidebar en móvil por defecto
                if (window.innerWidth < 768) {
                    sidebar.style.transform = 'translateX(-100%)';
                }
                
                sidebarToggle.addEventListener('click', function() {
                    if (sidebar.style.transform === 'translateX(-100%)') {
                        sidebar.style.transform = 'translateX(0)';
                    } else {
                        sidebar.style.transform = 'translateX(-100%)';
                    }
                });
                
                // Cerrar sidebar al hacer clic fuera en móvil
                document.addEventListener('click', function(event) {
                    if (window.innerWidth < 768) {
                        if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                            sidebar.style.transform = 'translateX(-100%)';
                        }
                    }
                });
            });
        </script>
    </body>
</html>
