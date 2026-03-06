<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    {{-- Encabezado con selector de fecha --}}
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Dashboard del Día</h2>
            <p class="mt-1 text-sm text-gray-600">Indicadores y métricas de calidad</p>
        </div>
        <div class="flex items-center space-x-3">
            <input type="date" 
                   wire:model="fecha" 
                   wire:change="cambiarFecha($event.target.value)"
                   class="px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            <button wire:click="recalcularIndicadores" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                🔄 Recalcular
            </button>
        </div>
    </div>

    @if($indicadores)
        {{-- Tarjetas de Indicadores Principales --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            {{-- Total Animales --}}
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Animales Procesados</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($indicadores->total_animales) }}</p>
                    </div>
                    <div class="text-4xl">🐄</div>
                </div>
            </div>

            {{-- Porcentaje de Liberación --}}
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">% Liberación</p>
                        <p class="text-3xl font-bold text-green-600 mt-2">{{ number_format($indicadores->porcentaje_liberacion, 2) }}%</p>
                        <p class="text-xs text-gray-500 mt-1">{{ number_format($indicadores->canales_liberadas) }} canales</p>
                    </div>
                    <div class="text-4xl">✅</div>
                </div>
            </div>

            {{-- Total Hallazgos --}}
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Hallazgos</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($indicadores->total_hallazgos) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ number_format($indicadores->porcentaje_hallazgos, 2) }}% del total</p>
                    </div>
                    <div class="text-4xl">⚠️</div>
                </div>
            </div>

            {{-- Hallazgos Críticos --}}
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Hallazgos Críticos</p>
                        <p class="text-3xl font-bold text-red-600 mt-2">{{ number_format($indicadores->hallazgos_criticos) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ number_format($indicadores->hallazgos_leves) }} leves</p>
                    </div>
                    <div class="text-4xl">❌</div>
                </div>
            </div>
        </div>

        {{-- Sección de Gráficos --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Hallazgos por Puesto --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Hallazgos por Puesto de Trabajo</h3>
                @if(count($hallazgosPorPuesto) > 0)
                    <div class="space-y-3">
                        @foreach($hallazgosPorPuesto as $item)
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-medium text-gray-700">{{ $item['puesto'] }}</span>
                                    <span class="text-sm font-bold text-gray-900">{{ $item['total'] }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" 
                                         style="width: {{ ($item['total'] / $indicadores->total_hallazgos) * 100 }}%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Críticos: {{ $item['criticos'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">No hay datos disponibles</p>
                @endif
            </div>

            {{-- Hallazgos por Tipo --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Hallazgos por Tipo</h3>
                @if(count($hallazgosPorTipo) > 0)
                    <div class="space-y-3">
                        @foreach($hallazgosPorTipo as $item)
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-medium text-gray-700">{{ $item['tipo'] }}</span>
                                    <span class="text-sm font-bold {{ $item['es_critico'] ? 'text-red-600' : 'text-gray-900' }}">{{ $item['total'] }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="{{ $item['es_critico'] ? 'bg-red-500' : 'bg-yellow-500' }} h-2 rounded-full" 
                                         style="width: {{ ($item['total'] / $indicadores->total_hallazgos) * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">No hay datos disponibles</p>
                @endif
            </div>
        </div>

        {{-- Gráfica de Hallazgos de Tolerancia Cero por Hora --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Hallazgos de Tolerancia Cero por Hora</h3>
            <div class="relative" style="height: 300px;">
                <canvas id="chartToleranciaZero"></canvas>
            </div>
        </div>

        {{-- Hallazgos TC por Cuarto (Anterior vs Posterior) --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">🔍 Hallazgos Tolerancia Cero - Cuarto Anterior vs Posterior</h3>
            @if(count($hallazgosToleranciaZeroPorCuarto) > 0)
                <div class="space-y-4">
                    @foreach($hallazgosToleranciaZeroPorCuarto as $item)
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-semibold text-gray-900">{{ $item['tipo'] }}</span>
                                <span class="text-xs text-gray-500">Total: {{ $item['CUARTO ANTERIOR'] + $item['CUARTO POSTERIOR'] }}</span>
                            </div>
                            <div class="bg-gray-200 rounded-full h-6 overflow-hidden flex">
                                @php
                                    $totalCuartos = $item['CUARTO ANTERIOR'] + $item['CUARTO POSTERIOR'];
                                    $porcentajeAnterior = $totalCuartos > 0 ? ($item['CUARTO ANTERIOR'] / $totalCuartos) * 100 : 0;
                                    $porcentajePosterior = $totalCuartos > 0 ? ($item['CUARTO POSTERIOR'] / $totalCuartos) * 100 : 0;
                                @endphp
                                @if($item['CUARTO ANTERIOR'] > 0)
                                    <div class="bg-blue-500 h-full flex items-center justify-center text-white text-xs font-bold" 
                                         style="width: {{ $porcentajeAnterior }}%">
                                        {{ $item['CUARTO ANTERIOR'] }}
                                    </div>
                                @endif
                                @if($item['CUARTO POSTERIOR'] > 0)
                                    <div class="bg-orange-500 h-full flex items-center justify-center text-white text-xs font-bold" 
                                         style="width: {{ $porcentajePosterior }}%">
                                        {{ $item['CUARTO POSTERIOR'] }}
                                    </div>
                                @endif
                                @if($totalCuartos == 0)
                                    <div class="bg-gray-300 h-full w-full flex items-center justify-center text-gray-600 text-xs font-bold">
                                        Sin datos
                                    </div>
                                @endif
                            </div>
                            <div class="flex justify-between items-center mt-2">
                                <div class="flex items-center space-x-2">
                                    <span class="inline-block w-3 h-3 bg-blue-500 rounded"></span>
                                    <span class="text-xs text-gray-600">Cuarto Anterior: {{ $item['CUARTO ANTERIOR'] }}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="inline-block w-3 h-3 bg-orange-500 rounded"></span>
                                    <span class="text-xs text-gray-600">Cuarto Posterior: {{ $item['CUARTO POSTERIOR'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No hay hallazgos de Tolerancia Cero registrados en Cuarto Anterior o Posterior</p>
            @endif
        </div>

        {{-- Últimos Hallazgos Registrados --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Últimos Hallazgos Registrados</h3>
            @if($ultimosHallazgos->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hora</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Canal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Puesto</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Operario</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($ultimosHallazgos as $hallazgo)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $hallazgo->created_at->format('H:i') }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $hallazgo->numero_canal }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $hallazgo->tipoHallazgo->es_critico ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $hallazgo->tipoHallazgo->nombre }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $hallazgo->puestoTrabajo->nombre }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $hallazgo->operario->nombre_completo }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No hay hallazgos registrados hoy</p>
            @endif
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <p class="text-yellow-800 font-medium">No hay datos disponibles para esta fecha</p>
            <p class="text-yellow-600 text-sm mt-2">Registra hallazgos para ver los indicadores</p>
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if(@isset($hallazgosTZPorHora) && count($hallazgosTZPorHora) > 0)
            const horas = [];
            const materiaFecalData = [];
            const contenidoRuminalData = [];
            const lecheVisibleData = [];

            @foreach($hallazgosTZPorHora as $hora => $tipos)
                horas.push('{{ str_pad($hora, 2, '0', STR_PAD_LEFT) }}:00');
                materiaFecalData.push({{ $tipos['MATERIA FECAL'] ?? 0 }});
                contenidoRuminalData.push({{ $tipos['CONTENIDO RUMINAL'] ?? 0 }});
                lecheVisibleData.push({{ $tipos['LECHE VISIBLE'] ?? 0 }});
            @endforeach

            const ctx = document.getElementById('chartToleranciaZero').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: horas,
                    datasets: [
                        {
                            label: 'Materia Fecal',
                            data: materiaFecalData,
                            borderColor: '#FCD34D',
                            backgroundColor: 'rgba(252, 211, 77, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#FCD34D',
                            pointBorderColor: '#FCD34D',
                            pointRadius: 4,
                            pointHoverRadius: 6
                        },
                        {
                            label: 'Contenido Ruminal',
                            data: contenidoRuminalData,
                            borderColor: '#F97316',
                            backgroundColor: 'rgba(249, 115, 22, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#F97316',
                            pointBorderColor: '#F97316',
                            pointRadius: 4,
                            pointHoverRadius: 6
                        },
                        {
                            label: 'Leche Visible',
                            data: lecheVisibleData,
                            borderColor: '#3B82F6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#3B82F6',
                            pointBorderColor: '#3B82F6',
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                font: {
                                    size: 12,
                                    weight: 'bold'
                                },
                                padding: 15,
                                usePointStyle: true
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            },
                            title: {
                                display: true,
                                text: 'Cantidad de Hallazgos'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Hora del Día'
                            }
                        }
                    }
                }
            });
        @endif
    });
</script>