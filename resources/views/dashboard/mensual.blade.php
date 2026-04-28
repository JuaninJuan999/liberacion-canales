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
            
            {{-- Selector de Mes/Año + descarga Excel --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <form method="GET" action="{{ route('dashboard.mensual') }}" class="flex flex-wrap items-center gap-3 sm:gap-4">
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
                    <button type="button" onclick="abrirModalDescarga()"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow transition w-full sm:w-auto shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 3v12m0 0l3.5-3.5M12 15L8.5 11.5M12 3h4a2 2 0 012 2v1"/>
                        </svg>
                        Descargar gráficas (Excel)
                    </button>
                </div>
            </div>

            {{-- Modal: exportar datos de gráficas a Excel (mismo mes/año del filtro) --}}
            <form id="formGraficasExcel" method="GET" action="{{ route('dashboard.mensual.graficas-excel') }}"
                  onsubmit="return validarHojasExcel(event)">
                <input type="hidden" name="mes" value="{{ $mes }}">
                <input type="hidden" name="anio" value="{{ $anio }}">
                <div id="modalDescarga" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                    <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm p-6" onclick="event.stopPropagation()">
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Descargar en Excel</h3>
                        <p class="text-sm text-gray-500 mb-4">Hojas con los datos de las gráficas del mes <span class="font-medium text-gray-700">{{ \Carbon\Carbon::create($anio, $mes, 1)->locale('es')->isoFormat('MMMM YYYY') }}</span></p>
                        <ul class="space-y-3 mb-6">
                            <li class="flex items-center gap-3">
                                <input type="checkbox" id="hoja_resumen" name="hojas[]" value="resumen" checked
                                       class="w-4 h-4 accent-emerald-600 cursor-pointer">
                                <label for="hoja_resumen" class="text-sm text-gray-700 cursor-pointer">Resumen (KPIs del mes)</label>
                            </li>
                            <li class="flex items-center gap-3">
                                <input type="checkbox" id="hoja_sobrebarriga" name="hojas[]" value="sobrebarriga" checked
                                       class="w-4 h-4 accent-emerald-600 cursor-pointer">
                                <label for="hoja_sobrebarriga" class="text-sm text-gray-700 cursor-pointer">Sobrebarriga rotas (tendencia diaria)</label>
                            </li>
                            <li class="flex items-center gap-3">
                                <input type="checkbox" id="hoja_hematomas" name="hojas[]" value="hematomas" checked
                                       class="w-4 h-4 accent-emerald-600 cursor-pointer">
                                <label for="hoja_hematomas" class="text-sm text-gray-700 cursor-pointer">Hematomas</label>
                            </li>
                            <li class="flex items-center gap-3">
                                <input type="checkbox" id="hoja_cortes" name="hojas[]" value="cortes_piernas" checked
                                       class="w-4 h-4 accent-emerald-600 cursor-pointer">
                                <label for="hoja_cortes" class="text-sm text-gray-700 cursor-pointer">Corte en piernas</label>
                            </li>
                            <li class="flex items-center gap-3">
                                <input type="checkbox" id="hoja_cobertura" name="hojas[]" value="cobertura_grasa" checked
                                       class="w-4 h-4 accent-emerald-600 cursor-pointer">
                                <label for="hoja_cobertura" class="text-sm text-gray-700 cursor-pointer">Cobertura grasa</label>
                            </li>
                            <li class="flex items-center gap-3">
                                <input type="checkbox" id="hoja_hallazgos_tc" name="hojas[]" value="hallazgos_tc" checked
                                       class="w-4 h-4 accent-emerald-600 cursor-pointer">
                                <label for="hoja_hallazgos_tc" class="text-sm text-gray-700 cursor-pointer">Hallazgos TC por tipo</label>
                            </li>
                            <li class="flex items-center gap-3">
                                <input type="checkbox" id="hoja_seguimiento" name="hojas[]" value="seguimiento" checked
                                       class="w-4 h-4 accent-emerald-600 cursor-pointer">
                                <label for="hoja_seguimiento" class="text-sm text-gray-700 cursor-pointer">Seguimiento (suma del mes / combo)</label>
                            </li>
                        </ul>
                        <div class="flex gap-3 justify-end">
                            <button type="button" onclick="cerrarModalDescarga()"
                                    class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg shadow transition">
                                Descargar .xlsx
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            @php
                $sSeg = $seguimientoSemanal ?? [];
                $pc = $sSeg['por_clave'] ?? [];
                $pPct = fn (string $k): string => \App\Support\PorcentajeVista::mediaCanalFormato2((float) ($pc[$k]['pct_media'] ?? 0));
            @endphp
            {{-- Tarjetas Resumen del Mes --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-sm text-gray-500 uppercase">Días Operados</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $totales['dias_operados'] }}</p>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-sm text-gray-500 uppercase">Total Animales</p>
                    <p class="text-3xl font-bold text-green-600">{{ number_format($totales['animales'], 0, ',', '.') }}</p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-sm text-gray-500 uppercase">Total Medias Canales</p>
                    <p class="text-3xl font-bold text-teal-600">{{ number_format($totales['medias_canales'], 0, ',', '.') }}</p>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-sm text-gray-500 uppercase">Total Hallazgos</p>
                    <p class="text-3xl font-bold text-red-600">{{ number_format($totales['hallazgos'], 0, ',', '.') }}</p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-sm text-gray-500 uppercase">Sobrebarriga Rotas</p>
                    <p class="text-3xl font-bold text-orange-600">{{ number_format($totales['sobrebarriga_rotas'], 0, ',', '.') }}</p>
                    <p class="text-xs sm:text-sm text-gray-500 mt-2">Promedio: <span class="font-semibold text-gray-800 tabular-nums">{{ $pPct('sobrebarriga_rota') }}</span></p>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-sm text-gray-500 uppercase">Hematomas</p>
                    <p class="text-3xl font-bold text-purple-600">{{ number_format($totales['hematomas'], 0, ',', '.') }}</p>
                    <p class="text-xs sm:text-sm text-gray-500 mt-2">Promedio: <span class="font-semibold text-gray-800 tabular-nums">{{ $pPct('hematomas') }}</span></p>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-sm text-gray-500 uppercase">Cobertura Grasa</p>
                    <p class="text-3xl font-bold text-yellow-600">{{ number_format($totales['cobertura'], 0, ',', '.') }}</p>
                    <p class="text-xs sm:text-sm text-gray-500 mt-2">Promedio: <span class="font-semibold text-gray-800 tabular-nums">{{ $pPct('cobertura_grasa') }}</span></p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-sm text-gray-500 uppercase">Cortes Piernas</p>
                    <p class="text-3xl font-bold text-pink-600">{{ number_format($totales['cortes_piernas'], 0, ',', '.') }}</p>
                    <p class="text-xs sm:text-sm text-gray-500 mt-2">Promedio: <span class="font-semibold text-gray-800 tabular-nums">{{ $pPct('cortes_piernas') }}</span></p>
                </div>

                <div class="bg-amber-50/90 overflow-hidden shadow-sm sm:rounded-lg p-6 text-center border border-amber-200/80">
                    <p class="text-sm text-amber-900/80 uppercase">Acumulado del mes</p>
                    <p class="text-3xl font-bold text-amber-800 tabular-nums">{{ \App\Support\PorcentajeVista::mediaCanalFormato2((float) ($sSeg['acumulado_pct_media'] ?? 0)) }}</p>
                </div>
            </div>

            {{-- Gráficos de Tendencia --}}
            @if($indicadores->count() > 0)

            @isset($seguimientoSemanal)
            {{-- Gráfica seguimiento (porcentajes en tarjetas superiores) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="px-4 sm:px-6 py-4 border-b border-indigo-100 bg-gradient-to-r from-indigo-50 to-slate-50">
                    <h2 class="text-lg font-bold text-gray-900">Seguimiento Mensual</h2>
                </div>
                <div class="p-4 sm:px-6 sm:pt-2 sm:pb-6">
                    <div class="h-80 max-w-4xl">
                        <canvas id="chartSeguimientoSemanal" style="min-height: 16rem;"></canvas>
                    </div>
                </div>
            </div>
            @endisset

            @isset($seguimientoSemanalLinea)
            <div id="seguimiento-semanal-linea" class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 scroll-mt-4">
                <div class="px-4 sm:px-6 py-4 border-b border-sky-100 bg-gradient-to-r from-sky-50 to-slate-50">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <h2 class="text-lg font-bold text-gray-900">Seguimiento semanal</h2>
                        <form id="form-semana-iso" method="GET" action="{{ route('dashboard.mensual') }}" class="flex flex-wrap items-center gap-3 text-sm">
                            <input type="hidden" name="mes" value="{{ $mes }}">
                            <input type="hidden" name="anio" value="{{ $anio }}">
                            <label for="semana_iso" class="text-gray-600 font-medium">Semana ISO</label>
                            <select id="semana_iso" name="semana_iso"
                                    class="rounded-md border-gray-300 shadow-sm text-sm min-w-[14rem] focus:border-sky-500 focus:ring-sky-500">
                                @foreach($seguimientoSemanalLinea['semanas_opciones'] as $opt)
                                    <option value="{{ $opt['key'] }}"
                                            @selected(($seguimientoSemanalLinea['semana_iso'] ?? '') === $opt['key'])>
                                        {{ $opt['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <label class="inline-flex items-center gap-2 cursor-pointer text-gray-700 select-none whitespace-nowrap">
                                <input type="checkbox"
                                       id="incluir_domingo"
                                       name="incluir_domingo"
                                       value="1"
                                       class="rounded border-gray-300 text-sky-600 shadow-sm focus:ring-sky-500"
                                       @checked(!empty($seguimientoSemanalLinea['incluir_domingo']))
                                       onchange="try { sessionStorage.setItem('liberacion_mensual_semana_scroll', String(window.scrollY || 0)); } catch(e) {} this.form.submit();">
                                <span>Incluir domingo</span>
                            </label>
                        </form>
                    </div>
                </div>
                <div class="p-4 sm:px-6 sm:pt-2 sm:pb-6">
                    <div class="mb-3 flex flex-wrap items-baseline gap-x-3 gap-y-1">
                        <span class="text-sm font-semibold text-gray-700">Promedio Total de la Semana:</span>
                        <span class="text-xl font-bold text-sky-800 tabular-nums tracking-tight">
                            {{ number_format((float) ($seguimientoSemanalLinea['total_acumulado_promedios'] ?? 0), 2, ',', '.') }}&nbsp;%
                        </span>
                    </div>
                    <div class="h-80 max-w-5xl">
                        <canvas id="chartSeguimientoSemanalLinea" style="min-height: 18rem;"></canvas>
                    </div>
                </div>
            </div>
            @endisset

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
    <script>
        (function () {
            const key = 'liberacion_mensual_semana_scroll';
            var savedY = null;
            try {
                savedY = sessionStorage.getItem(key);
            } catch (e) {}
            if (savedY === null) {
                return;
            }
            try {
                sessionStorage.removeItem(key);
            } catch (e) {}
            const y = parseInt(savedY, 10);
            if (isNaN(y) || y < 0) {
                return;
            }
            function applyScroll() {
                window.scrollTo(0, y);
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', applyScroll);
            } else {
                applyScroll();
            }
            requestAnimationFrame(function () {
                requestAnimationFrame(applyScroll);
            });
            window.addEventListener('load', function () {
                window.scrollTo(0, y);
            }, { once: true });
        })();
    </script>
    <script>
        (function () {
            function bindSemanaForm() {
                const form = document.getElementById('form-semana-iso');
                const sel = document.getElementById('semana_iso');
                if (!form || !sel) {
                    return;
                }
                sel.addEventListener('change', function () {
                    try {
                        sessionStorage.setItem('liberacion_mensual_semana_scroll', String(window.scrollY || 0));
                    } catch (e) {}
                    form.submit();
                });
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bindSemanaForm);
            } else {
                bindSemanaForm();
            }
        })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Chart !== 'undefined' && typeof ChartDataLabels !== 'undefined') {
                Chart.register(ChartDataLabels);
            }

            @isset($seguimientoSemanal['chart_combo'])
            (function () {
                const combo = @json($seguimientoSemanal['chart_combo']);
                const el = document.getElementById('chartSeguimientoSemanal');
                if (!el || !combo) {
                    return;
                }
                const ac = Number(combo.acumulado_bar) || 0;
                const res = combo.resultado_bars || [];
                const meta = combo.meta_line || [];
                const barAcum = [ac, null, null, null, null];
                const barRes = [null, res[0] ?? 0, res[1] ?? 0, res[2] ?? 0, res[3] ?? 0];
                new Chart(el, {
                    data: {
                        labels: combo.labels,
                        datasets: [
                            {
                                type: 'bar',
                                label: 'Σ acumulado',
                                data: barAcum,
                                backgroundColor: 'rgba(220, 38, 38, 0.9)',
                                borderColor: '#B91C1C',
                                borderWidth: 1,
                                order: 2,
                            },
                            {
                                type: 'bar',
                                label: 'Resultado',
                                data: barRes,
                                backgroundColor: 'rgba(22, 163, 74, 0.9)',
                                borderColor: '#15803D',
                                borderWidth: 1,
                                order: 2,
                            },
                            {
                                type: 'line',
                                label: 'META',
                                data: meta,
                                borderColor: '#2563EB',
                                backgroundColor: 'transparent',
                                borderWidth: 2,
                                fill: false,
                                tension: 0.1,
                                pointRadius: 4,
                                pointBackgroundColor: '#2563EB',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 1,
                                order: 0,
                            },
                        ],
                    },
                    type: 'bar',
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: { padding: { top: 20 } },
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            title: {
                                display: true,
                                text: combo.titulo || '',
                                font: { size: 15, weight: '600' },
                            },
                            legend: { position: 'bottom' },
                            datalabels: {
                                color: '#1f2937',
                                font: { weight: '600', size: 10 },
                                anchor: 'end',
                                align: 'end',
                                offset: 2,
                                formatter: function (value) {
                                    if (value === null || value === undefined || (typeof value === 'number' && isNaN(value))) {
                                        return '';
                                    }
                                    return Number(value).toFixed(2) + '%';
                                },
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (ctx) {
                                        const y = ctx.parsed.y;
                                        if (y === null || (typeof y === 'number' && isNaN(y))) {
                                            return '';
                                        }
                                        return (ctx.dataset.label || '') + ': ' + Number(y).toFixed(2) + '%';
                                    },
                                },
                            },
                        },
                        scales: {
                            y: {
                                min: 0,
                                max: 4.5,
                                title: { display: true, text: '%' },
                                ticks: {
                                    stepSize: 0.5,
                                    callback: function (v) {
                                        return Number(v).toFixed(2) + '%';
                                    },
                                },
                            },
                            x: { ticks: { maxRotation: 40, minRotation: 0, font: { size: 10 } } },
                        },
                    },
                });
            })();
            @endisset

            @isset($seguimientoSemanalLinea)
            (function () {
                const sl = @json($seguimientoSemanalLinea);
                const el = document.getElementById('chartSeguimientoSemanalLinea');
                if (!el || !sl || !sl.labels) {
                    return;
                }
                function hexToRgba(color, alpha) {
                    if (!color || typeof color !== 'string' || color[0] !== '#') {
                        return 'rgba(100, 100, 100, ' + alpha + ')';
                    }
                    const h = color.slice(1);
                    if (h.length !== 6) {
                        return 'rgba(100, 100, 100, ' + alpha + ')';
                    }
                    const r = parseInt(h.slice(0, 2), 16);
                    const g = parseInt(h.slice(2, 4), 16);
                    const b = parseInt(h.slice(4, 6), 16);
                    return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + alpha + ')';
                }
                function roundRectPath(ctx, rx, ry, rw, rh, rad) {
                    const rr = Math.min(rad, rw / 2, rh / 2);
                    ctx.beginPath();
                    ctx.moveTo(rx + rr, ry);
                    ctx.arcTo(rx + rw, ry, rx + rw, ry + rh, rr);
                    ctx.arcTo(rx + rw, ry + rh, rx, ry + rh, rr);
                    ctx.arcTo(rx, ry + rh, rx, ry, rr);
                    ctx.arcTo(rx, ry, rx + rw, ry, rr);
                    ctx.closePath();
                }
                /**
                 * Abanico horizontal por serie + trazo en L: cada % queda alineado con su pico
                 * (sin apilar todo en la misma X, que confundía el orden respecto al valor).
                 */
                const seguimientoSemanalCallouts = {
                    id: 'seguimientoSemanalCallouts',
                    afterDatasetsDraw: function (chart) {
                        const ctx = chart.ctx;
                        const chartArea = chart.chartArea;
                        if (!chartArea) {
                            return;
                        }
                        const nDatasets = chart.data.datasets.length;
                        const centerOff = nDatasets > 1 ? (nDatasets - 1) / 2 : 0;
                        const toDraw = [];
                        chart.data.datasets.forEach(function (dataset, datasetIndex) {
                            const meta = chart.getDatasetMeta(datasetIndex);
                            if (meta.hidden === true) {
                                return;
                            }
                            const raw = dataset.data;
                            for (let i = 0; i < raw.length; i++) {
                                const v = raw[i];
                                if (v === null || v === undefined || (typeof v === 'number' && isNaN(v))) {
                                    continue;
                                }
                                const point = meta.data[i];
                                if (!point || point.skip) {
                                    continue;
                                }
                                const p = point.getProps(['x', 'y'], true);
                                toDraw.push({
                                    x: p.x,
                                    y: p.y,
                                    text: Number(v).toFixed(2) + '%',
                                    color: dataset.borderColor || '#6b7280',
                                    datasetIndex: datasetIndex,
                                });
                            }
                        });
                        ctx.save();
                        ctx.font = '700 11px system-ui, -apple-system, "Segoe UI", sans-serif';
                        const padX = 7;
                        const padY = 3;
                        const lineHeight = 14;
                        const boxH = lineHeight + padY * 2;
                        const baseLift = 20;
                        const spread = nDatasets >= 2 ? 38 : 0;
                        const items = toDraw.map(function (it) {
                            const w = ctx.measureText(it.text).width + padX * 2;
                            const offsetX = (it.datasetIndex - centerOff) * spread;
                            let xLabel = it.x + offsetX;
                            xLabel = Math.max(
                                chartArea.left + w / 2 + 2,
                                Math.min(chartArea.right - w / 2 - 2, xLabel)
                            );
                            let labelCy = it.y - baseLift - boxH / 2;
                            if (labelCy - boxH / 2 < chartArea.top + 2) {
                                labelCy = chartArea.top + 2 + boxH / 2;
                            }
                            return {
                                x: it.x,
                                y: it.y,
                                xLabel: xLabel,
                                labelCy: labelCy,
                                text: it.text,
                                color: it.color,
                                w: w,
                                h: boxH,
                            };
                        });
                        items.forEach(function (it) {
                            const h = it.h;
                            const boxTop = it.labelCy - h / 2;
                            const boxBottom = it.labelCy + h / 2;
                            const boxLeft = it.xLabel - it.w / 2;
                            ctx.strokeStyle = hexToRgba(it.color, 0.7);
                            ctx.lineWidth = 1.15;
                            ctx.setLineDash([]);
                            ctx.lineCap = 'round';
                            ctx.lineJoin = 'round';
                            ctx.beginPath();
                            ctx.moveTo(it.x, it.y);
                            ctx.lineTo(it.xLabel, it.y);
                            ctx.lineTo(it.xLabel, boxBottom);
                            ctx.stroke();
                        });
                        items.forEach(function (it) {
                            const h = it.h;
                            const boxTop = it.labelCy - h / 2;
                            const boxLeft = it.xLabel - it.w / 2;
                            ctx.fillStyle = 'rgba(255, 255, 255, 0.98)';
                            ctx.strokeStyle = it.color;
                            ctx.lineWidth = 1.5;
                            roundRectPath(ctx, boxLeft, boxTop, it.w, h, 6);
                            ctx.fill();
                            ctx.stroke();
                            ctx.fillStyle = it.color;
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            ctx.fillText(it.text, it.xLabel, it.labelCy);
                        });
                        ctx.restore();
                    },
                };
                const datasets = (sl.datasets || []).map(function (ds) {
                    const c = ds.borderColor || '#6b7280';
                    return {
                        label: ds.label,
                        data: ds.data,
                        borderColor: c,
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [5, 4],
                        pointRadius: 3.5,
                        pointHoverRadius: 5,
                        pointBackgroundColor: c,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 1.5,
                        tension: 0.12,
                        spanGaps: false,
                    };
                });
                new Chart(el, {
                    type: 'line',
                    data: { labels: sl.labels, datasets: datasets },
                    plugins: [seguimientoSemanalCallouts],
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: {
                            padding: {
                                top: 72,
                                right: 28,
                                left: 28,
                                bottom: 8,
                            },
                        },
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            title: {
                                display: true,
                                text: sl.titulo || '',
                                font: { size: 15, weight: '600' },
                            },
                            legend: {
                                position: 'bottom',
                                labels: { usePointStyle: true, padding: 12, font: { size: 11 } },
                            },
                            datalabels: {
                                display: false,
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (ctx) {
                                        const py = ctx.parsed.y;
                                        if (py === null || (typeof py === 'number' && isNaN(py))) {
                                            return '';
                                        }
                                        return (ctx.dataset.label || '') + ': ' + Number(py).toFixed(2) + '%';
                                    },
                                },
                            },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grace: '10%',
                                ticks: {
                                    callback: function (v) {
                                        return Number(v).toFixed(2) + '%';
                                    },
                                },
                            },
                            x: { ticks: { maxRotation: 0, minRotation: 0, font: { size: 11 } } },
                        },
                    },
                });
            })();
            @endisset

            @if(isset($chartData) && $indicadores->count() > 0)
            const chartData = @json($chartData);
            const hallazgosNuevos = @json($hallazgosNuevos);
            
            if (typeof window.initDashboardCharts === 'function') {
                window.initDashboardCharts(chartData);
            }

            // Gráfico de Hallazgos Nuevos
            
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
                    layout: {
                        padding: {
                            top: 48,
                            right: 8,
                            left: 4,
                        },
                    },
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
                            display: function (context) {
                                if (context.dataset.label === 'META') {
                                    return false;
                                }
                                return context.parsed.y > 0;
                            },
                            anchor: 'center',
                            align: 'top',
                            offset: function (context) {
                                return 8 + context.datasetIndex * 15;
                            },
                            color: '#1f2937',
                            backgroundColor: 'rgba(255,255,255,0.92)',
                            borderColor: 'rgba(0,0,0,0.06)',
                            borderWidth: 1,
                            borderRadius: 4,
                            padding: { top: 2, right: 4, bottom: 2, left: 4 },
                            font: { weight: '600', size: 9 },
                            formatter: function (value) {
                                return value > 0 ? Number(value).toFixed(2) + '%' : '';
                            },
                            clip: false,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grace: '12%',
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
                                minRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 14,
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
        function abrirModalDescarga() {
            window._pausarAutoRefresh = true;
            const el = document.getElementById('modalDescarga');
            if (el) el.classList.remove('hidden');
        }

        function cerrarModalDescarga() {
            window._pausarAutoRefresh = false;
            const el = document.getElementById('modalDescarga');
            if (el) el.classList.add('hidden');
        }

        function validarHojasExcel(e) {
            const n = document.querySelectorAll('#formGraficasExcel input[name="hojas[]"]:checked').length;
            if (n === 0) {
                if (e && e.preventDefault) e.preventDefault();
                alert('Selecciona al menos una hoja para exportar.');
                return false;
            }
            cerrarModalDescarga();
            return true;
        }

        (function () {
            const m = document.getElementById('modalDescarga');
            if (m) {
                m.addEventListener('click', function (e) {
                    if (e.target === this) cerrarModalDescarga();
                });
            }
        })();
    </script>

    {{-- Auto-refresh cada 40 segundos (pausado mientras el modal está abierto) --}}
    <script>
        (function() {
            window._pausarAutoRefresh = false;
            setInterval(function() {
                if (!window._pausarAutoRefresh) {
                    window.location.reload();
                }
            }, 40000);
        })();
    </script>
</x-app-layout>
