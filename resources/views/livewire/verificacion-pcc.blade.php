<div class="bg-gradient-to-b from-slate-50 via-teal-50/40 to-transparent min-h-[calc(100vh-4rem)]">
    <div class="py-4 sm:py-6">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-6">

            <div class="relative overflow-hidden rounded-2xl border border-teal-200 bg-gradient-to-br from-teal-50 via-white to-cyan-50 p-4 sm:p-6 shadow-md shadow-teal-900/10">
                <div class="relative flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-start gap-3 sm:gap-5 min-w-0">
                        <div class="rounded-xl bg-white p-2.5 shadow-md ring-1 ring-teal-100 shrink-0">
                            <img src="{{ asset('logo.png') }}" alt="" class="h-8 sm:h-12 max-w-[44px] sm:max-w-[88px] object-contain">
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] sm:text-xs font-semibold uppercase tracking-[0.18em] text-teal-800">Calidad</p>
                            <h1 class="text-xl sm:text-2xl font-bold !text-gray-900 tracking-tight mt-0.5">Verificación PCC</h1>
                            <p class="mt-1 text-sm text-teal-900/85 max-w-2xl leading-snug">
                                Cola del <strong>día actual</strong>: solo productos con insensibilización registrada hoy en la BD externa (plan/turno del día).
                                Al guardar se pasa automáticamente al siguiente ID producto pendiente.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            @if (! $externoDisponible)
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    <p class="font-semibold">Conexión a BD externa no configurada</p>
                    <p class="mt-1 text-amber-900/90">Define credenciales de la BD externa: por ejemplo <code class="rounded bg-white/80 px-1">POSTGRES_HOST</code>,
                        <code class="rounded bg-white/80 px-1">POSTGRES_DB</code>,
                        <code class="rounded bg-white/80 px-1">POSTGRES_USER</code>,
                        <code class="rounded bg-white/80 px-1">POSTGRES_PASSWORD</code>
                        (o el prefijo <code class="rounded bg-white/80 px-1">DB_TRAZABILIDAD_*</code>). Opcional: <code class="rounded bg-white/80 px-1">DB_TRAZABILIDAD_SEARCH_PATH</code>.
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

            {{-- Estado cola del día --}}
            @if ($externoDisponible)
                <div class="rounded-2xl border border-teal-100 bg-white shadow-lg shadow-teal-900/[0.06] px-4 py-5 sm:px-6 ring-1 ring-teal-900/5">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">Turno / día (BD externa)</p>
                            <p class="mt-1 text-sm text-teal-900">Fecha de trabajo: <span class="font-semibold tabular-nums">{{ now()->format('d/m/Y') }}</span></p>
                            <p class="mt-2 text-xs text-teal-800/85 max-w-xl">
                                Solo se listan insensibilizaciones con <strong>fecha de registro = hoy</strong> (sin histórico de días anteriores).
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-3 shrink-0">
                            <span class="inline-flex items-center rounded-xl bg-teal-100 px-4 py-2 text-sm font-semibold text-teal-900 tabular-nums">
                                Total hoy: {{ $totalExternosHoy }}
                            </span>
                            <span class="inline-flex items-center rounded-xl bg-emerald-100 px-4 py-2 text-sm font-semibold text-emerald-900 tabular-nums">
                                Verificados: {{ $verificadosEnEstaAppHoy }}
                            </span>
                            <span class="inline-flex items-center rounded-xl bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-950 tabular-nums">
                                Pendientes: {{ $pendientesCount }}
                            </span>
                        </div>
                    </div>
                    @if ($totalExternosHoy === 0)
                        <p class="mt-4 text-sm text-gray-600 border-t border-teal-100 pt-4">
                            No hay registros de insensibilización para la fecha de hoy en la BD externa (o la fecha del servidor PostgreSQL no coincide con tu día local — revisa zona horaria).
                        </p>
                    @elseif ($pendientesCount === 0 && $totalExternosHoy > 0)
                        <p class="mt-4 text-sm font-medium text-emerald-800 border-t border-teal-100 pt-4 flex items-center gap-2">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500 text-white text-sm">✓</span>
                            Ya registraste todas las verificaciones PCC para los productos del día en esta aplicación.
                        </p>
                    @elseif ($filaActual && $totalExternosHoy > 0)
                        <p class="mt-4 text-sm text-teal-900 border-t border-teal-100 pt-4">
                            Siguiente en cola:
                            <span class="font-bold tabular-nums">paso {{ $verificadosEnEstaAppHoy + 1 }} de {{ $totalExternosHoy }}</span>
                            (orden por ID insensibilización ascendente).
                        </p>
                    @endif
                </div>
            @endif

            {{-- Formulario verificación --}}
            <div class="rounded-2xl border border-teal-100 bg-white shadow-xl shadow-teal-900/[0.06] overflow-hidden ring-1 ring-teal-900/5">
                <div class="border-b border-teal-100 bg-gradient-to-r from-teal-50 via-cyan-50/40 to-emerald-50/50 px-4 sm:px-6 py-5">
                    <div>
                        <h2 class="text-lg font-semibold text-teal-900">Registrar verificación PCC</h2>
                        <p class="mt-2 text-sm text-teal-800/90">Completa la verificación del ID producto mostrado abajo y guarda para cargar el siguiente pendiente del día.</p>
                    </div>
                </div>

                <form wire:submit="guardar" class="p-4 sm:p-6 space-y-6">
                    <div class="flex flex-col lg:flex-row gap-6 lg:gap-10">
                        {{-- Recuadro ID producto: ancho fijo a la izquierda (como antes) + texto en una sola línea en la tarjeta --}}
                        <div class="shrink-0 flex flex-col items-stretch w-full sm:max-w-md sm:mx-auto lg:mx-0 lg:max-w-none lg:w-[280px]">
                            <span class="text-xs font-semibold uppercase tracking-wide text-teal-800 mb-2">ID producto</span>
                            <div class="rounded-2xl border-2 border-teal-400 bg-gradient-to-br from-teal-50 to-emerald-50 px-5 py-8 text-center shadow-inner shadow-teal-900/10 min-h-[8rem] w-full flex flex-col items-center justify-center overflow-x-auto">
                                @if ($filaActual && isset($filaActual['id_producto']))
                                    <span class="block w-max max-w-full whitespace-nowrap text-2xl sm:text-3xl md:text-4xl font-black tabular-nums tracking-tight text-teal-950 leading-tight text-center mx-auto">{{ trim((string) $filaActual['id_producto']) }}</span>
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
                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">
                            <div class="rounded-xl border border-teal-100 bg-teal-50/30 p-4">
                                <span class="text-sm font-semibold text-teal-950">Media canal 1</span>
                                <div class="mt-3 flex flex-wrap gap-6">
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="radio" wire:model="cumple_media_canal_1" value="1" class="text-teal-600 border-teal-300 focus:ring-teal-500">
                                        <span class="text-sm text-teal-900">Cumple</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="radio" wire:model="cumple_media_canal_1" value="0" class="text-teal-600 border-teal-300 focus:ring-teal-500">
                                        <span class="text-sm text-teal-900">No cumple</span>
                                    </label>
                                </div>
                                @error('cumple_media_canal_1') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="rounded-xl border border-teal-100 bg-teal-50/30 p-4">
                                <span class="text-sm font-semibold text-teal-950">Media canal 2</span>
                                <div class="mt-3 flex flex-wrap gap-6">
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="radio" wire:model="cumple_media_canal_2" value="1" class="text-teal-600 border-teal-300 focus:ring-teal-500">
                                        <span class="text-sm text-teal-900">Cumple</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="radio" wire:model="cumple_media_canal_2" value="0" class="text-teal-600 border-teal-300 focus:ring-teal-500">
                                        <span class="text-sm text-teal-900">No cumple</span>
                                    </label>
                                </div>
                                @error('cumple_media_canal_2') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col max-w-xl">
                        <label for="pcc-resp" class="text-sm font-medium text-teal-950">Responsable puesto de trabajo</label>
                        <input id="pcc-resp" type="text" wire:model="responsable_puesto_trabajo" placeholder="Nombre o cargo"
                               class="mt-2 h-11 w-full rounded-lg border-teal-200 bg-white shadow-inner shadow-teal-900/5 focus:border-teal-500 focus:ring-2 focus:ring-teal-400/40 text-sm">
                        @error('responsable_puesto_trabajo') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="rounded-xl bg-teal-50/35 border border-teal-100/90 px-4 py-3 text-sm text-teal-900/90">
                        <span class="font-medium text-teal-950">Registrado en el sistema por:</span>
                        {{ auth()->user()->name }}
                    </div>

                    <div class="flex justify-end pt-1 border-t border-teal-100">
                        <button type="submit"
                                wire:loading.attr="disabled"
                                @disabled(! $filaActual)
                                class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-teal-600 to-emerald-700 px-8 py-3 text-sm font-semibold text-white shadow-lg shadow-teal-700/30 hover:from-teal-500 hover:to-emerald-600 focus:outline-none focus:ring-2 focus:ring-teal-400 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                            <span wire:loading.remove wire:target="guardar">Guardar y pasar al siguiente</span>
                            <span wire:loading wire:target="guardar">Guardando…</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Historial local --}}
            <div class="rounded-2xl border border-teal-100 bg-white shadow-xl shadow-teal-900/[0.06] overflow-hidden ring-1 ring-teal-900/5">
                <div class="px-4 py-4 border-b border-teal-700/20 bg-gradient-to-r from-slate-700 via-slate-600 to-teal-900">
                    <h2 class="text-base font-semibold text-white tracking-tight">Historial en esta aplicación</h2>
                    <p class="mt-1 text-xs text-teal-100/90">Verificaciones PCC guardadas localmente</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-teal-100 text-sm">
                        <thead class="bg-teal-900/95">
                            <tr>
                                <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-teal-100 whitespace-nowrap">Fecha</th>
                                <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-teal-100 whitespace-nowrap">ID producto</th>
                                <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-teal-100 whitespace-nowrap">MC1</th>
                                <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-teal-100 whitespace-nowrap">MC2</th>
                                <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-teal-100 min-w-[120px]">Responsable</th>
                                <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-teal-100 whitespace-nowrap">Usuario</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-teal-50 bg-white">
                            @forelse ($historial as $h)
                                <tr wire:key="pcc-h-{{ $h->id }}" class="hover:bg-teal-50/70 transition-colors">
                                    <td class="px-3 py-2.5 whitespace-nowrap text-teal-950 text-xs">{{ $h->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-3 py-2.5 font-semibold tabular-nums text-teal-950 whitespace-nowrap">{{ $h->codigoProductoCompleto() }}</td>
                                    <td class="px-3 py-2.5 whitespace-nowrap">
                                        @if ($h->cumple_media_canal_1)
                                            <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-800">Cumple</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-semibold text-red-800">No cumple</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 whitespace-nowrap">
                                        @if ($h->cumple_media_canal_2)
                                            <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-800">Cumple</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-semibold text-red-800">No cumple</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 text-teal-900 text-xs max-w-[180px] truncate" title="{{ $h->responsable_puesto_trabajo }}">{{ $h->responsable_puesto_trabajo }}</td>
                                    <td class="px-3 py-2.5 whitespace-nowrap text-teal-900">{{ $h->usuario->name ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-10 text-center text-teal-700/80 bg-teal-50/30">Aún no hay verificaciones guardadas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($historial->hasPages())
                    <div class="px-4 py-3 border-t border-teal-100 bg-teal-50/40">
                        {{ $historial->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
