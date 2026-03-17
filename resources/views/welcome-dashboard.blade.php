<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        {{-- Header de Bienvenida --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                <div class="flex items-center gap-3 sm:gap-4">
                    <img src="{{ asset('logo.png') }}" alt="Logo" class="h-6 sm:h-10 max-w-[40px] sm:max-w-[80px] object-contain flex-shrink-0">
                    <div class="min-w-0">
                        <h1 class="text-xl sm:text-3xl font-bold text-gray-900 truncate">¡Bienvenido! 👋</h1>
                        <p class="text-sm sm:text-base text-gray-500 mt-1 truncate">{{ auth()->user()->name }}, accede a los módulos desde aquí</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Contenido Principal --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            {{-- Grid de Módulos Principales (2 filas de 4) --}}
            <div class="mb-16">
                <h2 class="text-2xl font-bold text-gray-900 mb-8 flex items-center gap-2">
                    <div class="w-1 h-8 bg-blue-600 rounded"></div>
                    Módulos Disponibles
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    @foreach($modulos as $modulo)
                        @php
                            $iconMap = [
                                'clipboard-document-check' => '📋',
                                'document-text' => '📄',
                                'chart-bar' => '📊',
                                'calendar' => '📅',
                                'chart-pie' => '📈',
                                'presentation-chart-line' => '📊',
                                'users' => '👥',
                                'briefcase' => '💼',
                                'user-plus' => '➕',
                            ];
                            $emoji = $iconMap[$modulo->icono] ?? '📋';
                        @endphp
                        <a href="{{ $modulo->vista ? route($modulo->vista) : '#' }}" 
                           class="group bg-white rounded-xl shadow-sm hover:shadow-lg hover:border-blue-500 border border-gray-100 transition-all duration-300 p-6 cursor-pointer">
                            <div class="flex flex-col h-full">
                                <div class="text-4xl mb-4 group-hover:scale-110 transition-transform duration-300 inline-block">
                                    {{ $emoji }}
                                </div>
                                <h3 class="text-base font-bold text-gray-800 group-hover:text-blue-600 transition-colors mb-2 flex-grow">
                                    {{ $modulo->nombre }}
                                </h3>
                                <div class="flex items-center text-blue-600 text-sm opacity-0 group-hover:opacity-100 transition-all duration-300">
                                    <span>Ver</span>
                                    <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Accesos Rápidos --}}
            @if(auth()->check())
                <div class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-8 flex items-center gap-2">
                        <div class="w-1 h-8 bg-indigo-600 rounded"></div>
                        Accesos Rápidos
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <a href="{{ route('dashboard') }}" 
                           class="group bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 p-8 border border-gray-100 hover:border-blue-500">
                            <div class="flex items-center gap-4">
                                <div class="text-5xl">📊</div>
                                <div class="flex-grow">
                                    <h3 class="font-bold text-gray-900 text-lg group-hover:text-blue-600 transition-colors">Dashboard Diario</h3>
                                    <p class="text-gray-500 text-sm mt-1">Ver indicadores del día</p>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </a>

                        <a href="{{ route('dashboard.mensual') }}" 
                           class="group bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 p-8 border border-gray-100 hover:border-indigo-500">
                            <div class="flex items-center gap-4">
                                <div class="text-5xl">📈</div>
                                <div class="flex-grow">
                                    <h3 class="font-bold text-gray-900 text-lg group-hover:text-indigo-600 transition-colors">Dashboard Mensual</h3>
                                    <p class="text-gray-500 text-sm mt-1">Tendencias y análisis gráfico</p>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </a>

                        <a href="{{ route('hallazgos.registrar') }}" 
                           class="group bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 p-8 border border-gray-100 hover:border-red-500">
                            <div class="flex items-center gap-4">
                                <div class="text-5xl">⚠️</div>
                                <div class="flex-grow">
                                    <h3 class="font-bold text-gray-900 text-lg group-hover:text-red-600 transition-colors">Nuevo Hallazgo</h3>
                                    <p class="text-gray-500 text-sm mt-1">Registrar un nuevo hallazgo</p>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-red-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </a>
                    </div>
                </div>
            @endif

            {{-- Tiempo de Usabilidad (Solo Admin) --}}
            @if(strtoupper(auth()->user()->rol->nombre ?? '') === 'ADMINISTRADOR')
                <div class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-8 flex items-center gap-2">
                        <div class="w-1 h-8 bg-cyan-600 rounded"></div>
                        Tiempo de Usabilidad
                    </h2>
                    <a href="{{ route('tiempo-usabilidad') }}" 
                       class="group bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 p-8 border border-gray-100 hover:border-cyan-500 block">
                        <div class="flex items-center gap-4">
                            <div class="text-5xl">⏱️</div>
                            <div class="flex-grow">
                                <h3 class="font-bold text-gray-900 text-lg group-hover:text-cyan-600 transition-colors">Tiempo de Usabilidad</h3>
                                <p class="text-gray-500 text-sm mt-1">Controla el tiempo de uso del sistema por usuario con gráficas y estadísticas</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-cyan-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </a>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
