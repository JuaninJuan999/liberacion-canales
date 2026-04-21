<x-app-layout>
    {{--
        Fondo oscuro + colores institucionales (#f9dff8 / #7ce8ad).
        Incluye estilos inline en el contenedor y en los títulos para que el contraste sea correcto
        aunque el CSS de Tailwind no esté recompilado o haya caché del navegador.
    --}}
    <div
        class="relative w-full min-h-screen overflow-hidden"
        style="min-height:100vh;width:100%;background-color:#0c1222;background-image:radial-gradient(ellipse 80% 50% at 100% 0%, rgba(249,223,248,0.18), transparent 55%), radial-gradient(ellipse 70% 45% at 0% 100%, rgba(124,232,173,0.16), transparent 50%);"
    >
        {{-- Halos decorativos (Tailwind; refuerzo visual si el build está al día) --}}
        <div class="pointer-events-none absolute -right-24 -top-32 h-[22rem] w-[22rem] rounded-full bg-[#f9dff8]/25 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-28 -left-20 h-[20rem] w-[20rem] rounded-full bg-[#7ce8ad]/20 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute left-1/2 top-1/3 h-64 w-64 -translate-x-1/2 rounded-full bg-[#f9dff8]/10 blur-3xl" aria-hidden="true"></div>

        <div class="relative z-10">
            {{-- Header de Bienvenida --}}
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
                <div class="overflow-hidden rounded-xl border border-white/10 bg-white/95 shadow-xl shadow-black/30 backdrop-blur-sm ring-1 ring-[#f9dff8]/30">
                    <div class="h-1.5 bg-gradient-to-r from-[#f9dff8] via-white to-[#7ce8ad]"></div>
                    <div class="p-4 sm:p-6">
                        <div class="flex items-center gap-3 sm:gap-4">
                            <img src="{{ asset('logo.png') }}" alt="Logo" class="h-6 sm:h-10 max-w-[40px] sm:max-w-[80px] object-contain flex-shrink-0">
                            <div class="min-w-0">
                                <h1 class="text-xl sm:text-3xl font-bold text-slate-900 truncate">¡Bienvenido! 👋</h1>
                                <p class="text-sm sm:text-base text-slate-600 mt-1 truncate">{{ auth()->user()->name }}, accede a los módulos de tu perfil desde aquí.</p>
                                @if(!empty($rolUsuario))
                                    <p class="text-xs font-semibold text-emerald-800 mt-2 inline-flex items-center gap-1.5 rounded-md bg-[#7ce8ad]/35 px-2 py-1 border border-emerald-800/10">
                                        <span class="opacity-80">Rol:</span> {{ $rolUsuario }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contenido Principal --}}
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-12">

                {{-- Grid de Módulos Principales --}}
                <div class="mb-16">
                    <h2
                        class="mb-8 flex items-center gap-3 rounded-xl px-4 py-3 text-xl sm:text-2xl font-bold shadow-lg border border-slate-900/10"
                        style="background: linear-gradient(90deg, #f9dff8 0%, #7ce8ad 100%); color: #0f172a;"
                    >
                        <span class="h-8 w-1.5 shrink-0 rounded-full bg-slate-900/80" aria-hidden="true"></span>
                        Módulos Disponibles
                    </h2>
                    @if($modulos->isEmpty())
                        <div class="rounded-xl border border-white/15 bg-white/5 px-4 py-8 text-center text-slate-200">
                            <p class="font-medium">No hay módulos asignados a tu rol actual.</p>
                            <p class="text-sm text-slate-400 mt-2">Si necesitas acceso a algún módulo, contacta al administrador.</p>
                        </div>
                    @else
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
                                    'exclamation-triangle' => '⚠️',
                                    'clock' => '🕐',
                                ];
                                $emoji = $iconMap[$modulo->icono] ?? '📋';
                                $bordeInstitucional = $loop->iteration % 2 === 1
                                    ? 'border-l-[#f9dff8]'
                                    : 'border-l-[#7ce8ad]';
                            @endphp
                            <a href="{{ $modulo->vista ? route($modulo->vista) : '#' }}"
                               class="group relative flex flex-col overflow-hidden rounded-xl border border-slate-200/80 bg-white p-6 shadow-lg shadow-slate-900/25 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-[#7ce8ad]/25 hover:ring-2 hover:ring-[#7ce8ad]/35 border-l-4 {{ $bordeInstitucional }}">
                                <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[#f9dff8] to-[#7ce8ad] opacity-90"></div>
                                <div class="flex flex-col h-full pt-1">
                                    <div class="text-4xl mb-4 group-hover:scale-110 transition-transform duration-300 inline-block">
                                        {{ $emoji }}
                                    </div>
                                    <h3 class="text-base font-bold text-slate-800 group-hover:text-emerald-800 transition-colors mb-2 flex-grow">
                                        {{ $modulo->nombre }}
                                    </h3>
                                    <div class="flex items-center text-sm font-medium text-emerald-700 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                        <span>Ver</span>
                                        <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Accesos Rápidos (mismas reglas que el menú lateral) --}}
                @if(auth()->check())
                    @php
                        $puedeVerRol = function (array $roles) use ($rolUsuario) {
                            $norm = array_map(fn ($r) => \App\Models\Rol::normalizarNombre($r), $roles);
                            return in_array($rolUsuario, $norm, true);
                        };
                        $puedeDashboards = $puedeVerRol(['Admin', 'Operaciones', 'Calidad', 'Gerencia']);
                        $puedeNuevoHallazgo = $puedeVerRol(['Admin', 'Calidad']);
                        $mostrarAccesosRapidos = $puedeDashboards || $puedeNuevoHallazgo;
                    @endphp
                    @if($mostrarAccesosRapidos)
                    <div class="mb-12">
                        <h2
                            class="mb-8 flex items-center gap-3 rounded-xl px-4 py-3 text-xl sm:text-2xl font-bold shadow-lg border border-slate-900/10"
                            style="background: linear-gradient(90deg, #7ce8ad 0%, #f9dff8 100%); color: #0f172a;"
                        >
                            <span class="h-8 w-1.5 shrink-0 rounded-full bg-slate-900/80" aria-hidden="true"></span>
                            Accesos Rápidos
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            @if($puedeDashboards)
                            <a href="{{ route('dashboard') }}"
                               class="group relative overflow-hidden rounded-xl border border-slate-200/80 bg-white p-8 shadow-lg shadow-slate-900/25 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-[#7ce8ad]/20 hover:ring-2 hover:ring-[#7ce8ad]/35 border-l-4 border-l-[#f9dff8]">
                                <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[#f9dff8] to-[#7ce8ad] opacity-90"></div>
                                <div class="flex items-center gap-4 pt-1">
                                    <div class="text-5xl">📊</div>
                                    <div class="flex-grow min-w-0">
                                        <h3 class="font-bold text-slate-900 text-lg group-hover:text-emerald-800 transition-colors">Dashboard Diario</h3>
                                        <p class="text-slate-500 text-sm mt-1">Ver indicadores del día</p>
                                    </div>
                                    <svg class="w-5 h-5 shrink-0 text-slate-400 group-hover:text-emerald-700 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </a>
                            @endif

                            @if($puedeDashboards)
                            <a href="{{ route('dashboard.mensual') }}"
                               class="group relative overflow-hidden rounded-xl border border-slate-200/80 bg-white p-8 shadow-lg shadow-slate-900/25 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-[#7ce8ad]/20 hover:ring-2 hover:ring-[#f9dff8]/50 border-l-4 border-l-[#7ce8ad]">
                                <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[#7ce8ad] to-[#f9dff8] opacity-90"></div>
                                <div class="flex items-center gap-4 pt-1">
                                    <div class="text-5xl">📈</div>
                                    <div class="flex-grow min-w-0">
                                        <h3 class="font-bold text-slate-900 text-lg group-hover:text-emerald-800 transition-colors">Dashboard Mensual</h3>
                                        <p class="text-slate-500 text-sm mt-1">Tendencias y análisis gráfico</p>
                                    </div>
                                    <svg class="w-5 h-5 shrink-0 text-slate-400 group-hover:text-emerald-700 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </a>
                            @endif

                            @if($puedeNuevoHallazgo)
                            <a href="{{ route('hallazgos.registrar') }}"
                               class="group relative overflow-hidden rounded-xl border border-slate-200/80 bg-white p-8 shadow-lg shadow-slate-900/25 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-rose-900/15 hover:ring-2 hover:ring-rose-300/60 border-l-4 border-l-[#f9dff8]">
                                <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[#f9dff8] via-rose-200 to-[#7ce8ad] opacity-90"></div>
                                <div class="flex items-center gap-4 pt-1">
                                    <div class="text-5xl">⚠️</div>
                                    <div class="flex-grow min-w-0">
                                        <h3 class="font-bold text-slate-900 text-lg group-hover:text-rose-700 transition-colors">Nuevo Hallazgo</h3>
                                        <p class="text-slate-500 text-sm mt-1">Registrar un nuevo hallazgo</p>
                                    </div>
                                    <svg class="w-5 h-5 shrink-0 text-slate-400 group-hover:text-rose-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif
                @endif

                {{-- Tiempo de Usabilidad (Solo Admin) --}}
                @if(($rolUsuario ?? '') === 'ADMINISTRADOR')
                    <div class="mb-12">
                        <h2
                            class="mb-8 flex items-center gap-3 rounded-xl px-4 py-3 text-xl sm:text-2xl font-bold shadow-lg border border-slate-900/10"
                            style="background: linear-gradient(90deg, #f9dff8 0%, #7ce8ad 50%, #f9dff8 100%); color: #0f172a;"
                        >
                            <span class="h-8 w-1.5 shrink-0 rounded-full bg-slate-900/80" aria-hidden="true"></span>
                            Tiempo de Usabilidad
                        </h2>
                        <a href="{{ route('tiempo-usabilidad') }}"
                           class="group relative block overflow-hidden rounded-xl border border-slate-200/80 bg-white p-8 shadow-lg shadow-slate-900/25 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-[#7ce8ad]/20 hover:ring-2 hover:ring-[#7ce8ad]/35 border-l-4 border-l-[#7ce8ad]">
                            <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[#7ce8ad] to-[#f9dff8] opacity-90"></div>
                            <div class="flex items-center gap-4 pt-1">
                                <div class="text-5xl">⏱️</div>
                                <div class="flex-grow min-w-0">
                                    <h3 class="font-bold text-slate-900 text-lg group-hover:text-emerald-800 transition-colors">Tiempo de Usabilidad</h3>
                                    <p class="text-slate-500 text-sm mt-1">Controla el tiempo de uso del sistema por usuario con gráficas y estadísticas</p>
                                </div>
                                <svg class="w-5 h-5 shrink-0 text-slate-400 group-hover:text-emerald-700 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </a>
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
