<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                    Historial de registros de Verificación PCC
                </h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Filtra por día.
                    <span class="ml-1 font-semibold text-gray-900 dark:text-gray-100 tabular-nums">
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
            <div class="flex flex-wrap items-end gap-3">
                <a href="{{ route('verificacion-pcc') }}" wire:navigate
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    ← Volver al registro PCC
                </a>
            </div>
        </div>

        <div class="mt-6 flex flex-col gap-4 border-t border-gray-200 pt-6 dark:border-gray-700 sm:flex-row sm:flex-wrap sm:items-end">
            <div class="flex flex-col gap-1">
                <label for="fecha_filtro" class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Día (filtro)
                </label>
                <input id="fecha_filtro" type="date" wire:model.live="fecha_filtro"
                       class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-[#0047ab] focus:ring-[#0047ab] dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
            </div>
            @if ($fecha_filtro !== '')
                <button type="button" wire:click="limpiarFecha"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    Mostrar todos los días
                </button>
            @endif
            <div class="sm:ml-auto">
                @php
                    $excelParams = $fecha_filtro !== '' ? ['fecha' => $fecha_filtro] : [];
                @endphp
                <a href="{{ route('verificacion-pcc.historial.excel', $excelParams) }}"
                   class="inline-flex items-center rounded-lg bg-[#0047ab] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#003a91] focus:outline-none focus:ring-2 focus:ring-[#0047ab] focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                    Descargar Excel
                </a>
            </div>
        </div>

        <div class="mt-6 overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800/80">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">Fecha</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">ID producto</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">MC1</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">MC2</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">Responsable</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">Obs.</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">Acc. corr.</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">Usuario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($historial as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                            <td class="whitespace-nowrap px-3 py-2 text-gray-800 dark:text-gray-100">
                                {{ $item->created_at?->format('d/m/Y H:i') }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-2 font-mono text-xs text-gray-900 dark:text-gray-100">
                                {{ $item->codigoProductoCompleto() }}
                            </td>
                            <td class="px-3 py-2">
                                @if ($item->cumple_media_canal_1)
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">Cumple</span>
                                @else
                                    <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/40 dark:text-red-200">No cumple</span>
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                @if ($item->cumple_media_canal_2)
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">Cumple</span>
                                @else
                                    <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/40 dark:text-red-200">No cumple</span>
                                @endif
                            </td>
                            <td class="max-w-[12rem] truncate px-3 py-2 text-gray-800 dark:text-gray-100" title="{{ $item->responsablePuestoResuelto() }}">
                                {{ $item->responsablePuestoResuelto() }}
                            </td>
                            <td class="max-w-[14rem] truncate px-3 py-2 text-gray-600 dark:text-gray-300" title="{{ $item->observacion }}">
                                {{ $item->observacion ?: '—' }}
                            </td>
                            <td class="max-w-[14rem] truncate px-3 py-2 text-gray-600 dark:text-gray-300" title="{{ $item->accion_correctiva }}">
                                {{ $item->accion_correctiva ?: '—' }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-2 text-gray-800 dark:text-gray-100">
                                {{ $item->usuario?->name ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-8 text-center text-gray-500 dark:text-gray-400">
                                No hay registros para el filtro seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($historial->hasPages())
            <div class="mt-4">
                {{ $historial->links() }}
            </div>
        @endif
    </div>
</div>
