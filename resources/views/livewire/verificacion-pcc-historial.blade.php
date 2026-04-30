<div class="mx-auto max-w-7xl px-3 py-4 sm:px-6 sm:py-8 lg:px-8 pb-8">
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900 sm:p-6">
        <div class="flex flex-col gap-4">
            <div class="min-w-0">
                <h1 class="text-lg font-semibold leading-snug text-gray-900 dark:text-gray-100 sm:text-xl">
                    Historial de registros de Verificación PCC
                </h1>
                <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                    <span class="block sm:inline">Filtra por día.</span>
                    <span class="mt-1 block font-semibold text-gray-900 dark:text-gray-100 tabular-nums sm:mt-0 sm:inline sm:ml-1">
                        @php $n = $totalRegistros; @endphp
                        Total: {{ number_format($n, 0, ',', '.') }}
                        @if ($fecha_filtro !== '')
                            {{ $n === 1 ? 'registro' : 'registros' }} para el día {{ \Illuminate\Support\Carbon::parse($fecha_filtro)->format('d/m/Y') }}.
                        @else
                            {{ $n === 1 ? 'registro guardado' : 'registros guardados' }}.
                        @endif
                    </span>
                </p>
            </div>
            <div class="flex w-full flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:justify-end sm:gap-3">
                <a href="{{ route('verificacion-pcc') }}" wire:navigate
                   class="inline-flex min-h-[44px] w-full touch-manipulation items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 active:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 sm:w-auto sm:py-2">
                    ← Volver al registro PCC
                </a>
            </div>
        </div>

        <div class="mt-5 flex flex-col gap-3 border-t border-gray-200 pt-5 dark:border-gray-700 sm:mt-6 sm:flex-row sm:flex-wrap sm:items-end sm:gap-4 sm:pt-6">
            <div class="flex w-full flex-col gap-1 sm:w-auto sm:max-w-[200px]">
                <label for="fecha_filtro" class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Día (filtro)
                </label>
                <input id="fecha_filtro" type="date" wire:model.live="fecha_filtro"
                       class="min-h-[44px] w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-base text-gray-900 shadow-sm focus:border-[#0047ab] focus:ring-[#0047ab] dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 sm:min-h-0 sm:text-sm touch-manipulation">
            </div>
            <div class="flex w-full flex-col gap-2 sm:flex-1 sm:flex-row sm:flex-wrap sm:items-end sm:justify-end">
                @if ($fecha_filtro !== '')
                    <button type="button" wire:click="limpiarFecha"
                            class="inline-flex min-h-[44px] w-full touch-manipulation items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 active:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 sm:w-auto sm:py-2">
                        Mostrar todos los días
                    </button>
                @endif
                @php
                    $excelParams = $fecha_filtro !== '' ? ['fecha' => $fecha_filtro] : [];
                @endphp
                <a href="{{ route('verificacion-pcc.historial.excel', $excelParams) }}"
                   class="inline-flex min-h-[44px] w-full touch-manipulation items-center justify-center rounded-lg bg-[#0047ab] px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-[#003a91] active:bg-[#002d6f] focus:outline-none focus:ring-2 focus:ring-[#0047ab] focus:ring-offset-2 dark:focus:ring-offset-gray-900 sm:ms-auto sm:w-auto sm:py-2 sm:min-w-[160px]">
                    Descargar Excel
                </a>
            </div>
        </div>

        {{-- Listado móvil (tarjetas) --}}
        <div class="mt-5 space-y-3 md:hidden">
            @forelse ($historial as $item)
                <article wire:key="pcc-hist-m-{{ $item->id }}" class="rounded-xl border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-600 dark:bg-gray-800/50">
                    <div class="flex flex-wrap items-start justify-between gap-2 border-b border-gray-200 pb-3 dark:border-gray-600">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Fecha</p>
                        <p class="text-right text-sm font-semibold tabular-nums text-gray-900 dark:text-gray-100">
                            {{ $item->created_at?->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <p class="mt-3 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">ID producto</p>
                    <p class="mt-1 break-all font-mono text-sm font-semibold text-gray-900 dark:text-gray-100">
                        {{ $item->codigoProductoCompleto() }}
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400">MC1</span>
                        @if ($item->cumple_media_canal_1)
                            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">Cumple</span>
                        @else
                            <span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-800 dark:bg-red-900/40 dark:text-red-200">No cumple</span>
                        @endif
                        <span class="ms-2 text-xs text-gray-500 dark:text-gray-400">MC2</span>
                        @if ($item->cumple_media_canal_2)
                            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">Cumple</span>
                        @else
                            <span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-800 dark:bg-red-900/40 dark:text-red-200">No cumple</span>
                        @endif
                    </div>
                    <div class="mt-3 space-y-2 text-sm">
                        <div>
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Responsable</span>
                            <p class="mt-0.5 break-words text-gray-900 dark:text-gray-100">{{ $item->responsablePuestoResuelto() }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Observación</span>
                            <p class="mt-0.5 break-words text-gray-700 dark:text-gray-300">{{ $item->observacion ?: '—' }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Acción correctiva</span>
                            <p class="mt-0.5 break-words text-gray-700 dark:text-gray-300">{{ $item->accion_correctiva ?: '—' }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Usuario</span>
                            <p class="mt-0.5 break-words text-gray-900 dark:text-gray-100">{{ $item->usuario?->name ?? '—' }}</p>
                        </div>
                    </div>
                </article>
            @empty
                <p class="rounded-lg border border-dashed border-gray-300 py-10 text-center text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
                    No hay registros para el filtro seleccionado.
                </p>
            @endforelse
        </div>

        {{-- Tabla escritorio / tablet apaisado --}}
        <div class="-mx-4 mt-5 hidden overflow-x-auto px-4 sm:mx-0 sm:mt-6 sm:px-0 md:block">
            <div class="inline-block min-w-full rounded-lg border border-gray-200 align-middle dark:border-gray-700">
                <table class="min-w-[720px] w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800/80">
                        <tr>
                            <th class="whitespace-nowrap px-3 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">Fecha</th>
                            <th class="whitespace-nowrap px-3 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">ID producto</th>
                            <th class="whitespace-nowrap px-3 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">MC1</th>
                            <th class="whitespace-nowrap px-3 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">MC2</th>
                            <th class="min-w-[8rem] px-3 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">Responsable</th>
                            <th class="min-w-[7rem] px-3 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">Obs.</th>
                            <th class="min-w-[7rem] px-3 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">Acc. corr.</th>
                            <th class="whitespace-nowrap px-3 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">Usuario</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($historial as $item)
                            <tr wire:key="pcc-hist-d-{{ $item->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                <td class="whitespace-nowrap px-3 py-2.5 text-gray-800 dark:text-gray-100">
                                    {{ $item->created_at?->format('d/m/Y H:i') }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-2.5 font-mono text-xs text-gray-900 dark:text-gray-100">
                                    {{ $item->codigoProductoCompleto() }}
                                </td>
                                <td class="px-3 py-2.5">
                                    @if ($item->cumple_media_canal_1)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">Cumple</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/40 dark:text-red-200">No cumple</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5">
                                    @if ($item->cumple_media_canal_2)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">Cumple</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/40 dark:text-red-200">No cumple</span>
                                    @endif
                                </td>
                                <td class="max-w-[12rem] truncate px-3 py-2.5 text-gray-800 dark:text-gray-100" title="{{ $item->responsablePuestoResuelto() }}">
                                    {{ $item->responsablePuestoResuelto() }}
                                </td>
                                <td class="max-w-[14rem] truncate px-3 py-2.5 text-gray-600 dark:text-gray-300" title="{{ $item->observacion }}">
                                    {{ $item->observacion ?: '—' }}
                                </td>
                                <td class="max-w-[14rem] truncate px-3 py-2.5 text-gray-600 dark:text-gray-300" title="{{ $item->accion_correctiva }}">
                                    {{ $item->accion_correctiva ?: '—' }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-2.5 text-gray-800 dark:text-gray-100">
                                    {{ $item->usuario?->name ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-3 py-10 text-center text-gray-500 dark:text-gray-400">
                                    No hay registros para el filtro seleccionado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($historial->hasPages())
            <div class="mt-4 overflow-x-auto pb-2 [-webkit-overflow-scrolling:touch]">
                <div class="flex min-w-0 justify-center px-1 sm:justify-end">
                    {{ $historial->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
