<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard - Indicadores de Calidad
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Filtro de Fechas -->
            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <form id="formFiltroDashboard" method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700">Desde:</label>
                        <input type="date" 
                               name="fecha_inicio" 
                               value="{{ $fecha_inicio }}"
                               onchange="this.form.submit()"
                               class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700">Hasta:</label>
                        <input type="date" 
                               name="fecha_fin" 
                               value="{{ $fecha_fin }}"
                               onchange="this.form.submit()"
                               class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                        Aplicar Filtro
                    </button>
                    <a href="{{ route('dashboard') }}" 
                       class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm font-medium">
                        Limpiar filtro
                    </a>
                </form>
            </div>

            <!-- Gráficos Principales -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h3 class="font-bold mb-2 text-center">Distribución de Hallazgos - Media Canal 1</h3>
                    <div style="height: 300px;">
                        <canvas id="hallazgosChartCanal1"></canvas>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h3 class="font-bold mb-2 text-center">Distribución de Hallazgos - Media Canal 2</h3>
                    <div style="height: 300px;">
                        <canvas id="hallazgosChartCanal2"></canvas>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h3 class="font-bold mb-2 text-center">Hallazgos por Producto</h3>
                    <div style="height: 300px;">
                        <canvas id="productosChart"></canvas>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h3 class="font-bold mb-2 text-center">Hallazgos por Operario y Tipo</h3>
                    <p class="text-xs text-gray-500 text-center mb-1">Operario · Tipo de hallazgo</p>
                    <div style="height: 300px;">
                        <canvas id="puestosChart"></canvas>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h3 class="font-bold mb-2 text-center">Hallazgos por Tipo (Materia Fecal, Contenido Ruminal, Leche Visible)</h3>
                    <div style="height: 300px;">
                        <canvas id="hallazgosNuevosChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tarjetas de Indicadores -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500 uppercase">Animales Procesados</p>
                    <p class="text-3xl font-bold text-blue-600">{{ number_format($animalesProcesados, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500 uppercase">Total Hallazgos</p>
                    <p class="text-3xl font-bold text-red-600">{{ $indicador->total_hallazgos }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500 uppercase">Participación</p>
                    <p class="text-3xl font-bold text-orange-600">{{ number_format($indicador->participacion_total, 2) }}%</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500 uppercase">Medias Canales</p>
                    <p class="text-3xl font-bold text-green-600">{{ $indicador->medias_canales_total }}</p>
                </div>
            </div>

            <!-- Últimos Hallazgos -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Últimos Hallazgos Registrados</h3>
                    @if($hallazgosDia->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Hallazgo</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Operario</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($hallazgosDia->take(15) as $hallazgo)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 text-sm">{{ $hallazgo->fecha_operacion->format('d/m/Y') }}</td>
                                            <td class="px-4 py-2 text-sm font-medium">{{ $hallazgo->codigo }}</td>
                                            <td class="px-4 py-2 text-sm">{{ $hallazgo->producto->nombre }}</td>
                                            <td class="px-4 py-2 text-sm">{{ $hallazgo->tipoHallazgo->nombre }}</td>
                                            <td class="px-4 py-2 text-sm">{{ $hallazgo->operario?->nombre_completo ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">No hay hallazgos registrados para el rango de fechas</p>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Chart.register(ChartDataLabels);

            const baseChartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                let value = context.raw;
                                if (context.chart.config.type === 'pie' || context.chart.config.type === 'doughnut') {
                                    const total = context.chart.getDatasetMeta(0).total;
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(2) + '%' : '0%';
                                    return ` ${label}: ${value} (${percentage})`;
                                }
                                return ` ${label}: ${value}`;
                            }
                        }
                    },
                },
            };

            const colores = ['#264653', '#2a9d8f', '#e9c46a', '#f4a261', '#e76f51', '#A8DADC', '#457B9D', '#1D3557'];

            // 1. Hallazgos - Media Canal 1
            new Chart(document.getElementById('hallazgosChartCanal1'), {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($hallazgosChartDataCanal1->keys()) !!},
                    datasets: [{
                        data: {!! json_encode($hallazgosChartDataCanal1->values()) !!},
                        backgroundColor: colores,
                    }]
                },
                options: {
                    ...baseChartOptions,
                    plugins: {
                        ...baseChartOptions.plugins,
                        legend: { display: true, position: 'right' },
                        datalabels: {
                            formatter: (value, ctx) => {
                                const total = ctx.chart.getDatasetMeta(0).total;
                                const percentage = total > 0 ? (value / total) * 100 : 0;
                                return percentage > 6 ? percentage.toFixed(1) + '%' : '';
                            },
                            color: '#fff',
                            textStrokeColor: '#333',
                            textStrokeWidth: 1.5,
                            font: { weight: 'bold', size: 12 }
                        }
                    }
                }
            });

            // 2. Hallazgos - Media Canal 2
            new Chart(document.getElementById('hallazgosChartCanal2'), {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($hallazgosChartDataCanal2->keys()) !!},
                    datasets: [{
                        data: {!! json_encode($hallazgosChartDataCanal2->values()) !!},
                        backgroundColor: colores,
                    }]
                },
                options: {
                    ...baseChartOptions,
                    plugins: {
                        ...baseChartOptions.plugins,
                        legend: { display: true, position: 'right' },
                        datalabels: {
                            formatter: (value, ctx) => {
                                const total = ctx.chart.getDatasetMeta(0).total;
                                const percentage = total > 0 ? (value / total) * 100 : 0;
                                return percentage > 6 ? percentage.toFixed(1) + '%' : '';
                            },
                            color: '#fff',
                            textStrokeColor: '#333',
                            textStrokeWidth: 1.5,
                            font: { weight: 'bold', size: 12 }
                        }
                    }
                }
            });

            // 3. Productos - Pie Chart
            new Chart(document.getElementById('productosChart'), {
                type: 'pie',
                data: {
                    labels: {!! json_encode($productosChartData->keys()) !!},
                    datasets: [{
                        data: {!! json_encode($productosChartData->values()) !!},
                        backgroundColor: colores,
                    }]
                },
                options: {
                    ...baseChartOptions,
                    plugins: {
                        ...baseChartOptions.plugins,
                        legend: { display: true, position: 'right' },
                        datalabels: {
                            formatter: (value, ctx) => {
                                const total = ctx.chart.getDatasetMeta(0).total;
                                const percentage = total > 0 ? (value / total) * 100 : 0;
                                return percentage > 6 ? percentage.toFixed(1) + '%' : '';
                            },
                            color: '#fff',
                            textStrokeColor: '#333',
                            textStrokeWidth: 1.5,
                            font: { weight: 'bold', size: 12 }
                        }
                    }
                }
            });

            // 4. Puestos de Trabajo - Doughnut Chart
            new Chart(document.getElementById('puestosChart'), {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($hallazgosPorOperarioYTipo->keys()) !!},
                    datasets: [{
                        label: 'Hallazgos',
                        data: {!! json_encode($hallazgosPorOperarioYTipo->values()) !!},
                        backgroundColor: colores,
                    }]
                },
                options: {
                    ...baseChartOptions,
                     plugins: {
                        ...baseChartOptions.plugins,
                        legend: { display: true, position: 'right' },
                        datalabels: {
                            formatter: (value, ctx) => {
                                const total = ctx.chart.getDatasetMeta(0).total;
                                const percentage = total > 0 ? (value / total) * 100 : 0;
                                return percentage > 6 ? percentage.toFixed(1) + '%' : '';
                            },
                            color: '#fff',
                            textStrokeColor: '#333',
                            textStrokeWidth: 1.5,
                            font: { weight: 'bold', size: 12 }
                        }
                    }
                }
            });

            // 5. Hallazgos Nuevos - Bar Chart
            new Chart(document.getElementById('hallazgosNuevosChart'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_keys($hallazgosNuevos)) !!},
                    datasets: [{
                        label: 'Cantidad',
                        data: {!! json_encode(array_values($hallazgosNuevos)) !!},
                        backgroundColor: ['#8B5CF6', '#06B6D4', '#EC4899'],
                        borderColor: ['#8B5CF6', '#06B6D4', '#EC4899'],
                        borderWidth: 1
                    }]
                },
                options: {
                    ...baseChartOptions,
                    indexAxis: 'y',
                    plugins: {
                        ...baseChartOptions.plugins,
                        legend: { display: true, position: 'top' },
                        datalabels: {
                            anchor: 'end',
                            align: 'end',
                            color: '#000',
                            font: { weight: 'bold', size: 12 }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>
