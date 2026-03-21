<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Header con logo --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                        <img src="{{ asset('logo.png') }}" alt="Logo" class="h-6 sm:h-10 max-w-[40px] sm:max-w-[80px] object-contain flex-shrink-0">
                        <div class="min-w-0">
                            <h1 class="text-lg sm:text-2xl font-bold text-gray-900 truncate">Dashboard Mensual - Indicadores</h1>
                            <p class="text-gray-500 mt-1 text-xs sm:text-sm">Análisis completo de indicadores y tendencias</p>
                        </div>
                    </div>
                    <a href="{{ route('dashboard') }}"
                       class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition text-xs sm:text-sm flex-shrink-0 text-center">
                        ← Volver al Dashboard Diario
                    </a>
                </div>
            </div>
            
            {{-- Selector de Mes/Año --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <form method="GET" action="{{ route('dashboard.mensual') }}" class="flex items-center gap-4">
                    <label class="text-sm font-medium text-gray-700">Mes:</label>
                    <select name="mes" 
                            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            onchange="this.form.submit()">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == $mes ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->locale('es')->isoFormat('MMMM') }}
                            </option>
                        @endfor
                    </select>
                    
                    <label class="text-sm font-medium text-gray-700">Año:</label>
                    <select name="anio" 
                            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            onchange="this.form.submit()">
                        @for($a = now()->year - 2; $a <= now()->year + 1; $a++)
                            <option value="{{ $a }}" {{ $a == $anio ? 'selected' : '' }}>{{ $a }}</option>
                        @endfor
                    </select>
                </form>
            </div>

            {{-- Tarjetas Resumen del Mes --}}
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-7 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-sm text-gray-500 uppercase">Días Operados</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $totales['dias_operados'] }}</p>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-sm text-gray-500 uppercase">Total Animales</p>
                    <p class="text-3xl font-bold text-green-600">{{ number_format($totales['animales'], 0, ',', '.') }}</p>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-sm text-gray-500 uppercase">Total Hallazgos</p>
                    <p class="text-3xl font-bold text-red-600">{{ number_format($totales['hallazgos'], 0, ',', '.') }}</p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-sm text-gray-500 uppercase">Sobrebarriga Rotas</p>
                    <p class="text-3xl font-bold text-orange-600">{{ number_format($totales['sobrebarriga_rotas'], 0, ',', '.') }}</p>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-sm text-gray-500 uppercase">Hematomas</p>
                    <p class="text-3xl font-bold text-purple-600">{{ number_format($totales['hematomas'], 0, ',', '.') }}</p>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-sm text-gray-500 uppercase">Cobertura Grasa</p>
                    <p class="text-3xl font-bold text-yellow-600">{{ number_format($totales['cobertura'], 0, ',', '.') }}</p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-sm text-gray-500 uppercase">Cortes Piernas</p>
                    <p class="text-3xl font-bold text-pink-600">{{ number_format($totales['cortes_piernas'], 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Gráficos de Tendencia --}}
            @if($indicadores->count() > 0)

            {{-- Botón Descargar Gráficas --}}
            <div class="flex justify-end mb-2">
                <button onclick="abrirModalDescarga()"
                        class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                    </svg>
                    Descargar gráficas
                </button>
            </div>

            {{-- Modal de selección --}}
            <div id="modalDescarga" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm mx-4 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Seleccionar gráficas a descargar</h3>
                    <ul class="space-y-3 mb-6">
                        <li class="flex items-center gap-3">
                            <input type="checkbox" id="chk_sobrebarriga" value="chartSobrebarriga" checked
                                   class="w-4 h-4 accent-indigo-600 cursor-pointer">
                            <label for="chk_sobrebarriga" class="text-sm text-gray-700 cursor-pointer">Sobrebarriga rotas</label>
                        </li>
                        <li class="flex items-center gap-3">
                            <input type="checkbox" id="chk_hematomas" value="chartHematomas" checked
                                   class="w-4 h-4 accent-indigo-600 cursor-pointer">
                            <label for="chk_hematomas" class="text-sm text-gray-700 cursor-pointer">Hematomas</label>
                        </li>
                        <li class="flex items-center gap-3">
                            <input type="checkbox" id="chk_cortes" value="chartCortePiernas" checked
                                   class="w-4 h-4 accent-indigo-600 cursor-pointer">
                            <label for="chk_cortes" class="text-sm text-gray-700 cursor-pointer">Corte en piernas</label>
                        </li>
                        <li class="flex items-center gap-3">
                            <input type="checkbox" id="chk_cobertura" value="chartCoberturaGrasa" checked
                                   class="w-4 h-4 accent-indigo-600 cursor-pointer">
                            <label for="chk_cobertura" class="text-sm text-gray-700 cursor-pointer">Cobertura grasa</label>
                        </li>
                        <li class="flex items-center gap-3">
                            <input type="checkbox" id="chk_hallazgos" value="chartHallazgosNuevos" checked
                                   class="w-4 h-4 accent-indigo-600 cursor-pointer">
                            <label for="chk_hallazgos" class="text-sm text-gray-700 cursor-pointer">Hallazgos TC por tipo</label>
                        </li>
                    </ul>
                    <div class="flex gap-3 justify-end">
                        <button onclick="cerrarModalDescarga()"
                                class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                        <button onclick="descargarGraficasSeleccionadas()"
                                class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow transition">
                            Descargar
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800">Indicador de Sobrebarriga rotas</h3>
                    <div class="h-64">
                        <canvas id="chartSobrebarriga"></canvas>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800">Indicador de Hematomas</h3>
                    <div class="h-64">
                        <canvas id="chartHematomas"></canvas>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800">Indicador de Corte en Piernas</h3>
                    <div class="h-64">
                        <canvas id="chartCortePiernas"></canvas>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800">Indicador de Cobertura Grasa</h3>
                    <div class="h-64">
                        <canvas id="chartCoberturaGrasa"></canvas>
                    </div>
                </div>
            </div>

            {{-- Gráfico de Hallazgos Nuevos --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">Hallazgos TC por Tipo</h3>
                <div class="h-64">
                    <canvas id="chartHallazgosNuevos"></canvas>
                </div>
            </div>
            @else
                <div class="text-center py-12 bg-white rounded-lg shadow-sm">
                    <div class="text-gray-400 mb-4 text-4xl">📅</div>
                    <p class="text-gray-500">No hay indicadores registrados para este mes</p>
                </div>
            @endif

        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if(isset($chartData) && $indicadores->count() > 0)
            const chartData = @json($chartData);
            const hallazgosNuevos = @json($hallazgosNuevos);
            
            if (typeof window.initDashboardCharts === 'function') {
                window.initDashboardCharts(chartData);
            }

            // Gráfico de Hallazgos Nuevos
            Chart.register(ChartDataLabels);
            
            // Crear array de meta con el mismo largo que las fechas
            const metaArray = Array(hallazgosNuevos.fechas.length).fill(hallazgosNuevos.meta);
            
            new Chart(document.getElementById('chartHallazgosNuevos'), {
                type: 'line',
                data: {
                    labels: hallazgosNuevos.fechas,
                    datasets: [
                        {
                            label: 'Materia Fecal',
                            data: hallazgosNuevos['MATERIA FECAL'],
                            borderColor: '#FCD34D',
                            backgroundColor: 'rgba(252, 211, 77, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#FCD34D',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7
                        },
                        {
                            label: 'Contenido Ruminal',
                            data: hallazgosNuevos['CONTENIDO RUMINAL'],
                            borderColor: '#F97316',
                            backgroundColor: 'rgba(249, 115, 22, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#F97316',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7
                        },
                        {
                            label: 'Leche Visible',
                            data: hallazgosNuevos['LECHE VISIBLE'],
                            borderColor: '#3B82F6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#3B82F6',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7
                        },
                        {
                            label: 'META',
                            data: metaArray,
                            borderColor: '#EF4444',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            fill: false,
                            tension: 0,
                            pointRadius: 0,
                            pointHoverRadius: 0
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
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 13,
                                    weight: 'bold'
                                },
                                padding: 15,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            color: '#333',
                            font: { weight: 'bold', size: 11 },
                            formatter: function(value) {
                                return value > 0 ? value.toFixed(2) + '%' : '';
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                font: { size: 12 },
                                callback: function(value) {
                                    return value.toFixed(2) + '%';
                                }
                            }
                        },
                        x: {
                            ticks: {
                                font: { size: 12 },
                                maxRotation: 45,
                                minRotation: 0
                            }
                        }
                    }
                }
            });
            @endif
        });
    </script>
    @endpush

    <script>
        const chartTitulos = {
            chartSobrebarriga:    'Indicador de Sobrebarriga Rotas',
            chartHematomas:       'Indicador de Hematomas',
            chartCortePiernas:    'Indicador de Corte en Piernas',
            chartCoberturaGrasa:  'Indicador de Cobertura Grasa',
            chartHallazgosNuevos: 'Hallazgos TC por Tipo',
        };
        const chartNombresArchivo = {
            chartSobrebarriga:    'sobrebarriga-rotas',
            chartHematomas:       'hematomas',
            chartCortePiernas:    'corte-piernas',
            chartCoberturaGrasa:  'cobertura-grasa',
            chartHallazgosNuevos: 'hallazgos-tc',
        };

        function abrirModalDescarga() {
            window._pausarAutoRefresh = true;
            document.getElementById('modalDescarga').classList.remove('hidden');
        }

        function cerrarModalDescarga() {
            window._pausarAutoRefresh = false;
            document.getElementById('modalDescarga').classList.add('hidden');
        }

        function descargarGraficasSeleccionadas() {
            const checkboxes = document.querySelectorAll('#modalDescarga input[type=checkbox]:checked');

            if (checkboxes.length === 0) {
                alert('Selecciona al menos una gráfica.');
                return;
            }

            const TITULO_H   = 40;  // altura reservada para el título
            const PADDING    = 16;

            checkboxes.forEach(function(chk) {
                const canvas = document.getElementById(chk.value);
                if (!canvas) return;

                const titulo = chartTitulos[chk.value] || chk.value;

                // Canvas temporal con espacio extra para el título
                const tmpCanvas = document.createElement('canvas');
                tmpCanvas.width  = canvas.width;
                tmpCanvas.height = canvas.height + TITULO_H;
                const ctx = tmpCanvas.getContext('2d');

                // Fondo blanco
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, tmpCanvas.width, tmpCanvas.height);

                // Título
                ctx.fillStyle = '#1f2937';
                ctx.font = 'bold 16px sans-serif';
                ctx.textBaseline = 'middle';
                ctx.fillText(titulo, PADDING, TITULO_H / 2);

                // Gráfica
                ctx.drawImage(canvas, 0, TITULO_H);

                const link = document.createElement('a');
                link.href = tmpCanvas.toDataURL('image/png');
                link.download = (chartNombresArchivo[chk.value] || chk.value) + '.png';
                link.click();
            });

            cerrarModalDescarga();
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('modalDescarga').addEventListener('click', function(e) {
            if (e.target === this) cerrarModalDescarga();
        });
    </script>

    {{-- Auto-refresh cada 15 segundos (pausado mientras el modal está abierto) --}}
    <script>
        (function() {
            window._pausarAutoRefresh = false;
            setInterval(function() {
                if (!window._pausarAutoRefresh) {
                    window.location.reload();
                }
            }, 15000);
        })();
    </script>
</x-app-layout>
