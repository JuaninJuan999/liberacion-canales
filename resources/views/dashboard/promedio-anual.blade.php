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
                            <button type="button" id="btnDescargarPngResumen"
                                    class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg shadow transition hover:brightness-110"
                                    style="background-color:#2563eb;color:#fff;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                PNG Resumen
                            </button>
                            <button type="button" id="btnDescargarPngTabla"
                                    class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg shadow transition hover:brightness-110"
                                    style="background-color:#7c3aed;color:#fff;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                PNG Tabla
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
                    #kpiExportFrame {
                        overflow: hidden;
                        border: 3px solid #7ce8ad;
                        border-radius: 20px;
                        box-shadow: 0 12px 40px rgba(124, 232, 173, 0.28), 0 4px 16px rgba(0, 0, 0, 0.06);
                        background: #ffffff;
                    }
                    #kpiExportHeader {
                        display: flex;
                        align-items: center;
                        gap: 1rem;
                        padding: 1.25rem 1.5rem;
                        background: linear-gradient(135deg, #7ce8ad 0%, #a8f0c8 45%, #f9dff8 100%);
                        border-bottom: 2px solid rgba(255, 255, 255, 0.65);
                    }
                    @media (min-width: 640px) {
                        #kpiExportHeader { gap: 1.25rem; padding: 1.375rem 1.75rem; }
                    }
                    #kpiExportHeader .kpi-export-logo,
                    #kpiExportHeader .kpi-export-eyebrow {
                        display: none;
                    }
                    #kpiExportHeader .kpi-export-title {
                        margin-top: 0;
                    }
                    .kpi-export-logo {
                        height: 2.75rem;
                        width: auto;
                        max-width: 5.5rem;
                        object-fit: contain;
                        filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.12));
                        flex-shrink: 0;
                    }
                    @media (min-width: 640px) {
                        .kpi-export-logo { height: 3.25rem; }
                    }
                    .kpi-export-titles { flex: 1; min-width: 0; }
                    .kpi-export-eyebrow {
                        margin: 0;
                        font-size: 0.6875rem;
                        font-weight: 600;
                        letter-spacing: 0.12em;
                        text-transform: uppercase;
                        color: rgba(17, 24, 39, 0.65);
                    }
                    .kpi-export-title {
                        margin: 0.25rem 0 0;
                        font-size: 1.125rem;
                        font-weight: 800;
                        color: #111827;
                        line-height: 1.2;
                    }
                    @media (min-width: 640px) {
                        .kpi-export-title { font-size: 1.375rem; }
                    }
                    .kpi-export-badge {
                        flex-shrink: 0;
                        padding: 0.5rem 1rem;
                        border-radius: 9999px;
                        background: rgba(255, 255, 255, 0.82);
                        border: 1px solid rgba(255, 255, 255, 0.95);
                        font-size: 0.8125rem;
                        font-weight: 700;
                        color: #065f46;
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
                    }
                    #kpiAcumuladoAnual {
                        display: flex;
                        flex-direction: column;
                        gap: 1rem;
                        padding: 1.25rem 1rem 1.5rem;
                        background: linear-gradient(180deg, #fdf6fc 0%, #ffffff 120px);
                    }
                    @media (min-width: 640px) {
                        #kpiAcumuladoAnual { gap: 1.125rem; padding: 1.5rem 1.75rem 1.75rem; }
                    }
                    .kpi-export-row {
                        display: grid;
                        gap: 1rem;
                    }
                    .kpi-export-row-5 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
                    .kpi-export-row-4 {
                        grid-template-columns: repeat(1, minmax(0, 1fr));
                        width: 100%;
                    }
                    @media (min-width: 640px) {
                        .kpi-export-row-5 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
                        .kpi-export-row-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
                    }
                    @media (min-width: 1024px) {
                        .kpi-export-row-5 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
                        .kpi-export-row-4 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
                    }
                    @media (min-width: 1280px) {
                        .kpi-export-row-5 { grid-template-columns: repeat(5, minmax(0, 1fr)); }
                        .kpi-export-row-4 {
                            grid-template-columns: repeat(4, minmax(0, 1fr));
                            width: calc(80% + 12px);
                            margin-left: auto;
                            margin-right: auto;
                        }
                    }
                    .acumulado-kpi-card {
                        --kpi-accent: #7ce8ad;
                        min-height: 7.375rem;
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                        align-items: center;
                        text-align: center;
                        padding: 1.125rem 0.875rem;
                        border-radius: 0.875rem;
                        border: 1px solid #e8ecf0;
                        border-top: 4px solid var(--kpi-accent);
                        background: #ffffff;
                        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.06);
                        transition: transform 0.25s ease, box-shadow 0.25s ease;
                    }
                    .acumulado-kpi-card:hover {
                        transform: translateY(-3px);
                        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.1);
                    }
                    .acumulado-kpi-card--highlight {
                        border: 2px solid #7ce8ad;
                        border-top-width: 4px;
                        border-top-color: #7ce8ad;
                        background: linear-gradient(160deg, rgba(124, 232, 173, 0.22) 0%, rgba(249, 223, 248, 0.35) 100%);
                        box-shadow: 0 6px 20px rgba(124, 232, 173, 0.25);
                    }
                    .acumulado-kpi-card--highlight:hover {
                        box-shadow: 0 10px 28px rgba(124, 232, 173, 0.32);
                    }
                    .acumulado-kpi-label {
                        margin: 0;
                        font-size: 0.625rem;
                        font-weight: 700;
                        letter-spacing: 0.08em;
                        text-transform: uppercase;
                        color: #6b7280;
                    }
                    .acumulado-kpi-value {
                        margin: 0.5rem 0 0;
                        font-size: 1.75rem;
                        font-weight: 800;
                        line-height: 1.15;
                        color: var(--kpi-accent);
                    }
                    .acumulado-kpi-card--highlight .acumulado-kpi-value { color: #065f46; }
                    .acumulado-kpi-sub {
                        margin: 0.5rem 0 0;
                        font-size: 0.6875rem;
                        color: #6b7280;
                    }
                    .acumulado-kpi-sub span { color: #374151; font-weight: 700; }
                    @media (prefers-reduced-motion: reduce) {
                        .acumulado-kpi-card { transition: none; }
                        .acumulado-kpi-card:hover { transform: none; }
                    }
                </style>

                <div id="exportAcumuladoWrap" class="space-y-6">
                <div id="kpiExportFrame">
                <div id="kpiExportHeader">
                    <img src="{{ asset('logo.png') }}" alt="Colbeef" class="kpi-export-logo">
                    <div class="kpi-export-titles">
                        <p class="kpi-export-eyebrow">Liberación de Canales · Colbeef</p>
                        <h2 class="kpi-export-title">Resumen acumulado {{ $anio }}</h2>
                    </div>
                    <span class="kpi-export-badge" id="kpiExportBadgeMeses">{{ (int) ($acumulado['mes_limite'] ?? 0) }} meses</span>
                </div>
                <div id="kpiAcumuladoAnual">
                    <div class="kpi-export-row kpi-export-row-5">
                    <div class="acumulado-kpi-card" style="--kpi-accent:#2563eb">
                        <p class="acumulado-kpi-label">Días Operados</p>
                        <p class="acumulado-kpi-value tabular-nums" data-kpi-total="dias_operados">{{ number_format($totAnual['dias_operados'] ?? 0, 0, ',', '.') }}</p>
                        <p class="acumulado-kpi-sub" id="kpiMesesVisibles">{{ (int) ($acumulado['mes_limite'] ?? 0) }} {{ ((int) ($acumulado['mes_limite'] ?? 0) === 1) ? 'mes' : 'meses' }} · {{ $anio }}</p>
                    </div>

                    <div class="acumulado-kpi-card" style="--kpi-accent:#16a34a">
                        <p class="acumulado-kpi-label">Total Animales</p>
                        <p class="acumulado-kpi-value tabular-nums" data-kpi-total="animales">{{ number_format($totAnual['animales'] ?? 0, 0, ',', '.') }}</p>
                    </div>

                    <div class="acumulado-kpi-card" style="--kpi-accent:#0d9488">
                        <p class="acumulado-kpi-label">Total Medias Canales</p>
                        <p class="acumulado-kpi-value tabular-nums" data-kpi-total="medias_canales">{{ number_format($totAnual['medias_canales'] ?? 0, 0, ',', '.') }}</p>
                    </div>

                    <div class="acumulado-kpi-card" style="--kpi-accent:#dc2626">
                        <p class="acumulado-kpi-label">Total Hallazgos</p>
                        <p class="acumulado-kpi-value tabular-nums" data-kpi-total="hallazgos">{{ number_format($totAnual['hallazgos'] ?? 0, 0, ',', '.') }}</p>
                    </div>

                    <div class="acumulado-kpi-card" style="--kpi-accent:#ea580c">
                        <p class="acumulado-kpi-label">Sobrebarriga Rotas</p>
                        <p class="acumulado-kpi-value tabular-nums" data-kpi-total="sobrebarriga_rota">{{ number_format($totAnual['sobrebarriga_rota'] ?? 0, 0, ',', '.') }}</p>
                        <p class="acumulado-kpi-sub">Promedio: <span class="tabular-nums" data-kpi-promedio="sobrebarriga_rota">{{ $fmtProm('sobrebarriga_rota') }}</span></p>
                    </div>
                    </div>

                    <div class="kpi-export-row kpi-export-row-4">
                    <div class="acumulado-kpi-card" style="--kpi-accent:#9333ea">
                        <p class="acumulado-kpi-label">Hematomas</p>
                        <p class="acumulado-kpi-value tabular-nums" data-kpi-total="hematomas">{{ number_format($totAnual['hematomas'] ?? 0, 0, ',', '.') }}</p>
                        <p class="acumulado-kpi-sub">Promedio: <span class="tabular-nums" data-kpi-promedio="hematomas">{{ $fmtProm('hematomas') }}</span></p>
                    </div>

                    <div class="acumulado-kpi-card" style="--kpi-accent:#ca8a04">
                        <p class="acumulado-kpi-label">Cobertura Grasa</p>
                        <p class="acumulado-kpi-value tabular-nums" data-kpi-total="cobertura_grasa">{{ number_format($totAnual['cobertura_grasa'] ?? 0, 0, ',', '.') }}</p>
                        <p class="acumulado-kpi-sub">Promedio: <span class="tabular-nums" data-kpi-promedio="cobertura_grasa">{{ $fmtProm('cobertura_grasa') }}</span></p>
                    </div>

                    <div class="acumulado-kpi-card" style="--kpi-accent:#db2777">
                        <p class="acumulado-kpi-label">Cortes Piernas</p>
                        <p class="acumulado-kpi-value tabular-nums" data-kpi-total="cortes_piernas">{{ number_format($totAnual['cortes_piernas'] ?? 0, 0, ',', '.') }}</p>
                        <p class="acumulado-kpi-sub">Promedio: <span class="tabular-nums" data-kpi-promedio="cortes_piernas">{{ $fmtProm('cortes_piernas') }}</span></p>
                    </div>

                    <div class="acumulado-kpi-card acumulado-kpi-card--highlight">
                        <p class="acumulado-kpi-label">Acumulado del Año</p>
                        <p class="acumulado-kpi-value tabular-nums" data-kpi-promedio="acumulado_anual">{{ $fmtProm('total_hallazgos') }}</p>
                        <p class="acumulado-kpi-sub">Promedio anual consolidado</p>
                    </div>
                    </div>
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
                const kpiExportFrame = document.getElementById('kpiExportFrame');
                const tablaWrap = document.getElementById('tablaAcumuladoWrap');
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

                    const badgeMeses = document.getElementById('kpiExportBadgeMeses');
                    if (badgeMeses) {
                        const n = visibleMesCount();
                        badgeMeses.textContent = n + (n === 1 ? ' mes' : ' meses');
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

                function prepararCloneResumenKpi(clonedDoc) {
                    const frame = clonedDoc.getElementById('kpiExportFrame');
                    if (!frame) return;

                    frame.style.display = 'inline-block';
                    frame.style.boxSizing = 'border-box';
                    frame.style.width = '1280px';

                    const badge = clonedDoc.getElementById('kpiExportBadgeMeses');
                    if (badge) {
                        const mesesVisibles = visibleMesCount();
                        badge.textContent = mesesVisibles + (mesesVisibles === 1 ? ' mes' : ' meses');
                    }

                    const logo = clonedDoc.querySelector('.kpi-export-logo');
                    if (logo) {
                        logo.style.display = 'block';
                    }

                    const eyebrow = clonedDoc.querySelector('.kpi-export-eyebrow');
                    if (eyebrow) {
                        eyebrow.style.display = 'block';
                    }

                    const title = clonedDoc.querySelector('#kpiExportHeader .kpi-export-title');
                    if (title) {
                        title.style.marginTop = '0.25rem';
                    }

                    clonedDoc.querySelectorAll('.acumulado-kpi-card').forEach(function (card) {
                        card.style.transform = 'none';
                    });

                    const row4 = clonedDoc.querySelector('.kpi-export-row-4');
                    if (row4) {
                        row4.style.width = 'calc(80% + 12px)';
                        row4.style.marginLeft = 'auto';
                        row4.style.marginRight = 'auto';
                        row4.style.gridTemplateColumns = 'repeat(4, minmax(0, 1fr))';
                    }

                    const row5 = clonedDoc.querySelector('.kpi-export-row-5');
                    if (row5) {
                        row5.style.gridTemplateColumns = 'repeat(5, minmax(0, 1fr))';
                    }
                }

                function descargarPngAcumulado(element, filename, btn, onCloneExtra) {
                    if (!element || typeof html2canvas === 'undefined') return;

                    btn.disabled = true;
                    btn.classList.add('opacity-70');

                    html2canvas(element, {
                        scale: 2,
                        backgroundColor: '#ffffff',
                        useCORS: true,
                        logging: false,
                        scrollX: 0,
                        scrollY: -window.scrollY,
                        width: element.id === 'kpiExportFrame' ? 1280 : element.scrollWidth,
                        windowWidth: element.id === 'kpiExportFrame' ? 1280 : element.scrollWidth,
                        onclone: onCloneExtra
                            ? function (clonedDoc) { onCloneExtra(clonedDoc); }
                            : undefined,
                    }).then(function (canvas) {
                        const enlace = document.createElement('a');
                        enlace.download = filename;
                        enlace.href = canvas.toDataURL('image/png');
                        enlace.click();
                    }).catch(function (err) {
                        console.error(err);
                        alert('No se pudo generar la imagen. Intente de nuevo.');
                    }).finally(function () {
                        btn.disabled = false;
                        btn.classList.remove('opacity-70');
                    });
                }

                const btnPngResumen = document.getElementById('btnDescargarPngResumen');
                if (btnPngResumen && kpiExportFrame) {
                    btnPngResumen.addEventListener('click', function () {
                        descargarPngAcumulado(
                            kpiExportFrame,
                            'acumulado-resumen-{{ $anio }}.png',
                            btnPngResumen,
                            prepararCloneResumenKpi
                        );
                    });
                }

                const btnPngTabla = document.getElementById('btnDescargarPngTabla');
                if (btnPngTabla && tablaWrap) {
                    btnPngTabla.addEventListener('click', function () {
                        descargarPngAcumulado(
                            tablaWrap,
                            'acumulado-tabla-{{ $anio }}.png',
                            btnPngTabla,
                            function (clonedDoc) {
                                const tabla = clonedDoc.getElementById('tablaAcumuladoWrap');
                                if (!tabla) return;
                                tabla.style.boxSizing = 'border-box';
                                tabla.style.border = '4px solid #7ce8ad';
                                tabla.style.boxShadow = 'inset 0 0 0 8px #f9dff8';
                                tabla.style.borderRadius = '12px';
                                tabla.style.padding = '16px';
                                tabla.style.background = '#ffffff';
                            }
                        );
                    });
                }
            });
        </script>
    @endif
</x-app-layout>
