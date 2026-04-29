<div class="bg-gradient-to-b from-teal-50/70 via-emerald-50/30 to-transparent min-h-[calc(100vh-4rem)]">
    <div class="py-4 sm:py-6">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-6">

            {{-- Cabecera módulo (texto oscuro sobre fondo claro = contraste seguro en cualquier entorno) --}}
            <div class="relative overflow-hidden rounded-2xl border border-teal-200 bg-gradient-to-br from-teal-50 via-emerald-50 to-cyan-50 p-4 sm:p-6 shadow-md shadow-teal-900/10">
                <div class="absolute -right-12 -top-12 h-36 w-36 rounded-full bg-teal-200/40 blur-2xl pointer-events-none" aria-hidden="true"></div>
                <div class="relative flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-start gap-3 sm:gap-5 min-w-0">
                        <div class="rounded-xl bg-white p-2.5 shadow-md ring-1 ring-teal-100 shrink-0">
                            <img src="{{ asset('logo.png') }}" alt="" class="h-8 sm:h-12 max-w-[44px] sm:max-w-[88px] object-contain">
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] sm:text-xs font-semibold uppercase tracking-[0.18em] text-teal-800">Control de proceso</p>
                            <h1 class="text-xl sm:text-2xl font-bold !text-gray-900 tracking-tight mt-0.5">Titulación de ácido láctico</h1>
                            <p class="mt-1 text-sm text-teal-900/85 max-w-2xl leading-snug">Registro de titulación y verificación del desinfectante.</p>
                        </div>
                    </div>
                    <a href="{{ route('consumo-acido-lactico') }}"
                       class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-teal-400/80 bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-teal-900/15 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 transition-colors w-full sm:w-auto">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Consumo de ácido láctico
                    </a>
                </div>
            </div>

            @if (session('ok'))
                <div class="rounded-xl bg-emerald-50 border border-emerald-200/90 text-emerald-900 px-4 py-3 text-sm shadow-sm shadow-emerald-900/5 flex items-start gap-3">
                    <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white text-xs font-bold">✓</span>
                    <span class="pt-0.5">{{ session('ok') }}</span>
                </div>
            @endif

            {{-- Formulario nuevo registro --}}
            <div class="rounded-2xl border border-teal-100 bg-white shadow-xl shadow-teal-900/[0.06] overflow-hidden ring-1 ring-teal-900/5">
                <div class="border-b border-teal-100 bg-gradient-to-r from-teal-50 via-cyan-50/40 to-emerald-50/50 px-4 sm:px-6 py-5">
                    <h2 class="text-lg font-semibold text-teal-900">Nuevo registro</h2>
                    <p class="mt-2 text-sm text-teal-800/90 leading-relaxed max-w-3xl">
                        La <span class="font-semibold text-teal-950">fecha</span> y la <span class="font-semibold text-teal-950">hora</span> del registro se guardan automáticamente al pulsar «Guardar registro».
                    </p>
                </div>

                <form wire:submit="guardar" class="p-4 sm:p-6 space-y-8">

                    {{-- Bloque 1: mediciones --}}
                    <div class="rounded-xl bg-teal-50/40 border border-teal-100/80 p-4 sm:p-5">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-teal-800 mb-4 flex items-center gap-2">
                            <span class="h-px flex-1 max-w-[2rem] bg-teal-300 rounded"></span>
                            Mediciones
                            <span class="h-px flex-1 bg-teal-100 rounded"></span>
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5 max-w-3xl">
                            <div class="flex flex-col">
                                <label for="tit-al-volumen" class="text-sm font-medium text-teal-950">Volumen NaOH (ml)</label>
                                <p id="tit-al-volumen-hint" class="mt-1 text-xs text-teal-700/80 leading-snug min-h-[2.25rem]">Rango esperado: 2,2 – 2,3 ml</p>
                                <input id="tit-al-volumen" type="text" inputmode="decimal" wire:model="volumen_naoh_ml" placeholder="ej. 2,25" aria-describedby="tit-al-volumen-hint"
                                       class="mt-auto h-11 w-full rounded-lg border-teal-200 bg-white/90 shadow-inner shadow-teal-900/5 focus:border-teal-500 focus:ring-2 focus:ring-teal-400/40 text-sm">
                                @error('volumen_naoh_ml') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex flex-col">
                                <label for="tit-al-conc" class="text-sm font-medium text-teal-950">Conc. solución (%)</label>
                                <p id="tit-al-conc-hint" class="mt-1 text-xs text-teal-700/80 leading-snug min-h-[2.25rem]">2 % ± 0,1 (1,9 – 2,1)</p>
                                <input id="tit-al-conc" type="text" inputmode="decimal" wire:model="concentracion_sol_pct" placeholder="ej. 2,0" aria-describedby="tit-al-conc-hint"
                                       class="mt-auto h-11 w-full rounded-lg border-teal-200 bg-white/90 shadow-inner shadow-teal-900/5 focus:border-teal-500 focus:ring-2 focus:ring-teal-400/40 text-sm">
                                @error('concentracion_sol_pct') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Bloque 2: cumple + corrección --}}
                    <div class="rounded-xl bg-cyan-50/35 border border-cyan-100/90 p-4 sm:p-5">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-cyan-900 mb-4 flex items-center gap-2">
                            <span class="h-px flex-1 max-w-[2rem] bg-cyan-300 rounded"></span>
                            Evaluación
                            <span class="h-px flex-1 bg-cyan-100 rounded"></span>
                        </h3>
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
                            <div class="lg:col-span-4">
                                <span class="block text-sm font-medium text-teal-950 mb-3">¿Cumple?</span>
                                <div class="rounded-xl border border-teal-200 bg-white/70 px-4 py-3.5 flex flex-wrap gap-6 shadow-sm shadow-teal-900/5">
                                    <label class="inline-flex items-center gap-2.5 cursor-pointer group">
                                        <input type="radio" wire:model.live="cumple" value="1" class="border-teal-300 text-teal-600 focus:ring-teal-500">
                                        <span class="text-sm font-medium text-teal-950 group-hover:text-teal-900">Cumple</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2.5 cursor-pointer group">
                                        <input type="radio" wire:model.live="cumple" value="0" class="border-teal-300 text-teal-600 focus:ring-teal-500">
                                        <span class="text-sm font-medium text-teal-950 group-hover:text-teal-900">No cumple</span>
                                    </label>
                                </div>
                                @error('cumple') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="lg:col-span-8">
                                <label for="tit-al-correccion" class="block text-sm font-medium text-teal-950 mb-2">Corrección</label>
                                <textarea id="tit-al-correccion" wire:model="correccion" rows="3" placeholder="Opcional — describe la corrección aplicada si aplica"
                                          class="w-full rounded-xl border-teal-200 bg-white/90 shadow-inner shadow-teal-900/5 focus:border-teal-500 focus:ring-2 focus:ring-teal-400/40 text-sm resize-y min-h-[5.5rem]"></textarea>
                                @error('correccion') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Bloque 3: actividad y personas --}}
                    <div class="rounded-xl bg-emerald-50/35 border border-emerald-100/90 p-4 sm:p-5">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-emerald-900 mb-4 flex items-center gap-2">
                            <span class="h-px flex-1 max-w-[2rem] bg-emerald-300 rounded"></span>
                            Actividad y verificación
                            <span class="h-px flex-1 bg-emerald-100 rounded"></span>
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-5">
                            <div class="flex flex-col">
                                <label for="tit-al-actividad" class="text-sm font-medium text-teal-950">Actividad</label>
                                <p class="mt-1 text-xs text-teal-700/80 min-h-[2.25rem] leading-snug">Momento en que se realiza el control</p>
                                <select id="tit-al-actividad" wire:model="actividad"
                                        class="mt-auto h-11 w-full rounded-lg border-teal-200 bg-white/90 shadow-inner shadow-teal-900/5 focus:border-teal-500 focus:ring-2 focus:ring-teal-400/40 text-sm">
                                    @foreach($actividadesOpciones as $valor => $etiqueta)
                                        <option value="{{ $valor }}">{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                                @error('actividad') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex flex-col">
                                <label for="tit-al-responsable" class="text-sm font-medium text-teal-950">Responsable</label>
                                <p class="mt-1 text-xs text-teal-700/80 min-h-[2.25rem] leading-snug">Usuario que registra (automático)</p>
                                <input id="tit-al-responsable" type="text" readonly value="{{ auth()->user()->name }}"
                                       class="mt-auto h-11 w-full rounded-lg border border-teal-200/90 bg-teal-50/80 text-teal-950 text-sm cursor-not-allowed">
                            </div>
                            <div class="flex flex-col">
                                <label for="tit-al-verificado" class="text-sm font-medium text-teal-950">Verificado</label>
                                <p class="mt-1 text-xs text-teal-700/80 min-h-[2.25rem] leading-snug">Persona que revisa el registro</p>
                                <input id="tit-al-verificado" type="text" wire:model="verificado_nombre" placeholder="Nombre de quien verifica"
                                       class="mt-auto h-11 w-full rounded-lg border-teal-200 bg-white/90 shadow-inner shadow-teal-900/5 focus:border-teal-500 focus:ring-2 focus:ring-teal-400/40 text-sm">
                                @error('verificado_nombre') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-3 border-t border-teal-100">
                        <button type="submit"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-teal-600 to-emerald-700 px-8 py-3 text-sm font-semibold text-white shadow-lg shadow-teal-700/30 hover:from-teal-500 hover:to-emerald-600 focus:outline-none focus:ring-2 focus:ring-teal-400 focus:ring-offset-2 disabled:opacity-60 transition-all">
                            <span wire:loading.remove wire:target="guardar">Guardar registro</span>
                            <span wire:loading wire:target="guardar">Guardando…</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tabla histórico --}}
            <div class="rounded-2xl border border-teal-100 bg-white shadow-xl shadow-teal-900/[0.06] overflow-hidden ring-1 ring-teal-900/5">
                <div class="px-4 py-4 border-b border-teal-700/20 bg-gradient-to-r from-teal-800 via-teal-700 to-emerald-800">
                    <h2 class="text-base font-semibold text-white tracking-tight flex items-center gap-2">
                        <svg class="w-5 h-5 text-teal-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2h-2M9 13h6"/>
                        </svg>
                        Registros recientes
                    </h2>
                    <p class="mt-1 text-xs text-teal-100/90">Historial de titulaciones guardadas</p>
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
                                <tr wire:key="tit-al-{{ $row->id }}" class="hover:bg-teal-50/70 transition-colors">
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
                                    <td class="px-3 py-2.5 whitespace-nowrap text-teal-900">{{ $row->verificado_nombre }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-3 py-10 text-center text-teal-700/80 bg-teal-50/30">
                                        <span class="inline-flex flex-col items-center gap-2">
                                            <span class="rounded-full bg-teal-100 p-3 text-teal-600">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            </span>
                                            No hay registros todavía.
                                        </span>
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
