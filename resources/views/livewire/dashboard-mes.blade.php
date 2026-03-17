<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8" wire:poll.3s="actualizarDespuesDeRegistro">
    {{-- Header dinámico --}}
    <div class="mb-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-4xl font-bold text-gray-900">📊 Dashboard Mensual</h1>
                <p class="text-gray-600 mt-1">Análisis completo de indicadores y tendencias</p>
            </div>
        </div>

        {{-- Navegación de mes mejorada --}}
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg shadow-md p-6 border border-gray-200">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-4">
                    <button wire:click="cambiarMes('anterior')" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition transform hover:scale-105">
                        ◀ Anterior
                    </button>
                    <div class="text-center">
                        <p class="text-xs text-gray-600 uppercase tracking-wider">Período Actual</p>
                        <p class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                            {{ \Carbon\Carbon::create($anio, $mes, 1)->locale('es')->isoFormat('MMMM') }}
                        </p>
                        <p class="text-sm text-gray-600 mt-1">{{ $anio }}</p>
                    </div>
                    <button wire:click="cambiarMes('siguiente')" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition transform hover:scale-105">
                        Siguiente ▶
                    </button>
                </div>
                <button wire:click="irAHoy" 
                        class="px-6 py-2 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-lg font-semibold shadow-lg transition transform hover:scale-105">
                    📅 Hoy
                </button>
            </div>
        </div>
    </div>

    @if($indicadoresMes)
        {{-- Tarjetas Resumen Mensual Mejoradas --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            {{-- Total Animales del Mes --}}
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow-lg p-6 border-l-4 border-blue-500 transform transition hover:scale-105">
                <div class="flex justify-between items-start mb-3">
                    <h3 class="font-bold text-gray-800 text-sm">🐄 ANIMALES PROCESADOS</h3>
                    <span class="text-2xl">📊</span>
                </div>
                <div class="text-4xl font-extrabold text-blue-600 mb-2">{{ number_format($indicadoresMes['animales_procesados'] ?? 0) }}</div>
                <div class="w-full bg-gray-300 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full" style="width: 100%"></div>
                </div>
                <p class="text-xs text-gray-600 mt-2">Procesados en {{ $indicadoresMes['dias_procesados'] }} días laborales</p>
            </div>

            {{-- Promedio Liberación --}}
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow-lg p-6 border-l-4 border-green-500 transform transition hover:scale-105">
                <div class="flex justify-between items-start mb-3">
                    <h3 class="font-bold text-gray-800 text-sm">✅ PROMEDIO LIBERACIÓN</h3>
                    <span class="text-2xl">📈</span>
                </div>
                <div class="text-4xl font-extrabold text-green-600 mb-2">{{ number_format($indicadoresMes['promedio_liberacion'] ?? 0, 2) }}%</div>
                <div class="w-full bg-gray-300 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ min($indicadoresMes['promedio_liberacion'] ?? 0, 100) }}%"></div>
                </div>
                <p class="text-xs text-gray-600 mt-2">{{ number_format($indicadoresMes['canales_liberadas'] ?? 0) }} medias canales liberadas</p>
            </div>

            {{-- Total Hallazgos --}}
            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg shadow-lg p-6 border-l-4 border-yellow-500 transform transition hover:scale-105">
                <div class="flex justify-between items-start mb-3">
                    <h3 class="font-bold text-gray-800 text-sm">⚠️ TOTAL HALLAZGOS</h3>
                    <span class="text-2xl">🔍</span>
                </div>
                <div class="text-4xl font-extrabold text-yellow-600 mb-2">{{ number_format($indicadoresMes['total_hallazgos'] ?? 0) }}</div>
                <div class="w-full bg-gray-300 rounded-full h-2">
                    <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ min((($indicadoresMes['total_hallazgos'] ?? 0) / (($indicadoresMes['medias_canales_total'] ?? 1) / 5)) * 100, 100) }}%"></div>
                </div>
                <p class="text-xs text-gray-600 mt-2">{{ number_format($indicadoresMes['hallazgos_leves'] ?? 0) }} hallazgos leves</p>
            </div>

            {{-- Hallazgos Críticos --}}
            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg shadow-lg p-6 border-l-4 border-red-500 transform transition hover:scale-105">
                <div class="flex justify-between items-start mb-3">
                    <h3 class="font-bold text-gray-800 text-sm">🚨 HALLAZGOS CRÍTICOS</h3>
                    <span class="text-2xl">❌</span>
                </div>
                <div class="text-4xl font-extrabold text-red-600 mb-2">{{ number_format($indicadoresMes['hallazgos_criticos'] ?? 0) }}</div>
                <div class="w-full bg-gray-300 rounded-full h-2">
                    <div class="bg-red-500 h-2 rounded-full" style="width: {{ $indicadoresMes['total_hallazgos'] > 0 ? min(($indicadoresMes['hallazgos_criticos'] / $indicadoresMes['total_hallazgos']) * 100, 100) : 0 }}%"></div>
                </div>
                <p class="text-xs text-gray-600 mt-2">{{ $indicadoresMes['total_hallazgos'] > 0 ? number_format(($indicadoresMes['hallazgos_criticos'] / $indicadoresMes['total_hallazgos']) * 100, 2) : 0 }}% del total</p>
            </div>
        </div>

        {{-- Desglose de Hallazgos Críticos --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg shadow-md p-5 border border-orange-200">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-bold text-gray-800 text-sm">🧈 Cobertura Grasa</h4>
                    <span class="text-2xl">📦</span>
                </div>
                <div class="text-3xl font-bold text-orange-600">{{ $indicadoresMes['cobertura_grasa'] ?? 0 }}</div>
                <p class="text-xs text-gray-600 mt-2">Hallazgos registrados</p>
            </div>

            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg shadow-md p-5 border border-red-200">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-bold text-gray-800 text-sm">🩸 Hematomas</h4>
                    <span class="text-2xl">💔</span>
                </div>
                <div class="text-3xl font-bold text-red-600">{{ $indicadoresMes['hematomas'] ?? 0 }}</div>
                <p class="text-xs text-gray-600 mt-2">Hallazgos registrados</p>
            </div>

            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg shadow-md p-5 border border-yellow-200">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-bold text-gray-800 text-sm">🦵 Cortes en Pierna</h4>
                    <span class="text-2xl">⚡</span>
                </div>
                <div class="text-3xl font-bold text-yellow-600">{{ $indicadoresMes['cortes_piernas'] ?? 0 }}</div>
                <p class="text-xs text-gray-600 mt-2">Hallazgos registrados</p>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg shadow-md p-5 border border-purple-200">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-bold text-gray-800 text-sm">🐄 Sobrebarriga Rota</h4>
                    <span class="text-2xl">🔴</span>
                </div>
                <div class="text-3xl font-bold text-purple-600">{{ $indicadoresMes['sobrebarriga_rota'] ?? 0 }}</div>
                <p class="text-xs text-gray-600 mt-2">Hallazgos registrados</p>
            </div>
        </div>

        {{-- Hallazgos Tolerancia Cero Mensual --}}
        @if($toleranciaZeroDatos['total'] > 0)
        <div class="mb-8">
            <div class="bg-gradient-to-r from-red-50 to-orange-50 rounded-lg shadow-md p-6 border-l-4 border-red-500 mb-6">
                <h3 class="text-2xl font-bold text-red-700 flex items-center gap-2">
                    <span>🚨</span> Hallazgos Tolerancia Cero del Mes
                </h3>
                <p class="text-sm text-gray-600 mt-1">{{ $toleranciaZeroDatos['total'] }} hallazgos | {{ number_format($toleranciaZeroDatos['totalAnimales']) }} animales procesados | Meta: 1.00%</p>
            </div>

            {{-- Tarjetas de Tolerancia Cero --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
                {{-- Materia Fecal --}}
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg shadow-lg p-6 border-l-4 border-yellow-500">
                    <div class="flex justify-between items-start mb-3">
                        <h4 class="font-bold text-gray-800 text-sm">💛 MATERIA FECAL</h4>
                        <span class="text-xs font-bold px-2 py-1 rounded {{ $toleranciaZeroDatos['materiaFecalPct'] <= 1.0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">META: 1.00%</span>
                    </div>
                    <div class="text-4xl font-extrabold {{ $toleranciaZeroDatos['materiaFecalPct'] <= 1.0 ? 'text-green-600' : 'text-red-600' }} mb-2">{{ number_format($toleranciaZeroDatos['materiaFecalPct'], 2) }}%</div>
                    <p class="text-xs text-gray-600 mb-2">{{ $toleranciaZeroDatos['materiaFecal'] }} hallazgos</p>
                    <div class="mt-3 text-xs text-gray-700">
                        <p>🥩 Anterior: <span class="font-bold">{{ $toleranciaZeroDatos['porProducto']['MATERIA FECAL']['CUARTO ANTERIOR'] }}</span></p>
                        <p>🥩 Posterior: <span class="font-bold">{{ $toleranciaZeroDatos['porProducto']['MATERIA FECAL']['CUARTO POSTERIOR'] }}</span></p>
                    </div>
                </div>

                {{-- Contenido Ruminal --}}
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg shadow-lg p-6 border-l-4 border-orange-500">
                    <div class="flex justify-between items-start mb-3">
                        <h4 class="font-bold text-gray-800 text-sm">🧡 CONTENIDO RUMINAL</h4>
                        <span class="text-xs font-bold px-2 py-1 rounded {{ $toleranciaZeroDatos['contenidoRuminalPct'] <= 1.0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">META: 1.00%</span>
                    </div>
                    <div class="text-4xl font-extrabold {{ $toleranciaZeroDatos['contenidoRuminalPct'] <= 1.0 ? 'text-green-600' : 'text-red-600' }} mb-2">{{ number_format($toleranciaZeroDatos['contenidoRuminalPct'], 2) }}%</div>
                    <p class="text-xs text-gray-600 mb-2">{{ $toleranciaZeroDatos['contenidoRuminal'] }} hallazgos</p>
                    <div class="mt-3 text-xs text-gray-700">
                        <p>🥩 Anterior: <span class="font-bold">{{ $toleranciaZeroDatos['porProducto']['CONTENIDO RUMINAL']['CUARTO ANTERIOR'] }}</span></p>
                        <p>🥩 Posterior: <span class="font-bold">{{ $toleranciaZeroDatos['porProducto']['CONTENIDO RUMINAL']['CUARTO POSTERIOR'] }}</span></p>
                    </div>
                </div>

                {{-- Leche Visible --}}
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow-lg p-6 border-l-4 border-blue-500">
                    <div class="flex justify-between items-start mb-3">
                        <h4 class="font-bold text-gray-800 text-sm">💙 LECHE VISIBLE</h4>
                        <span class="text-xs font-bold px-2 py-1 rounded {{ $toleranciaZeroDatos['lecheVisiblePct'] <= 1.0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">META: 1.00%</span>
                    </div>
                    <div class="text-4xl font-extrabold {{ $toleranciaZeroDatos['lecheVisiblePct'] <= 1.0 ? 'text-green-600' : 'text-red-600' }} mb-2">{{ number_format($toleranciaZeroDatos['lecheVisiblePct'], 2) }}%</div>
                    <p class="text-xs text-gray-600 mb-2">{{ $toleranciaZeroDatos['lecheVisible'] }} hallazgos</p>
                    <div class="mt-3 text-xs text-gray-700">
                        <p>🥩 Anterior: <span class="font-bold">{{ $toleranciaZeroDatos['porProducto']['LECHE VISIBLE']['CUARTO ANTERIOR'] }}</span></p>
                        <p>🥩 Posterior: <span class="font-bold">{{ $toleranciaZeroDatos['porProducto']['LECHE VISIBLE']['CUARTO POSTERIOR'] }}</span></p>
                    </div>
                </div>
            </div>

            {{-- Gráfico de Pastel Tolerancia Cero --}}
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8 border border-gray-200">
                <div class="mb-6">
                    <h3 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <span>📊</span> Distribución por Tipo (%)
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">Porcentaje TC = (hallazgos / (animales × 4)) × 100 | Meta: 1.00%</p>
                </div>
                <div class="h-80 bg-gradient-to-b from-red-50 to-white rounded-lg p-4 flex items-center justify-center">
                    <canvas id="chartToleranciaZeroMes" wire:ignore></canvas>
                </div>
            </div>
        </div>
        @endif

        {{-- Gráfico de Tendencia Mejorado --}}
        <div class="bg-white rounded-lg shadow-lg p-8 mb-8 border border-gray-200">
            <div class="mb-6">
                <h3 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <span>📈</span> Tendencia del Mes
                </h3>
                <p class="text-sm text-gray-600 mt-1">Evolución diaria de hallazgos principales</p>
            </div>
            <div class="h-80 bg-gradient-to-b from-blue-50 to-white rounded-lg p-4">
                <canvas id="chartMensual" wire:ignore></canvas>
            </div>
        </div>

        {{-- Tabla de Indicadores Diarios Mejorada --}}
        <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
                <h3 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <span>📅</span> Detalle Diario del Mes
                </h3>
                <p class="text-sm text-gray-600 mt-1">Indicadores de cada día del período seleccionado</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100 border-b-2 border-gray-300">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">📅 Fecha</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">🐄 Animales</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">✅ % Liberación</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">⚠️ Total Hallazgos</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">🚨 Críticos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($indicadoresDiarios as $dia)
                            @php
                                $totalMedias = $dia->medias_canales_total ?: 1;
                                $pctLiberacion = $totalMedias > 0 ? round((($dia->medias_canal_1 + $dia->medias_canal_2) / $totalMedias) * 100, 2) : 0;
                                $hallazgos = $dia->total_hallazgos ?? 0;
                                $criticos = ($dia->cobertura_grasa ?? 0) + ($dia->hematomas ?? 0) + ($dia->cortes_piernas ?? 0) + ($dia->sobrebarriga_rota ?? 0);
                            @endphp
                            <tr class="hover:bg-blue-50 transition duration-200">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($dia->fecha_operacion)->locale('es')->isoFormat('dddd, DD MMM') }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-bold">
                                        {{ number_format($dia->animales_procesados ?? 0) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-sm font-bold {{ $pctLiberacion >= 90 ? 'bg-green-100 text-green-700' : ($pctLiberacion >= 80 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                        {{ number_format($pctLiberacion, 1) }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-bold">
                                        {{ number_format($hallazgos) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center px-3 py-1 {{ $criticos > 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }} rounded-full text-sm font-bold">
                                        {{ number_format($criticos) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
        <script>
            document.addEventListener('livewire:navigated', function() {
                const ctx = document.getElementById('chartMensual');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'line',
                        data: @json($graficoDatos),
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return value.toFixed(0) + '%';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                // Gráfico de Tolerancia Cero
                const ctxTZ = document.getElementById('chartToleranciaZeroMes');
                if (ctxTZ) {
                    @php
                        $labels = json_encode($toleranciaZeroDatos['labels'] ?? []);
                        $values = json_encode($toleranciaZeroDatos['values'] ?? []);
                        $colors = json_encode($toleranciaZeroDatos['colors'] ?? []);
                    @endphp
                    
                    new Chart(ctxTZ, {
                        type: 'doughnut',
                        data: {
                            labels: {!! $labels !!},
                            datasets: [{
                                data: {!! $values !!},
                                backgroundColor: {!! $colors !!},
                                borderColor: ['#F5D547', '#E56D22', '#2563EB'],
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom',
                                    labels: {
                                        font: {
                                            size: 13,
                                            weight: 'bold'
                                        },
                                        padding: 20,
                                        usePointStyle: true,
                                        pointStyle: 'circle'
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.label + ': ' + context.parsed.toFixed(2) + '%';
                                        }
                                    }
                                },
                                datalabels: {
                                    color: '#fff',
                                    font: {
                                        weight: 'bold',
                                        size: 14
                                    },
                                    formatter: function(value) {
                                        return value > 0 ? value.toFixed(2) + '%' : '';
                                    }
                                }
                            }
                        },
                        plugins: [ChartDataLabels || {}]
                    });
                }
            });
        </script>
        @endpush
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <p class="text-yellow-800 font-medium">No hay datos disponibles para este mes</p>
        </div>
    @endif
</div>