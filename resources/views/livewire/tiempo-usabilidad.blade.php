<div wire:poll.30s="cargarDatos">
    <div class="py-4 sm:py-6">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

            {{-- Header con logo --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                <div class="flex items-center gap-3 sm:gap-4">
                    <img src="{{ asset('logo.png') }}" alt="Logo" class="h-6 sm:h-10 max-w-[40px] sm:max-w-[80px] object-contain flex-shrink-0">
                    <div class="min-w-0">
                        <h1 class="text-lg sm:text-2xl font-bold text-gray-900 truncate">Tiempo de Usabilidad</h1>
                        <p class="text-gray-500 mt-1 text-xs sm:text-sm">Control del tiempo de uso del sistema por usuario</p>
                    </div>
                </div>
            </div>

            {{-- Filtros --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex flex-wrap items-end gap-4">
                    {{-- Periodo rápido --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Período</label>
                        <select wire:model.live="periodo" class="text-sm rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="semana">Última semana</option>
                            <option value="mes">Este mes</option>
                            <option value="personalizado">Personalizado</option>
                        </select>
                    </div>

                    {{-- Fecha inicio --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Desde</label>
                        <input type="date" wire:model.live="fechaInicio" class="text-sm rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    {{-- Fecha fin --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Hasta</label>
                        <input type="date" wire:model.live="fechaFin" class="text-sm rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    {{-- Filtro usuario --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Usuario</label>
                        <select wire:model.live="usuarioSeleccionado" class="text-sm rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Todos los usuarios</option>
                            @foreach($this->usuarios as $usuario)
                                <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Tarjetas Resumen --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Total Horas --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 p-3 bg-blue-100 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Total Horas</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $resumen['total_horas'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                {{-- Total Sesiones --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 p-3 bg-green-100 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Total Sesiones</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $resumen['total_sesiones'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                {{-- Promedio por Sesión --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 p-3 bg-amber-100 rounded-lg">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Promedio / Sesión</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $resumen['promedio_minutos'] ?? 0 }} <span class="text-sm font-normal text-gray-500">min</span></p>
                        </div>
                    </div>
                </div>

                {{-- Usuarios Activos --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 p-3 bg-purple-100 rounded-lg">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Usuarios Activos</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $resumen['usuarios_activos'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gráficos --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                {{-- Gráfico de Barras: Horas por Usuario --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Horas de Uso por Usuario</h3>
                    <div style="position: relative; height: 300px;">
                        <canvas id="chartBarras"></canvas>
                    </div>
                </div>

                {{-- Gráfico de Línea: Actividad Diaria --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Actividad Diaria (Horas)</h3>
                    <div style="position: relative; height: 300px;">
                        <canvas id="chartLinea"></canvas>
                    </div>
                </div>
            </div>

            {{-- Tabla de Sesiones --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-5 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700">Historial de Sesiones (últimas 50)</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Inicio de Sesión</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cierre de Sesión</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duración</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($sesionesTabla as $sesion)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-900 font-medium">{{ $sesion['usuario'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $sesion['login_at'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        @if($sesion['logout_at'] === 'Activa')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Activa</span>
                                        @else
                                            {{ $sesion['logout_at'] }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        @if($sesion['duracion'] === 'En curso')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">En curso</span>
                                        @else
                                            {{ $sesion['duracion'] }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-400 font-mono text-xs">{{ $sesion['ip'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-400">
                                        No hay sesiones registradas en el período seleccionado
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let chartBarras = null;
        let chartLinea = null;

        function inicializarGraficos(datosBarras, datosLinea) {
            // Destruir gráficos existentes
            if (chartBarras) chartBarras.destroy();
            if (chartLinea) chartLinea.destroy();

            // Gráfico de Barras
            const ctxBarras = document.getElementById('chartBarras');
            if (ctxBarras && datosBarras.labels && datosBarras.labels.length > 0) {
                chartBarras = new Chart(ctxBarras, {
                    type: 'bar',
                    data: {
                        labels: datosBarras.labels,
                        datasets: [{
                            label: 'Horas de uso',
                            data: datosBarras.valores,
                            backgroundColor: datosBarras.colores,
                            borderColor: datosBarras.colores,
                            borderWidth: 1,
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        return ctx.parsed.y.toFixed(2) + ' horas';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: 'Horas' },
                                grid: { color: '#f3f4f6' }
                            },
                            x: {
                                grid: { display: false }
                            }
                        }
                    }
                });
            }

            // Gráfico de Línea
            const ctxLinea = document.getElementById('chartLinea');
            if (ctxLinea && datosLinea.labels && datosLinea.labels.length > 0) {
                chartLinea = new Chart(ctxLinea, {
                    type: 'line',
                    data: {
                        labels: datosLinea.labels,
                        datasets: [
                            {
                                label: 'Horas',
                                data: datosLinea.horas,
                                borderColor: '#3B82F6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                fill: true,
                                tension: 0.3,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                            },
                            {
                                label: 'Sesiones',
                                data: datosLinea.sesiones,
                                borderColor: '#10B981',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                fill: false,
                                tension: 0.3,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                yAxisID: 'y1',
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: { position: 'top' }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: 'Horas' },
                                grid: { color: '#f3f4f6' }
                            },
                            y1: {
                                beginAtZero: true,
                                position: 'right',
                                title: { display: true, text: 'Sesiones' },
                                grid: { drawOnChartArea: false }
                            },
                            x: {
                                grid: { display: false }
                            }
                        }
                    }
                });
            }
        }

        // Inicializar al cargar
        document.addEventListener('DOMContentLoaded', function() {
            inicializarGraficos(
                @json($datosBarras),
                @json($datosLinea)
            );
        });

        // Actualizar cuando Livewire envíe nuevos datos
        document.addEventListener('livewire:initialized', function() {
            Livewire.on('datosActualizados', (event) => {
                const datos = event[0] || event;
                inicializarGraficos(datos.barras, datos.linea);
            });
        });
    </script>
</div>
