<div class="bg-gradient-to-b from-teal-50/70 via-emerald-50/30 to-transparent min-h-[calc(100vh-4rem)]">
    <div class="py-4 sm:py-6">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-6">

            {{-- Cabecera --}}
            <div class="relative overflow-hidden rounded-2xl border border-teal-200 bg-gradient-to-br from-teal-50 via-emerald-50 to-cyan-50 p-4 sm:p-6 shadow-md shadow-teal-900/10">
                <div class="absolute -right-12 -top-12 h-36 w-36 rounded-full bg-teal-200/40 blur-2xl pointer-events-none" aria-hidden="true"></div>
                <div class="relative flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-start gap-3 sm:gap-5 min-w-0">
                        <div class="rounded-xl bg-white p-2.5 shadow-md ring-1 ring-teal-100 shrink-0">
                            <img src="{{ asset('logo.png') }}" alt="" class="h-8 sm:h-12 max-w-[44px] sm:max-w-[88px] object-contain">
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] sm:text-xs font-semibold uppercase tracking-[0.18em] text-teal-800">Control de proceso</p>
                            <h1 class="text-xl sm:text-2xl font-bold !text-gray-900 tracking-tight mt-0.5">Historial — titulación de ácido láctico</h1>
                            <p class="mt-1 text-sm text-teal-900/85 max-w-2xl leading-snug">Consulta, filtros y exportación a Excel.</p>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                        <a href="{{ route('titulacion-acido-lactico') }}"
                           class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-teal-400/80 bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-teal-900/15 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 transition-colors w-full sm:w-auto">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            Volver al formulario
                        </a>
                        <a href="{{ route('consumo-acido-lactico') }}"
                           class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-emerald-500/70 bg-white px-4 py-2.5 text-sm font-semibold text-emerald-800 shadow-md shadow-teal-900/10 hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors w-full sm:w-auto">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            Consumo de ácido láctico
                        </a>
                    </div>
                </div>
            </div>

            {{-- Historial --}}
            <div class="rounded-2xl border border-teal-100 bg-white shadow-xl shadow-teal-900/[0.06] overflow-hidden ring-1 ring-teal-900/5">
                <div class="px-4 py-4 border-b border-teal-700/20 bg-gradient-to-r from-teal-800 via-teal-700 to-emerald-800">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-white tracking-tight flex items-center gap-2">
                                <svg class="w-5 h-5 text-teal-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2h-2M9 13h6"/>
                                </svg>
                                Registros guardados
                                <span class="ml-1 inline-flex items-center rounded-full bg-white/15 px-2.5 py-0.5 text-xs font-semibold text-white ring-1 ring-white/25">
                                    Total: {{ $totalRegistros }}
                                </span>
                            </h2>
                            <p class="mt-1 text-xs text-teal-100/90">Filtra por un día, por rango de fechas y actividad; exporta a Excel.</p>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-2 sm:items-end sm:flex-wrap">
                            <div class="flex flex-col">
                                <label for="tit-al-hist-dia" class="text-[11px] font-semibold uppercase tracking-wide text-teal-100/90">Día</label>
                                <input id="tit-al-hist-dia" type="date" wire:model.live.debounce.300ms="fecha_dia"
                                       title="Si eliges un día, se ignoran «Desde» y «Hasta»"
                                       class="h-10 rounded-lg border-teal-200/40 bg-white/10 text-white shadow-inner shadow-black/10 focus:border-white/60 focus:ring-2 focus:ring-white/20 text-sm placeholder:text-teal-200/50">
                            </div>
                            <div class="flex flex-col">
                                <label for="tit-al-hist-desde" class="text-[11px] font-semibold uppercase tracking-wide text-teal-100/90">Desde</label>
                                <input id="tit-al-hist-desde" type="date" wire:model.live.debounce.300ms="fecha_desde"
                                       @disabled($fecha_dia !== '')
                                       class="h-10 rounded-lg border-teal-200/40 bg-white/10 text-white shadow-inner shadow-black/10 focus:border-white/60 focus:ring-2 focus:ring-white/20 text-sm disabled:opacity-45 disabled:cursor-not-allowed">
                            </div>
                            <div class="flex flex-col">
                                <label for="tit-al-hist-hasta" class="text-[11px] font-semibold uppercase tracking-wide text-teal-100/90">Hasta</label>
                                <input id="tit-al-hist-hasta" type="date" wire:model.live.debounce.300ms="fecha_hasta"
                                       @disabled($fecha_dia !== '')
                                       class="h-10 rounded-lg border-teal-200/40 bg-white/10 text-white shadow-inner shadow-black/10 focus:border-white/60 focus:ring-2 focus:ring-white/20 text-sm disabled:opacity-45 disabled:cursor-not-allowed">
                            </div>
                            <div class="flex flex-col min-w-[190px]">
                                <label for="tit-al-hist-actividad" class="text-[11px] font-semibold uppercase tracking-wide text-teal-100/90">Actividad</label>
                                <select id="tit-al-hist-actividad" wire:model.live.debounce.250ms="actividad_filtro"
                                        class="h-10 rounded-lg border-teal-200/40 bg-white/90 text-teal-950 shadow-inner shadow-black/10 focus:border-white/60 focus:ring-2 focus:ring-white/20 text-sm">
                                    <option value="">Todas</option>
                                    @foreach($actividadesOpciones as $valor => $etiqueta)
                                        <option value="{{ $valor }}">{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="button" wire:click="limpiarFiltros"
                                    class="h-10 inline-flex items-center justify-center rounded-lg bg-white/15 px-3 text-sm font-semibold text-white ring-1 ring-white/25 hover:bg-white/20 transition-colors">
                                Quitar filtros
                            </button>

                            <a
                                href="{{ route('titulacion-acido-lactico.historial.excel', array_filter(['desde' => $fecha_desde ?: null, 'hasta' => $fecha_hasta ?: null, 'dia' => $fecha_dia ?: null, 'actividad' => $actividad_filtro ?: null])) }}"
                                class="h-10 inline-flex items-center justify-center rounded-lg bg-emerald-500/80 px-3 text-sm font-semibold text-white ring-1 ring-emerald-200/30 hover:bg-emerald-500 transition-colors"
                            >
                                Exportar a Excel
                            </a>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-teal-100 text-sm">
                        <thead class="bg-teal-900/95">
                            <tr>
                                <th scope="col" class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-teal-100 whitespace-nowrap">Fecha</th>
                                <th scope="col" class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-teal-100 whitespace-nowrap">Hora</th>
                                <th scope="col" class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-teal-100 whitespace-nowrap">Vol. NaOH (ml)</th>
                                <th scope="col" class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-teal-100 whitespace-nowrap">Conc. (%)</th>
                                <th scope="col" class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-teal-100 whitespace-nowrap">Cumple</th>
                                <th scope="col" class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-teal-100 min-w-[120px]">Corrección</th>
                                <th scope="col" class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-teal-100 whitespace-nowrap">Actividad</th>
                                <th scope="col" class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-teal-100 whitespace-nowrap">Responsable</th>
                                <th scope="col" class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-teal-100 whitespace-nowrap">Verificado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-teal-50 bg-white">
                            @forelse ($registros as $row)
                                <tr wire:key="tit-al-hist-{{ $row->id }}" class="hover:bg-teal-50/70 transition-colors">
                                    <td class="px-3 py-2.5 whitespace-nowrap text-teal-950 font-medium">{{ $row->fecha->format('d/m/Y') }}</td>
                                    <td class="px-3 py-2.5 whitespace-nowrap text-teal-900">{{ \Illuminate\Support\Str::substr($row->hora, 0, 5) }}</td>
                                    <td class="px-3 py-2.5 whitespace-nowrap text-teal-900 tabular-nums">{{ number_format((float) $row->volumen_naoh_ml, 2, ',', '') }}</td>
                                    <td class="px-3 py-2.5 whitespace-nowrap text-teal-900 tabular-nums">{{ number_format((float) $row->concentracion_sol_pct, 2, ',', '') }}</td>
                                    <td class="px-3 py-2.5 whitespace-nowrap">
                                        @if ($row->cumple)
                                            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-900 ring-1 ring-emerald-200/80">Cumple</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-900 ring-1 ring-rose-200/80">No cumple</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 text-teal-900/90 max-w-xs truncate" title="{{ $row->correccion }}">{{ $row->correccion ?: '—' }}</td>
                                    <td class="px-3 py-2.5 whitespace-nowrap text-teal-900">{{ $actividadesOpciones[$row->actividad] ?? $row->actividad }}</td>
                                    <td class="px-3 py-2.5 whitespace-nowrap text-teal-900">{{ $row->usuario->name ?? '—' }}</td>
                                    <td class="px-3 py-2.5 whitespace-nowrap text-teal-900">{{ $row->verificadoPor?->name ?? $row->verificado_nombre ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-3 py-10 text-center text-teal-700/80 bg-teal-50/30">
                                        No hay registros para los filtros seleccionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($registros->hasPages())
                    <div class="px-4 py-3 border-t border-teal-100 bg-teal-50/40">
                        {{ $registros->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

