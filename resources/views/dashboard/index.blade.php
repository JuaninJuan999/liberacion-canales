<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard - Indicadores de Calidad
            </h2>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">
            
            <!-- Filtro de Fechas -->
            <div class="bg-white shadow-sm rounded-lg p-3 sm:p-4">
                <form id="formFiltroDashboard" method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-2 sm:gap-4">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-1 sm:gap-2 w-full sm:w-auto">
                        <label class="text-xs sm:text-sm font-medium text-gray-700">Desde:</label>
                        <input type="date" 
                               name="fecha_inicio" 
                               value="{{ $fecha_inicio }}"
                               onchange="this.form.submit()"
                               class="w-full sm:w-auto text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-1 sm:gap-2 w-full sm:w-auto">
                        <label class="text-xs sm:text-sm font-medium text-gray-700">Hasta:</label>
                        <input type="date" 
                               name="fecha_fin" 
                               value="{{ $fecha_fin }}"
                               onchange="this.form.submit()"
                               class="w-full sm:w-auto text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <button type="submit" 
                            class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-xs sm:text-sm font-medium">
                        Aplicar
                    </button>
                    <a href="{{ route('dashboard') }}" 
                       class="w-full sm:w-auto text-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-xs sm:text-sm font-medium">
                        Limpiar filtro
                    </a>
                </form>
            </div>

            <!-- Gráficos Principales -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" id="chartsContainer">
                <div class="bg-white p-3 sm:p-4 rounded-lg shadow-md cursor-pointer hover:shadow-lg transition-shadow" onclick="abrirGrafico('hallazgosChartCanal1', 'Distribución de Hallazgos - Media Canal 1')">
                    <h3 class="font-bold mb-2 text-center text-sm sm:text-base">Distribución de Hallazgos - Media Canal 1</h3>
                    <div class="h-48 sm:h-64 md:h-72 lg:h-80">
                        <canvas id="hallazgosChartCanal1"></canvas>
                    </div>
                </div>
                <div class="bg-white p-3 sm:p-4 rounded-lg shadow-md cursor-pointer hover:shadow-lg transition-shadow" onclick="abrirGrafico('hallazgosChartCanal2', 'Distribución de Hallazgos - Media Canal 2')">
                    <h3 class="font-bold mb-2 text-center text-sm sm:text-base">Distribución de Hallazgos - Media Canal 2</h3>
                    <div class="h-48 sm:h-64 md:h-72 lg:h-80">
                        <canvas id="hallazgosChartCanal2"></canvas>
                    </div>
                </div>
                <div class="bg-white p-3 sm:p-4 rounded-lg shadow-md cursor-pointer hover:shadow-lg transition-shadow" onclick="abrirGrafico('productosChart', 'Hallazgos por Producto')">
                    <h3 class="font-bold mb-2 text-center text-sm sm:text-base">Hallazgos por Producto</h3>
                    <div class="h-48 sm:h-64 md:h-72 lg:h-80">
                        <canvas id="productosChart"></canvas>
                    </div>
                </div>
                <div class="bg-white p-3 sm:p-4 rounded-lg shadow-md cursor-pointer hover:shadow-lg transition-shadow" onclick="abrirGrafico('puestosChart', 'Hallazgos por Operario y Tipo')">
                    <h3 class="font-bold mb-2 text-center text-sm sm:text-base">Hallazgos por Operario y Tipo</h3>
                    <p class="text-xs text-gray-500 text-center mb-1">Operario · Tipo de hallazgo</p>
                    <div class="h-48 sm:h-64 md:h-72 lg:h-80">
                        <canvas id="puestosChart"></canvas>
                    </div>
                </div>
                <div class="bg-white p-3 sm:p-4 rounded-lg shadow-md cursor-pointer hover:shadow-lg transition-shadow" onclick="abrirGrafico('hallazgosTZAnteriorChart', 'Hallazgos de Tolerancia Cero - CUARTO ANTERIOR')">
                    <h3 class="font-bold mb-2 text-center text-sm sm:text-base">Hallazgos TC - CUARTO ANTERIOR</h3>
                    <div class="h-48 sm:h-64 md:h-72 lg:h-80">
                        <canvas id="hallazgosTZAnteriorChart"></canvas>
                    </div>
                </div>
                <div class="bg-white p-3 sm:p-4 rounded-lg shadow-md cursor-pointer hover:shadow-lg transition-shadow" onclick="abrirGrafico('hallazgosTZPosteriorChart', 'Hallazgos de Tolerancia Cero - CUARTO POSTERIOR')">
                    <h3 class="font-bold mb-2 text-center text-sm sm:text-base">Hallazgos TC - CUARTO POSTERIOR</h3>
                    <div class="h-48 sm:h-64 md:h-72 lg:h-80">
                        <canvas id="hallazgosTZPosteriorChart"></canvas>
                    </div>
                </div>
                <div class="bg-white p-3 sm:p-4 rounded-lg shadow-md lg:col-span-2 cursor-pointer hover:shadow-lg transition-shadow" onclick="abrirGrafico('hallazgosTZComparativoChart', 'Hallazgos de Tolerancia Cero - Cuarto Anterior vs Posterior')">
                    <h3 class="font-bold mb-2 text-center text-sm sm:text-base">Hallazgos TC - Anterior vs Posterior</h3>
                    <div class="h-48 sm:h-64 md:h-72 lg:h-80">
                        <canvas id="hallazgosTZComparativoChart"></canvas>
                    </div>
                </div>
                <div class="bg-white p-3 sm:p-4 rounded-lg shadow-md cursor-pointer hover:shadow-lg transition-shadow" onclick="abrirGrafico('hallazgosTZOperarioChart', 'Hallazgos de Tolerancia Cero por Operario')">
                    <h3 class="font-bold mb-2 text-center text-sm sm:text-base">Hallazgos TC por Operario</h3>
                    <p class="text-xs text-gray-500 text-center mb-1">Operario · Tipo de hallazgo</p>
                    <div class="h-48 sm:h-64 md:h-72 lg:h-80">
                        <canvas id="hallazgosTZOperarioChart"></canvas>
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

            const legendaCompleta = {
                display: true,
                position: 'bottom',
                labels: {
                    boxWidth: 12,
                    padding: 8,
                    font: { size: 11 },
                    usePointStyle: true,
                    pointStyle: 'circle'
                }
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
                        legend: legendaCompleta,
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
                        legend: legendaCompleta,
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
                        legend: legendaCompleta,
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
                        legend: legendaCompleta,
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

            // 5. Hallazgos de Tolerancia Cero - Doughnut Charts por Producto con Ubicación
            @php
                $tzAnterior = $hallazgosTZPorDia['CUARTO ANTERIOR'] ?? [];
                $tzPosterior = $hallazgosTZPorDia['CUARTO POSTERIOR'] ?? [];
                
                // Paleta de colores azules para Cuarto Anterior
                $coloresAnterior = ['#1D4ED8', '#2563EB', '#3B82F6', '#60A5FA', '#93C5FD', '#BFDBFE', '#DBEAFE'];
                
                // Paleta de colores naranjas para Cuarto Posterior
                $coloresPosterior = ['#B45309', '#D97706', '#F97316', '#FB923C', '#FBBD23', '#FCD34D', '#FEF3C7'];
            @endphp

            // Cuarto Anterior (con ubicación - tonos azules)
            new Chart(document.getElementById('hallazgosTZAnteriorChart'), {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode(array_keys($tzAnterior)) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($tzAnterior)) !!},
                        backgroundColor: {!! json_encode(array_slice(array_merge($coloresAnterior, array_fill(0, 20, '#5B9FDB')), 0, count($tzAnterior))) !!},
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    ...baseChartOptions,
                    plugins: {
                        ...baseChartOptions.plugins,
                        legend: legendaCompleta,
                        datalabels: {
                            formatter: (value, ctx) => {
                                const total = ctx.chart.getDatasetMeta(0).total;
                                const percentage = total > 0 ? (value / total) * 100 : 0;
                                return percentage > 6 ? percentage.toFixed(1) + '%' : '';
                            },
                            color: '#fff',
                            textStrokeColor: '#333',
                            textStrokeWidth: 1.5,
                            font: { weight: 'bold', size: 11 }
                        }
                    }
                }
            });

            // Cuarto Posterior (con ubicación - tonos naranjas)
            new Chart(document.getElementById('hallazgosTZPosteriorChart'), {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode(array_keys($tzPosterior)) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($tzPosterior)) !!},
                        backgroundColor: {!! json_encode(array_slice(array_merge($coloresPosterior, array_fill(0, 20, '#F59E0B')), 0, count($tzPosterior))) !!},
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    ...baseChartOptions,
                    plugins: {
                        ...baseChartOptions.plugins,
                        legend: legendaCompleta,
                        datalabels: {
                            formatter: (value, ctx) => {
                                const total = ctx.chart.getDatasetMeta(0).total;
                                const percentage = total > 0 ? (value / total) * 100 : 0;
                                return percentage > 6 ? percentage.toFixed(1) + '%' : '';
                            },
                            color: '#fff',
                            textStrokeColor: '#333',
                            textStrokeWidth: 1.5,
                            font: { weight: 'bold', size: 11 }
                        }
                    }
                }
            });

            // Gráfico Comparativo - Cuarto Anterior vs Posterior (Total Impacto)
            @php
                $totalAnterior = array_sum($tzAnterior);
                $totalPosterior = array_sum($tzPosterior);
            @endphp
            
            new Chart(document.getElementById('hallazgosTZComparativoChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Cuarto Anterior', 'Cuarto Posterior'],
                    datasets: [{
                        data: [{{ $totalAnterior }}, {{ $totalPosterior }}],
                        backgroundColor: ['#3B82F6', '#F97316'],
                        borderColor: ['#1D4ED8', '#EA580C'],
                        borderWidth: 2
                    }]
                },
                options: {
                    ...baseChartOptions,
                    plugins: {
                        ...baseChartOptions.plugins,
                        legend: legendaCompleta,
                        datalabels: {
                            formatter: (value, ctx) => {
                                const total = ctx.chart.getDatasetMeta(0).total;
                                const percentage = total > 0 ? (value / total) * 100 : 0;
                                return percentage > 5 ? percentage.toFixed(1) + '%' : '';
                            },
                            color: '#fff',
                            textStrokeColor: '#333',
                            textStrokeWidth: 1.5,
                            font: { weight: 'bold', size: 12 }
                        }
                    }
                }
            });
            new Chart(document.getElementById('hallazgosTZOperarioChart'), {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode(array_keys($hallazgosTZPorOperario)) !!},
                    datasets: [{
                        label: 'Hallazgos',
                        data: {!! json_encode(array_values($hallazgosTZPorOperario)) !!},
                        backgroundColor: colores,
                    }]
                },
                options: {
                    ...baseChartOptions,
                     plugins: {
                        ...baseChartOptions.plugins,
                        legend: legendaCompleta,
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

            // Función para abrir gráfico en modal
            window.abrirGrafico = function(canvasId, titulo) {
                const modal = document.getElementById('graficoModal');
                const modalTitle = document.getElementById('modalGraficoTitulo');
                const modalCanvas = document.getElementById('modalGraficoCanvas');
                
                // Actualizar título
                modalTitle.textContent = titulo;
                
                // Buscar el gráfico en las instancias de Chart.js
                let chartOriginal = null;
                
                for (let key in Chart.instances) {
                    const instance = Chart.instances[key];
                    if (instance.canvas && instance.canvas.id === canvasId) {
                        chartOriginal = instance;
                        break;
                    }
                }
                
                if (chartOriginal) {
                    // Destruir gráfico anterior en el modal si existe
                    if (window.modalChartInstance) {
                        window.modalChartInstance.destroy();
                        window.modalChartInstance = null;
                    }
                    
                    // Clonar configuración del gráfico
                    const config = {
                        type: chartOriginal.config.type,
                        data: {
                            labels: chartOriginal.data.labels,
                            datasets: chartOriginal.data.datasets.map(ds => ({
                                ...ds,
                                data: [...ds.data]
                            }))
                        },
                        options: {
                            ...chartOriginal.config.options,
                            responsive: true,
                            maintainAspectRatio: true
                        }
                    };
                    
                    // Crear nuevo gráfico en el modal
                    const ctx = modalCanvas.getContext('2d');
                    window.modalChartInstance = new Chart(ctx, config);
                }
                
                // Mostrar modal
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            };

            // Función para cerrar modal
            window.cerrarGrafico = function() {
                const modal = document.getElementById('graficoModal');
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
                
                if (window.modalChartInstance) {
                    window.modalChartInstance.destroy();
                    window.modalChartInstance = null;
                }
            };

            // Cerrar modal al hacer click fuera
            document.getElementById('graficoModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    cerrarGrafico();
                }
            });

            // Cerrar modal con tecla ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    cerrarGrafico();
                }
            });
        });
    </script>

    <!-- Modal para ampliar gráficos -->
    <div id="graficoModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-[9999] flex items-center justify-center p-2 sm:p-4" style="z-index: 9999;">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-7xl max-h-[98vh] sm:max-h-[95vh] overflow-auto" onclick="event.stopPropagation();">
            <!-- Encabezado del modal -->
            <div class="flex justify-between items-center p-3 sm:p-6 border-b border-gray-200 bg-white sticky top-0 z-10">
                <h2 id="modalGraficoTitulo" class="text-base sm:text-xl md:text-2xl font-bold text-gray-900 pr-2"></h2>
                <button onclick="cerrarGrafico()" class="text-gray-500 hover:text-gray-700 text-2xl sm:text-3xl font-bold px-2 sm:px-4 py-1 sm:py-2 hover:bg-gray-100 rounded flex-shrink-0">
                    ×
                </button>
            </div>

            <!-- Cuerpo del modal con gráfico -->
            <div class="p-3 sm:p-6 md:p-8">
                <div class="relative w-full" style="height: 50vh; min-height: 300px; max-height: 600px;">
                    <canvas id="modalGraficoCanvas"></canvas>
                </div>
            </div>

            <!-- Footer del modal -->
            <div class="flex justify-end p-3 sm:p-6 border-t border-gray-200 bg-white sticky bottom-0">
                <button onclick="cerrarGrafico()" class="w-full sm:w-auto px-4 sm:px-6 py-2 sm:py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium text-sm sm:text-base">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</x-app-layout>
