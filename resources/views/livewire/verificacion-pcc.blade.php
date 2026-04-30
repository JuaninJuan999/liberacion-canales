<div class="bg-gradient-to-b from-slate-50 via-teal-50/40 to-transparent min-h-[calc(100vh-4rem)] pb-6 sm:pb-0">
    <div class="py-3 sm:py-6">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

            <div class="relative overflow-hidden rounded-2xl border border-teal-200 bg-gradient-to-br from-teal-50 via-white to-cyan-50 p-4 sm:p-6 shadow-md shadow-teal-900/10">
                <div class="relative flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-start gap-3 sm:gap-5 min-w-0 pr-1">
                        <div class="rounded-xl bg-white p-2.5 shadow-md ring-1 ring-teal-100 shrink-0">
                            <img src="{{ asset('logo.png') }}" alt="" class="h-9 sm:h-12 max-w-[48px] sm:max-w-[88px] object-contain">
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[11px] sm:text-xs font-semibold uppercase tracking-[0.18em] text-teal-800">Calidad</p>
                            <h1 class="text-lg sm:text-2xl font-bold !text-gray-900 tracking-tight mt-0.5 leading-snug">Verificación PCC</h1>
                        </div>
                    </div>
                    <div class="flex flex-col gap-3 w-full sm:w-auto sm:min-w-[280px] shrink-0 sm:items-end">
                        <a href="{{ route('verificacion-pcc.historial') }}" wire:navigate
                           class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-teal-700 bg-teal-900 px-4 py-3 sm:py-2.5 text-sm font-semibold text-white shadow-md hover:bg-teal-800 active:bg-teal-950 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 transition-colors min-h-[44px] w-full sm:w-auto touch-manipulation">
                            Historial PCC
                        </a>
                        @if ($externoDisponible)
                            <div class="grid grid-cols-1 gap-2 w-full sm:flex sm:flex-wrap sm:justify-end sm:max-w-md">
                                <span class="inline-flex w-full sm:w-auto items-center justify-center rounded-xl bg-teal-100 px-3 py-2.5 sm:py-2 text-sm font-semibold text-teal-900 tabular-nums min-h-[44px] sm:min-h-0 sm:flex-initial touch-manipulation">
                                    Total hoy: {{ $totalExternosHoy }}
                                </span>
                                <span class="inline-flex w-full sm:w-auto items-center justify-center rounded-xl bg-emerald-100 px-3 py-2.5 sm:py-2 text-sm font-semibold text-emerald-900 tabular-nums min-h-[44px] sm:min-h-0 sm:flex-initial touch-manipulation">
                                    Verificados: {{ $verificadosEnEstaAppHoy }}
                                </span>
                                <span class="inline-flex w-full sm:w-auto items-center justify-center rounded-xl bg-amber-100 px-3 py-2.5 sm:py-2 text-sm font-semibold text-amber-950 tabular-nums min-h-[44px] sm:min-h-0 sm:flex-initial touch-manipulation">
                                    Pendientes: {{ $pendientesCount }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
                @if ($externoDisponible)
                    @if ($totalExternosHoy === 0)
                        <p class="mt-4 text-sm text-gray-600 border-t border-teal-200/80 pt-4 leading-relaxed break-words">
                            No hay registros de insensibilización para la fecha de hoy en la BD externa (o la fecha del servidor PostgreSQL no coincide con tu día local — revisa zona horaria).
                        </p>
                    @elseif ($pendientesCount === 0 && $totalExternosHoy > 0)
                        <p class="mt-4 text-sm font-medium text-emerald-800 border-t border-teal-200/80 pt-4 flex items-center gap-2 flex-wrap">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500 text-white text-sm shrink-0">✓</span>
                            Ya registraste todas las verificaciones PCC para los productos del día en esta aplicación.
                        </p>
                    @endif
                @endif
            </div>

            @if (! $externoDisponible)
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 break-words">
                    <p class="font-semibold">Conexión a BD externa no configurada</p>
                    <p class="mt-1 text-amber-900/90 leading-relaxed">Define credenciales de la BD externa: por ejemplo <code class="rounded bg-white/80 px-1 break-all text-xs">POSTGRES_HOST</code>,
                        <code class="rounded bg-white/80 px-1 break-all text-xs">POSTGRES_DB</code>,
                        <code class="rounded bg-white/80 px-1 break-all text-xs">POSTGRES_USER</code>,
                        <code class="rounded bg-white/80 px-1 break-all text-xs">POSTGRES_PASSWORD</code>
                        (o el prefijo <code class="rounded bg-white/80 px-1 break-all text-xs">DB_TRAZABILIDAD_*</code>). Opcional: <code class="rounded bg-white/80 px-1 break-all text-xs">DB_TRAZABILIDAD_SEARCH_PATH</code>.
                    </p>
                </div>
            @endif

            @if (session('ok'))
                <div class="rounded-xl bg-emerald-50 border border-emerald-200/90 text-emerald-900 px-4 py-3 text-sm shadow-sm flex items-start gap-3">
                    <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white text-xs font-bold">✓</span>
                    <span class="pt-0.5">{{ session('ok') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-xl bg-red-50 border border-red-200 text-red-900 px-4 py-3 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Formulario verificación --}}
            <div class="rounded-2xl border border-teal-100 bg-white shadow-xl shadow-teal-900/[0.06] overflow-hidden ring-1 ring-teal-900/5">
                <div class="border-b border-teal-100 bg-gradient-to-r from-teal-50 via-cyan-50/40 to-emerald-50/50 px-4 sm:px-6 py-5">
                    <div>
                        <h2 class="text-lg font-semibold text-teal-900">Registrar verificación PCC</h2>
                    </div>
                </div>

                <form wire:submit="guardar" class="p-4 sm:p-6 space-y-6">
                    <div class="flex flex-col lg:flex-row gap-6 lg:gap-10">
                        {{-- Recuadro ID producto: ancho fijo a la izquierda (como antes) + texto en una sola línea en la tarjeta --}}
                        <div class="shrink-0 flex flex-col items-stretch w-full max-w-full sm:max-w-md sm:mx-auto lg:mx-0 lg:max-w-none lg:w-[280px]">
                            <span class="text-xs font-semibold uppercase tracking-wide text-teal-800 mb-2">ID producto</span>
                            <div class="rounded-2xl border-2 border-teal-400 bg-gradient-to-br from-teal-50 to-emerald-50 px-4 py-6 sm:px-5 sm:py-8 text-center shadow-inner shadow-teal-900/10 min-h-[7.5rem] sm:min-h-[8rem] w-full flex flex-col items-center justify-center overflow-x-auto overscroll-x-contain">
                                @if ($filaActual && isset($filaActual['id_producto']))
                                    <span class="block max-w-full text-xl sm:text-3xl md:text-4xl font-black tabular-nums tracking-tight text-teal-950 leading-tight text-center mx-auto break-all sm:break-normal sm:whitespace-nowrap sm:w-max">{{ trim((string) $filaActual['id_producto']) }}</span>
                                    @php
                                        $nombrePropietario = trim((string) (data_get($filaActual, 'nombre_empresa') ?? ''));
                                    @endphp
                                    <p class="mt-3 w-full text-sm sm:text-base text-teal-900/90 leading-snug break-words hyphens-auto text-center">
                                        <span class="font-semibold text-teal-800">Propietario:</span>
                                        <span class="ml-0.5">{{ $nombrePropietario !== '' ? $nombrePropietario : '—' }}</span>
                                    </p>
                                @else
                                    <span class="text-sm text-teal-700/80 leading-snug px-2">
                                        @if (! $externoDisponible)
                                            Configura la BD externa para obtener la cola del día.
                                        @elseif (($totalExternosHoy ?? 0) === 0)
                                            No hay insensibilizaciones registradas para hoy en la BD externa.
                                        @else
                                            No hay productos pendientes: ya aplicaste PCC a todos los del día, o revisa el mensaje superior.
                                        @endif
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Media canal 1 y 2: solo cumple / no cumple --}}
                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 sm:gap-y-6">
                            <div class="rounded-xl border border-teal-100 bg-teal-50/30 p-4">
                                <span class="text-sm font-semibold text-teal-950">Media canal 1</span>
                                <div class="mt-3 grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:gap-6">
                                    <label class="flex items-center gap-3 cursor-pointer rounded-lg border border-teal-100/80 bg-white/60 px-3 py-3 sm:border-transparent sm:bg-transparent sm:py-2 sm:px-0 min-h-[48px] sm:min-h-0 active:bg-teal-100/70 sm:active:bg-transparent touch-manipulation">
                                        <input type="radio" wire:model="cumple_media_canal_1" value="1" class="h-5 w-5 shrink-0 text-teal-600 border-teal-300 focus:ring-teal-500">
                                        <span class="text-base sm:text-sm text-teal-900 font-medium">Cumple</span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer rounded-lg border border-teal-100/80 bg-white/60 px-3 py-3 sm:border-transparent sm:bg-transparent sm:py-2 sm:px-0 min-h-[48px] sm:min-h-0 active:bg-teal-100/70 sm:active:bg-transparent touch-manipulation">
                                        <input type="radio" wire:model="cumple_media_canal_1" value="0" class="h-5 w-5 shrink-0 text-teal-600 border-teal-300 focus:ring-teal-500">
                                        <span class="text-base sm:text-sm text-teal-900 font-medium">No cumple</span>
                                    </label>
                                </div>
                                @error('cumple_media_canal_1') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="rounded-xl border border-teal-100 bg-teal-50/30 p-4">
                                <span class="text-sm font-semibold text-teal-950">Media canal 2</span>
                                <div class="mt-3 grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:gap-6">
                                    <label class="flex items-center gap-3 cursor-pointer rounded-lg border border-teal-100/80 bg-white/60 px-3 py-3 sm:border-transparent sm:bg-transparent sm:py-2 sm:px-0 min-h-[48px] sm:min-h-0 active:bg-teal-100/70 sm:active:bg-transparent touch-manipulation">
                                        <input type="radio" wire:model="cumple_media_canal_2" value="1" class="h-5 w-5 shrink-0 text-teal-600 border-teal-300 focus:ring-teal-500">
                                        <span class="text-base sm:text-sm text-teal-900 font-medium">Cumple</span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer rounded-lg border border-teal-100/80 bg-white/60 px-3 py-3 sm:border-transparent sm:bg-transparent sm:py-2 sm:px-0 min-h-[48px] sm:min-h-0 active:bg-teal-100/70 sm:active:bg-transparent touch-manipulation">
                                        <input type="radio" wire:model="cumple_media_canal_2" value="0" class="h-5 w-5 shrink-0 text-teal-600 border-teal-300 focus:ring-teal-500">
                                        <span class="text-base sm:text-sm text-teal-900 font-medium">No cumple</span>
                                    </label>
                                </div>
                                @error('cumple_media_canal_2') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-5 max-w-3xl">
                        <div>
                            <label for="pcc-obs" class="text-sm font-medium text-teal-950">Observación <span class="text-teal-600/80 font-normal">(opcional)</span></label>
                            <textarea id="pcc-obs" wire:model="observacion" rows="3" placeholder="Notas u observaciones…"
                                      class="mt-2 w-full rounded-lg border-teal-200 bg-white shadow-inner shadow-teal-900/5 focus:border-teal-500 focus:ring-2 focus:ring-teal-400/40 text-base sm:text-sm resize-y min-h-[5rem] py-3 px-3 touch-manipulation"></textarea>
                            @error('observacion') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="pcc-acc" class="text-sm font-medium text-teal-950">Acción correctiva <span class="text-teal-600/80 font-normal">(opcional)</span></label>
                            <textarea id="pcc-acc" wire:model="accion_correctiva" rows="3" placeholder="Acción correctiva si aplica…"
                                      class="mt-2 w-full rounded-lg border-teal-200 bg-white shadow-inner shadow-teal-900/5 focus:border-teal-500 focus:ring-2 focus:ring-teal-400/40 text-base sm:text-sm resize-y min-h-[5rem] py-3 px-3 touch-manipulation"></textarea>
                            @error('accion_correctiva') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex justify-stretch sm:justify-end pt-1 border-t border-teal-100">
                        <button type="submit"
                                wire:loading.attr="disabled"
                                @disabled(! $filaActual)
                                class="inline-flex w-full sm:w-auto items-center justify-center rounded-xl bg-gradient-to-r from-teal-600 to-emerald-700 px-6 sm:px-8 py-3.5 sm:py-3 text-base sm:text-sm font-semibold text-white shadow-lg shadow-teal-700/30 hover:from-teal-500 hover:to-emerald-600 active:opacity-95 focus:outline-none focus:ring-2 focus:ring-teal-400 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all min-h-[48px] touch-manipulation">
                            <span wire:loading.remove wire:target="guardar">Guardar y pasar al siguiente</span>
                            <span wire:loading wire:target="guardar">Guardando…</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
