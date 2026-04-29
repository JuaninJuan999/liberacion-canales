<div class="bg-gradient-to-b from-teal-50/70 via-emerald-50/30 to-transparent min-h-[calc(100vh-4rem)]">
    <div class="py-4 sm:py-6">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-6">

            <div class="relative overflow-hidden rounded-2xl border border-teal-200 bg-gradient-to-br from-teal-50 via-emerald-50 to-cyan-50 p-4 sm:p-6 shadow-md shadow-teal-900/10">
                <div class="absolute -right-12 -top-12 h-36 w-36 rounded-full bg-teal-200/40 blur-2xl pointer-events-none" aria-hidden="true"></div>
                <div class="relative flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-start gap-3 sm:gap-5 min-w-0">
                        <div class="rounded-xl bg-white p-2.5 shadow-md ring-1 ring-teal-100 shrink-0">
                            <img src="{{ asset('logo.png') }}" alt="" class="h-8 sm:h-12 max-w-[44px] sm:max-w-[88px] object-contain">
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] sm:text-xs font-semibold uppercase tracking-[0.18em] text-teal-800">Control de proceso</p>
                            <h1 class="text-xl sm:text-2xl font-bold !text-gray-900 tracking-tight mt-0.5">Consumo de ácido láctico</h1>
                            <p class="mt-1 text-sm text-teal-900/85 max-w-2xl leading-snug">Registro de litros preparados, cantidad utilizada y observaciones.</p>
                            <p class="mt-2 text-xs text-teal-800/80">La fecha y la hora se asignan automáticamente al guardar.</p>
                        </div>
                    </div>
                    <a href="{{ route('titulacion-acido-lactico') }}"
                       class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-teal-300 bg-white/90 px-4 py-2.5 text-sm font-semibold text-teal-800 shadow-sm hover:bg-teal-50 focus:outline-none focus:ring-2 focus:ring-teal-400 focus:ring-offset-1 transition-colors w-full sm:w-auto">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Titulación de ácido láctico
                    </a>
                </div>
            </div>

            @if (session('ok'))
                <div class="rounded-xl bg-emerald-50 border border-emerald-200/90 text-emerald-900 px-4 py-3 text-sm shadow-sm shadow-emerald-900/5 flex items-start gap-3">
                    <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white text-xs font-bold">✓</span>
                    <span class="pt-0.5">{{ session('ok') }}</span>
                </div>
            @endif

            <div class="rounded-2xl border border-teal-100 bg-white shadow-xl shadow-teal-900/[0.06] overflow-hidden ring-1 ring-teal-900/5">
                <div class="border-b border-teal-100 bg-gradient-to-r from-teal-50 via-cyan-50/40 to-emerald-50/50 px-4 sm:px-6 py-5">
                    <h2 class="text-lg font-semibold text-teal-900">Nuevo registro</h2>
                    <p class="mt-2 text-sm text-teal-800/90">Completa litros preparados y cantidad de ácido láctico (ml). La observación es opcional.</p>
                </div>

                <form wire:submit="guardar" class="p-4 sm:p-6 space-y-6">
                    <div class="rounded-xl bg-teal-50/40 border border-teal-100/80 p-4 sm:p-5">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-teal-800 mb-4 flex items-center gap-2">
                            <span class="h-px flex-1 max-w-[2rem] bg-teal-300 rounded"></span>
                            Datos del consumo
                            <span class="h-px flex-1 bg-teal-100 rounded"></span>
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                            <div class="flex flex-col">
                                <label for="cal-litros" class="text-sm font-medium text-teal-950">Litros preparados</label>
                                <p class="mt-1 text-xs text-teal-700/80 min-h-[2.25rem] leading-snug">Volumen de solución preparada (litros)</p>
                                <input id="cal-litros" type="text" inputmode="decimal" wire:model="litros_preparados" placeholder="ej. 100"
                                       class="mt-auto h-11 w-full rounded-lg border-teal-200 bg-white/90 shadow-inner shadow-teal-900/5 focus:border-teal-500 focus:ring-2 focus:ring-teal-400/40 text-sm">
                                @error('litros_preparados') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex flex-col">
                                <label for="cal-cant" class="text-sm font-medium text-teal-950">Cantidad ácido láctico (ml)</label>
                                <p class="mt-1 text-xs text-teal-700/80 min-h-[2.25rem] leading-snug">Cantidad consumida en mililitros</p>
                                <input id="cal-cant" type="text" inputmode="decimal" wire:model="cantidad_acido_lactico_ml" placeholder="ej. 250"
                                       class="mt-auto h-11 w-full rounded-lg border-teal-200 bg-white/90 shadow-inner shadow-teal-900/5 focus:border-teal-500 focus:ring-2 focus:ring-teal-400/40 text-sm">
                                @error('cantidad_acido_lactico_ml') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="md:col-span-2 flex flex-col">
                                <label for="cal-obs" class="text-sm font-medium text-teal-950">Observación</label>
                                <p class="mt-1 text-xs text-teal-700/80 mb-2">Opcional</p>
                                <textarea id="cal-obs" wire:model="observacion" rows="3" placeholder="Notas adicionales…"
                                          class="w-full rounded-xl border-teal-200 bg-white/90 shadow-inner shadow-teal-900/5 focus:border-teal-500 focus:ring-2 focus:ring-teal-400/40 text-sm resize-y min-h-[5rem]"></textarea>
                                @error('observacion') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-emerald-50/35 border border-emerald-100/90 px-4 py-3 text-sm text-teal-900/90">
                        <span class="font-medium text-teal-950">Registrado por:</span>
                        {{ auth()->user()->name }}
                    </div>

                    <div class="flex justify-end pt-1 border-t border-teal-100">
                        <button type="submit"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-teal-600 to-emerald-700 px-8 py-3 text-sm font-semibold text-white shadow-lg shadow-teal-700/30 hover:from-teal-500 hover:to-emerald-600 focus:outline-none focus:ring-2 focus:ring-teal-400 focus:ring-offset-2 disabled:opacity-60 transition-all">
                            <span wire:loading.remove wire:target="guardar">Guardar registro</span>
                            <span wire:loading wire:target="guardar">Guardando…</span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="rounded-2xl border border-teal-100 bg-white shadow-xl shadow-teal-900/[0.06] overflow-hidden ring-1 ring-teal-900/5">
                <div class="px-4 py-4 border-b border-teal-700/20 bg-gradient-to-r from-teal-800 via-teal-700 to-emerald-800">
                    <h2 class="text-base font-semibold text-white tracking-tight">Registros recientes</h2>
                    <p class="mt-1 text-xs text-teal-100/90">Historial de consumos</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-teal-100 text-sm">
                        <thead class="bg-teal-900/95">
                            <tr>
                                <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-teal-100 whitespace-nowrap">Fecha</th>
                                <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-teal-100 whitespace-nowrap">Hora</th>
                                <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-teal-100 whitespace-nowrap">Litros prep.</th>
                                <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-teal-100 whitespace-nowrap">Cant. ac. láct. (ml)</th>
                                <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-teal-100 min-w-[140px]">Observación</th>
                                <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-teal-100 whitespace-nowrap">Registrado por</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-teal-50 bg-white">
                            @forelse ($registros as $row)
                                <tr wire:key="cal-{{ $row->id }}" class="hover:bg-teal-50/70 transition-colors">
                                    <td class="px-3 py-2.5 whitespace-nowrap text-teal-950 font-medium">{{ $row->fecha->format('d/m/Y') }}</td>
                                    <td class="px-3 py-2.5 whitespace-nowrap text-teal-900">{{ \Illuminate\Support\Str::substr($row->hora, 0, 5) }}</td>
                                    <td class="px-3 py-2.5 whitespace-nowrap text-teal-900 tabular-nums">{{ format_decimal_es_trim($row->litros_preparados) }}</td>
                                    <td class="px-3 py-2.5 whitespace-nowrap text-teal-900 tabular-nums">{{ format_decimal_es_trim($row->cantidad_acido_lactico_ml) }}</td>
                                    <td class="px-3 py-2.5 text-teal-900/90 max-w-xs truncate" title="{{ $row->observacion }}">{{ $row->observacion ?: '—' }}</td>
                                    <td class="px-3 py-2.5 whitespace-nowrap text-teal-900">{{ $row->usuario->name ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-10 text-center text-teal-700/80 bg-teal-50/30">No hay registros todavía.</td>
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
