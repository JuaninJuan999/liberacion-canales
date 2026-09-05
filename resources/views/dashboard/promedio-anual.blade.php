<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                        <img src="{{ asset('logo.png') }}" alt="Logo" class="h-6 sm:h-10 max-w-[40px] sm:max-w-[80px] object-contain flex-shrink-0">
                        <div class="min-w-0">
                            <h1 class="text-lg sm:text-2xl font-bold text-gray-900 truncate">Promedio del Año</h1>
                            <p class="text-gray-500 mt-1 text-xs sm:text-sm">Indicadores mensuales consolidados</p>
                        </div>
                    </div>
                    <a href="{{ route('dashboard.mensual', ['mes' => now()->month, 'anio' => $anio]) }}"
                       class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition text-xs sm:text-sm flex-shrink-0 text-center">
                        ← Volver al Dashboard Mensual
                    </a>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <form method="GET" action="{{ route('dashboard.mensual.promedio-anual') }}" class="flex flex-wrap items-center gap-3 sm:gap-4">
                        <label class="text-sm font-medium text-gray-700">Año:</label>
                        <select name="anio"
                                class="rounded-md border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600"
                                onchange="this.form.submit()">
                            @for($a = now()->year - 2; $a <= now()->year + 1; $a++)
                                <option value="{{ $a }}" {{ $a == $anio ? 'selected' : '' }}>{{ $a }}</option>
                            @endfor
                        </select>
                    </form>
                    @if(($acumulado['mes_limite'] ?? 0) > 0)
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('dashboard.mensual.promedio-anual.excel', ['anio' => $anio]) }}"
                               class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 3v12m0 0l3.5-3.5M12 15L8.5 11.5M12 3h4a2 2 0 012 2v1"/>
                                </svg>
                                Excel
                            </a>
                            <button type="button" id="btnDescargarPngAcumulado"
                                    class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg shadow transition hover:brightness-110"
                                    style="background-color:#2563eb;color:#fff;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Imagen PNG
                            </button>
                        </div>
                    @endif
                </div>
                @if(($acumulado['mes_limite'] ?? 0) > 0)
                    <p class="mt-3 text-xs text-gray-500">Clic en el nombre de un mes en la tabla para ocultarlo o mostrarlo. La columna Promedio se recalcula con los meses visibles.</p>
                @endif
            </div>

            @if(($acumulado['mes_limite'] ?? 0) === 0)
                <div class="bg-white shadow-sm sm:rounded-lg p-10 text-center text-gray-500">
                    No hay datos para mostrar en este año.
                </div>
            @else
                @php
                    $totAnual = $acumulado['totales'] ?? [];
                    $promPorClave = collect($acumulado['filas'] ?? [])->keyBy('key');
                    $fmtProm = fn (string $key): string => \App\Support\AcumuladoAnualLiberacion::formatoPorcentaje($promPorClave[$key]['promedio'] ?? null);
                @endphp

                <style>
                    @keyframes mensual-kpi-enter {
                        from { opacity: 0; transform: translateY(14px) scale(0.98); }
                        to { opacity: 1; transform: translateY(0) scale(1); }
                    }
                    .mensual-kpi-card {
                        animation: mensual-kpi-enter 0.55s cubic-bezier(0.22, 1, 0.36, 1) backwards;
                        transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
                    }
                    .mensual-kpi-card:hover {
                        transform: translateY(-4px) scale(1.02);
                        box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.12);
                    }
                    .mensual-kpi-grid > .mensual-kpi-card:nth-child(1) { animation-delay: 0.04s; }
                    .mensual-kpi-grid > .mensual-kpi-card:nth-child(2) { animation-delay: 0.08s; }
                    .mensual-kpi-grid > .mensual-kpi-card:nth-child(3) { animation-delay: 0.12s; }
                    .mensual-kpi-grid > .mensual-kpi-card:nth-child(4) { animation-delay: 0.16s; }
                    .mensual-kpi-grid > .mensual-kpi-card:nth-child(5) { animation-delay: 0.20s; }
                    .mensual-kpi-grid > .mensual-kpi-card:nth-child(6) { animation-delay: 0.24s; }
                    .mensual-kpi-grid > .mensual-kpi-card:nth-child(7) { animation-delay: 0.28s; }
                    .mensual-kpi-grid > .mensual-kpi-card:nth-child(8) { animation-delay: 0.32s; }
                    .mensual-kpi-grid > .mensual-kpi-card:nth-child(9) { animation-delay: 0.36s; }
                    @media (prefers-reduced-motion: reduce) {
                        .mensual-kpi-card { animation: none !important; }
                        .mensual-kpi-card:hover { transform: none; }
                    }
                </style>

                <div id="kpiAcumuladoAnual" class="mensual-kpi-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    <div class="mensual-kpi-card bg-blue-50/60 overflow-hidden shadow-sm sm:rounded-xl p-6 text-center border-2 border-blue-300 hover:border-blue-500">
                        <p class="text-sm text-blue-800/80 uppercase tracking-wide font-medium">Días Operados</p>
                        <p class="text-3xl font-bold text-blue-600 mt-1 tabular-nums" data-kpi-total="dias_operados">{{ number_format($totAnual['dias_operados'] ?? 0, 0, ',', '.') }}</p>
                        <p id="kpiMesesVisibles" class="text-xs text-blue-700/70 mt-2">{{ (int) ($acumulado['mes_limite'] ?? 0) }} {{ ((int) ($acumulado['mes_limite'] ?? 0) === 1) ? 'mes' : 'meses' }} · {{ $anio }}</p>
                    </div>

                    <div class="mensual-kpi-card bg-green-50/60 overflow-hidden shadow-sm sm:rounded-xl p-6 text-center border-2 border-green-300 hover:border-green-500">
                        <p class="text-sm text-green-800/80 uppercase tracking-wide font-medium">Total Animales</p>
                        <p class="text-3xl font-bold text-green-600 mt-1 tabular-nums" data-kpi-total="animales">{{ number_format($totAnual['animales'] ?? 0, 0, ',', '.') }}</p>
                    </div>

                    <div class="mensual-kpi-card bg-teal-50/60 overflow-hidden shadow-sm sm:rounded-xl p-6 text-center border-2 border-teal-300 hover:border-teal-500">
                        <p class="text-sm text-teal-800/80 uppercase tracking-wide font-medium">Total Medias Canales</p>
                        <p class="text-3xl font-bold text-teal-600 mt-1 tabular-nums" data-kpi-total="medias_canales">{{ number_format($totAnual['medias_canales'] ?? 0, 0, ',', '.') }}</p>
                    </div>

                    <div class="mensual-kpi-card bg-red-50/60 overflow-hidden shadow-sm sm:rounded-xl p-6 text-center border-2 border-red-300 hover:border-red-500">
                        <p class="text-sm text-red-800/80 uppercase tracking-wide font-medium">Total Hallazgos</p>
                        <p class="text-3xl font-bold text-red-600 mt-1 tabular-nums" data-kpi-total="hallazgos">{{ number_format($totAnual['hallazgos'] ?? 0, 0, ',', '.') }}</p>
                    </div>

                    <div class="mensual-kpi-card bg-orange-50/60 overflow-hidden shadow-sm sm:rounded-xl p-6 text-center border-2 border-orange-300 hover:border-orange-500">
                        <p class="text-sm text-orange-800/80 uppercase tracking-wide font-medium">Sobrebarriga Rotas</p>
                        <p class="text-3xl font-bold text-orange-600 mt-1 tabular-nums" data-kpi-total="sobrebarriga_rota">{{ number_format($totAnual['sobrebarriga_rota'] ?? 0, 0, ',', '.') }}</p>
                        <p class="text-xs sm:text-sm text-orange-900/70 mt-2">Promedio: <span class="font-semibold text-gray-800 tabular-nums" data-kpi-promedio="sobrebarriga_rota">{{ $fmtProm('sobrebarriga_rota') }}</span></p>
                    </div>

                    <div class="mensual-kpi-card bg-purple-50/60 overflow-hidden shadow-sm sm:rounded-xl p-6 text-center border-2 border-purple-300 hover:border-purple-500">
                        <p class="text-sm text-purple-800/80 uppercase tracking-wide font-medium">Hematomas</p>
                        <p class="text-3xl font-bold text-purple-600 mt-1 tabular-nums" data-kpi-total="hematomas">{{ number_format($totAnual['hematomas'] ?? 0, 0, ',', '.') }}</p>
                        <p class="text-xs sm:text-sm text-purple-900/70 mt-2">Promedio: <span class="font-semibold text-gray-800 tabular-nums" data-kpi-promedio="hematomas">{{ $fmtProm('hematomas') }}</span></p>
                    </div>

                    <div class="mensual-kpi-card bg-yellow-50/70 overflow-hidden shadow-sm sm:rounded-xl p-6 text-center border-2 border-yellow-400 hover:border-yellow-500">
                        <p class="text-sm text-yellow-900/80 uppercase tracking-wide font-medium">Cobertura Grasa</p>
                        <p class="text-3xl font-bold text-yellow-600 mt-1 tabular-nums" data-kpi-total="cobertura_grasa">{{ number_format($totAnual['cobertura_grasa'] ?? 0, 0, ',', '.') }}</p>
                        <p class="text-xs sm:text-sm text-yellow-900/70 mt-2">Promedio: <span class="font-semibold text-gray-800 tabular-nums" data-kpi-promedio="cobertura_grasa">{{ $fmtProm('cobertura_grasa') }}</span></p>
                    </div>

                    <div class="mensual-kpi-card bg-pink-50/60 overflow-hidden shadow-sm sm:rounded-xl p-6 text-center border-2 border-pink-300 hover:border-pink-500">
                        <p class="text-sm text-pink-800/80 uppercase tracking-wide font-medium">Cortes Piernas</p>
                        <p class="text-3xl font-bold text-pink-600 mt-1 tabular-nums" data-kpi-total="cortes_piernas">{{ number_format($totAnual['cortes_piernas'] ?? 0, 0, ',', '.') }}</p>
                        <p class="text-xs sm:text-sm text-pink-900/70 mt-2">Promedio: <span class="font-semibold text-gray-800 tabular-nums" data-kpi-promedio="cortes_piernas">{{ $fmtProm('cortes_piernas') }}</span></p>
                    </div>

                    <div class="mensual-kpi-card bg-amber-50/90 overflow-hidden shadow-sm sm:rounded-xl p-6 text-center border-2 border-amber-400 hover:border-amber-600">
                        <p class="text-sm text-amber-900/80 uppercase tracking-wide font-semibold">Acumulado del Año</p>
                        <p class="text-3xl font-bold text-amber-800 tabular-nums mt-1" data-kpi-promedio="acumulado_anual">{{ $fmtProm('total_hallazgos') }}</p>
                        <p class="text-xs sm:text-sm text-amber-900/70 mt-2">Promedio anual consolidado</p>
                    </div>
                </div>

                <script type="application/json" id="totalesPorMesData">@json($acumulado['totales_por_mes'] ?? [])</script>

                <div id="tablaAcumuladoWrap" class="bg-white shadow-sm sm:rounded-lg p-4 sm:p-6 overflow-x-auto border border-[#7ce8ad]/60">
                    <table id="tablaAcumuladoAnual" class="min-w-full border-2 border-gray-800 text-sm border-collapse">
                        <thead>
                            <tr>
                                <th id="tituloAcumuladoColspan" colspan="{{ count($acumulado['columnas_meses']) + 2 }}"
                                    class="border-2 border-gray-800 bg-[#7ce8ad] px-3 py-2.5 text-center font-bold text-gray-900 text-base sm:text-lg tracking-wide">
                                    ACUMULADO LIBERACION DE CANALES {{ $anio }}
                                </th>
                            </tr>
                            <tr>
                                <th class="border-2 border-gray-800 bg-[#7ce8ad] px-3 py-2 text-left font-bold min-w-[12rem] text-gray-900">Ítem</th>
                                @foreach($acumulado['columnas_meses'] as $col)
                                    <th data-mes-col="{{ $col['num'] }}"
                                        class="mes-col-header border-2 border-gray-800 bg-[#f9dff8] px-2 py-2 text-center font-bold uppercase whitespace-nowrap text-gray-900 cursor-pointer select-none hover:brightness-95 transition"
                                        title="Clic para ocultar o mostrar {{ $col['label'] }}">
                                        {{ $col['label'] }}
                                    </th>
                                @endforeach
                                <th class="border-2 border-gray-800 bg-[#f9dff8] px-3 py-2 text-center font-bold min-w-[6rem] text-gray-900">Promedio</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($acumulado['filas'] as $fila)
                                <tr class="fila-acumulado {{ !empty($fila['is_total']) ? 'fila-total bg-[#7ce8ad]/25' : 'bg-white' }}"
                                    data-fila-key="{{ $fila['key'] ?? '' }}"
                                    @if(!empty($fila['is_total'])) data-es-total="1" @endif>
                                    <td class="border-2 border-gray-800 px-3 py-2 font-semibold text-gray-900 {{ !empty($fila['is_total']) ? 'font-bold bg-[#7ce8ad]/40' : 'bg-[#7ce8ad]/15' }}">
                                        {{ $fila['label'] }}
                                    </td>
                                    @foreach($fila['valores'] as $idx => $valor)
                                        @php $mesNum = $acumulado['columnas_meses'][$idx]['num'] ?? ($idx + 1); @endphp
                                        <td data-mes-col="{{ $mesNum }}"
                                            data-valor="{{ $valor }}"
                                            class="mes-col-cell border-2 border-gray-800 px-2 py-2 text-center tabular-nums text-gray-900 {{ !empty($fila['is_total']) ? 'font-bold bg-[#f9dff8]/30' : 'bg-[#f9dff8]/10' }}">
                                            {{ \App\Support\AcumuladoAnualLiberacion::formatoPorcentaje($valor) }}
                                        </td>
                                    @endforeach
                                    <td data-promedio-cell
                                        data-promedio-original="{{ $fila['promedio'] ?? '' }}"
                                        class="border-2 border-gray-800 px-3 py-2 text-center tabular-nums font-bold text-gray-900 bg-[#f9dff8]">
                                        {{ \App\Support\AcumuladoAnualLiberacion::formatoPorcentaje($fila['promedio']) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div id="mesesOcultosPanel"
                     class="hidden bg-white shadow-sm sm:rounded-lg p-4 sm:p-5 border border-[#f9dff8]">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                        <p class="text-sm font-semibold text-gray-800">
                            Meses ocultos
                            <span class="font-normal text-gray-500">— clic en un mes para volver a mostrarlo</span>
                        </p>
                        <button type="button"
                                id="btnAlistarMeses"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg shadow transition hover:brightness-95 shrink-0"
                                style="background-color:#7ce8ad;color:#111827;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Alistar todos los meses
                        </button>
                    </div>
                    <div id="mesesOcultosLista" class="flex flex-wrap gap-2"></div>
                </div>
            @endif
        </div>
    </div>

    @if(($acumulado['mes_limite'] ?? 0) > 0)
        <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const hiddenMeses = new Set();
                const table = document.getElementById('tablaAcumuladoAnual');
                const wrap = document.getElementById('tablaAcumuladoWrap');
                const tituloTh = document.getElementById('tituloAcumuladoColspan');
                const mesesOcultosPanel = document.getElementById('mesesOcultosPanel');
                const mesesOcultosLista = document.getElementById('mesesOcultosLista');
                const btnAlistarMeses = document.getElementById('btnAlistarMeses');
                const mesLabels = {};
                const totalesPorMesEl = document.getElementById('totalesPorMesData');
                const totalesPorMes = totalesPorMesEl ? JSON.parse(totalesPorMesEl.textContent || '[]') : [];
                const anioKpi = {{ (int) $anio }};

                document.querySelectorAll('.mes-col-header').forEach(function (th) {
                    mesLabels[th.dataset.mesCol] = th.textContent.trim();
                });

                function formatNum(value) {
                    return Number(value || 0).toLocaleString('es-CO');
                }

                function formatPct(value) {
                    if (value === null || value === undefined || isNaN(value)) {
                        return '';
                    }
                    return Number(value).toFixed(2).replace('.', ',') + '%';
                }

                function visibleMesCount() {
                    return document.querySelectorAll('.mes-col-header:not(.mes-oculto)').length;
                }

                function updateTituloColspan() {
                    if (!tituloTh) return;
                    tituloTh.colSpan = visibleMesCount() + 2;
                }

                function updateKpiTotales() {
                    const visibles = totalesPorMes.filter(function (t) {
                        return !hiddenMeses.has(String(t.mes));
                    });

                    const keys = [
                        'dias_operados', 'animales', 'medias_canales', 'hallazgos',
                        'sobrebarriga_rota', 'hematomas', 'cobertura_grasa', 'cortes_piernas',
                    ];

                    keys.forEach(function (key) {
                        const total = visibles.reduce(function (acc, t) {
                            return acc + (Number(t[key]) || 0);
                        }, 0);
                        const el = document.querySelector('[data-kpi-total="' + key + '"]');
                        if (el) {
                            el.textContent = formatNum(total);
                        }
                    });

                    const mesesEl = document.getElementById('kpiMesesVisibles');
                    if (mesesEl) {
                        const n = visibleMesCount();
                        mesesEl.textContent = n + (n === 1 ? ' mes' : ' meses') + ' · ' + anioKpi;
                    }
                }

                function recalcPromedios() {
                    if (!table) return;
                    const filas = table.querySelectorAll('tbody tr.fila-acumulado:not([data-es-total="1"])');
                    const promediosCategorias = [];

                    filas.forEach(function (tr) {
                        const celdas = tr.querySelectorAll('.mes-col-cell:not(.mes-oculto)');
                        const valores = Array.from(celdas).map(function (td) {
                            return parseFloat(td.dataset.valor || '0');
                        });
                        const prom = valores.length
                            ? valores.reduce(function (a, b) { return a + b; }, 0) / valores.length
                            : 0;
                        promediosCategorias.push(prom);
                        const promCell = tr.querySelector('[data-promedio-cell]');
                        if (promCell) {
                            promCell.textContent = valores.length ? formatPct(prom) : '';
                        }
                        const filaKey = tr.dataset.filaKey;
                        if (filaKey) {
                            const kpiEl = document.querySelector('[data-kpi-promedio="' + filaKey + '"]');
                            if (kpiEl) {
                                kpiEl.textContent = valores.length ? formatPct(prom) : '';
                            }
                        }
                    });

                    const totalRow = table.querySelector('tbody tr[data-es-total="1"]');
                    if (totalRow) {
                        const sumProm = promediosCategorias.reduce(function (a, b) { return a + b; }, 0);
                        const promCell = totalRow.querySelector('[data-promedio-cell]');
                        if (promCell) {
                            promCell.textContent = sumProm > 0 ? formatPct(sumProm) : '';
                        }
                        const kpiAcum = document.querySelector('[data-kpi-promedio="acumulado_anual"]');
                        if (kpiAcum) {
                            kpiAcum.textContent = sumProm > 0 ? formatPct(sumProm) : '';
                        }
                    }
                }

                function updateMesesOcultosPanel() {
                    if (!mesesOcultosPanel || !mesesOcultosLista) return;

                    if (hiddenMeses.size === 0) {
                        mesesOcultosPanel.classList.add('hidden');
                        mesesOcultosLista.innerHTML = '';
                        return;
                    }

                    mesesOcultosPanel.classList.remove('hidden');
                    mesesOcultosLista.innerHTML = '';

                    Array.from(hiddenMeses)
                        .sort(function (a, b) { return Number(a) - Number(b); })
                        .forEach(function (mes) {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.dataset.mesCol = mes;
                            btn.className = 'mes-oculto-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border-2 border-gray-800 text-sm font-bold uppercase text-gray-900 cursor-pointer hover:brightness-95 transition';
                            btn.style.backgroundColor = '#f9dff8';
                            btn.title = 'Clic para mostrar ' + (mesLabels[mes] || mes);
                            btn.innerHTML = '<span>' + (mesLabels[mes] || mes) + '</span>'
                                + '<svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>';
                            btn.addEventListener('click', function () {
                                hiddenMeses.delete(mes);
                                aplicarVisibilidadMeses();
                            });
                            mesesOcultosLista.appendChild(btn);
                        });
                }

                function aplicarVisibilidadMeses() {
                    document.querySelectorAll('[data-mes-col]').forEach(function (el) {
                        const mes = el.dataset.mesCol;
                        const oculto = hiddenMeses.has(mes);
                        el.classList.toggle('mes-oculto', oculto);
                        if (el.classList.contains('mes-col-header')) {
                            el.classList.toggle('line-through', oculto);
                            el.classList.toggle('opacity-40', oculto);
                            el.title = oculto
                                ? 'Clic para mostrar ' + el.textContent.trim()
                                : 'Clic para ocultar ' + el.textContent.trim();
                        }
                        if (el.classList.contains('mes-col-cell') || el.classList.contains('mes-col-header')) {
                            el.classList.toggle('hidden', oculto);
                        }
                    });
                    updateTituloColspan();
                    recalcPromedios();
                    updateKpiTotales();
                    updateMesesOcultosPanel();
                }

                document.querySelectorAll('.mes-col-header').forEach(function (th) {
                    th.addEventListener('click', function () {
                        const mes = th.dataset.mesCol;
                        if (hiddenMeses.has(mes)) {
                            hiddenMeses.delete(mes);
                        } else {
                            hiddenMeses.add(mes);
                        }
                        aplicarVisibilidadMeses();
                    });
                });

                if (btnAlistarMeses) {
                    btnAlistarMeses.addEventListener('click', function () {
                        hiddenMeses.clear();
                        aplicarVisibilidadMeses();
                    });
                }

                const btnPng = document.getElementById('btnDescargarPngAcumulado');
                if (btnPng && wrap && typeof html2canvas !== 'undefined') {
                    btnPng.addEventListener('click', function () {
                        btnPng.disabled = true;
                        btnPng.classList.add('opacity-70');
                        html2canvas(wrap, {
                            scale: 2,
                            backgroundColor: '#ffffff',
                            useCORS: true,
                            logging: false,
                        }).then(function (canvas) {
                            const enlace = document.createElement('a');
                            enlace.download = 'acumulado-liberacion-canales-{{ $anio }}.png';
                            enlace.href = canvas.toDataURL('image/png');
                            enlace.click();
                        }).catch(function (err) {
                            console.error(err);
                            alert('No se pudo generar la imagen. Intente de nuevo.');
                        }).finally(function () {
                            btnPng.disabled = false;
                            btnPng.classList.remove('opacity-70');
                        });
                    });
                }
            });
        </script>
    @endif
</x-app-layout>
