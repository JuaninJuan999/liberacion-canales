<div wire:poll.10s="cargarHistorial">
    <div class="mb-6 flex flex-wrap justify-between items-center gap-4">
        <h2 class="text-xl font-bold text-gray-800">Indicador Diario - Historial de Liberación</h2>
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">Mes:</label>
                <select wire:model.live="mes" wire:change="cargarHistorial" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->locale('es')->isoFormat('MMMM') }}</option>
                    @endfor
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">Año:</label>
                <select wire:model.live="anio" wire:change="cargarHistorial" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @for($a = now()->year - 2; $a <= now()->year + 1; $a++)
                        <option value="{{ $a }}">{{ $a }}</option>
                    @endfor
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">Ver un día:</label>
                <input type="date" wire:model.live="fecha" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            </div>
        </div>
    </div>

    {{-- Tabla Historial de Liberación --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Historial de Liberación</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">F. Operación</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">1/2 Canal 1</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">1/2 Canal 2</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total Hallazgo</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Cobertura G</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Hematoma</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Cortes en P</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Sobrebarriga R</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Promedio mes</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Part Total</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Mes</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Año</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">META Cobertura</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">META Sobrebarr.</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">META Hematoma</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">META Cortes P</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($historial as $row)
                        <tr wire:click="cambiarFecha('{{ $row['fecha_operacion'] }}')" class="hover:bg-blue-100 cursor-pointer">
                            <td class="px-3 py-2 whitespace-nowrap text-gray-900 font-medium">
                                {{ \Carbon\Carbon::parse($row['fecha_operacion'])->format('d/m/Y') }}
                            </td>
                            <td class="px-3 py-2 text-right">{{ number_format($row['medias_canal_1']) }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($row['medias_canal_2']) }}</td>
                            <td class="px-3 py-2 text-right font-semibold text-blue-600">{{ number_format($row['total_hallazgos']) }}</td>
                            <td class="px-3 py-2 text-right">
                                <span class="inline-flex items-center gap-1">
                                    @if(\App\Livewire\IndicadoresDia::cumpleMeta($row['cobertura_pct'], \App\Livewire\IndicadoresDia::META_COBERTURA))
                                        <span class="text-green-600" title="Dentro de meta">✓</span>
                                    @else
                                        <span class="text-red-600" title="Supera meta">!</span>
                                    @endif
                                    {{ number_format($row['cobertura_pct'], 2) }}%
                                </span>
                            </td>
                            <td class="px-3 py-2 text-right">
                                <span class="inline-flex items-center gap-1">
                                    @if(\App\Livewire\IndicadoresDia::cumpleMeta($row['hematoma_pct'], \App\Livewire\IndicadoresDia::META_HEMATOMA))
                                        <span class="text-green-600" title="Dentro de meta">✓</span>
                                    @else
                                        <span class="text-red-600" title="Supera meta">!</span>
                                    @endif
                                    {{ number_format($row['hematoma_pct'], 2) }}%
                                </span>
                            </td>
                            <td class="px-3 py-2 text-right">
                                <span class="inline-flex items-center gap-1">
                                    @if(\App\Livewire\IndicadoresDia::cumpleMeta($row['cortes_pct'], \App\Livewire\IndicadoresDia::META_CORTES_PIERNA))
                                        <span class="text-green-600" title="Dentro de meta">✓</span>
                                    @else
                                        <span class="text-red-600" title="Supera meta">!</span>
                                    @endif
                                    {{ number_format($row['cortes_pct'], 2) }}%
                                </span>
                            </td>
                            <td class="px-3 py-2 text-right">
                                <span class="inline-flex items-center gap-1">
                                    @if(\App\Livewire\IndicadoresDia::cumpleMeta($row['sobrebarriga_pct'], \App\Livewire\IndicadoresDia::META_SOBREBARRIGA))
                                        <span class="text-green-600" title="Dentro de meta">✓</span>
                                    @else
                                        <span class="text-red-600" title="Supera meta">!</span>
                                    @endif
                                    {{ number_format($row['sobrebarriga_pct'], 2) }}%
                                </span>
                            </td>
                            <td class="px-3 py-2 text-right text-gray-600">{{ number_format($promedioMes, 2) }}%</td>
                            <td class="px-3 py-2 text-right font-semibold text-blue-600">{{ number_format($row['participacion_total'], 2) }}%</td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-600">{{ strtoupper(\Carbon\Carbon::create($row['año'], (int)$row['mes'], 1)->locale('es')->isoFormat('MMM')) }}</td>
                            <td class="px-3 py-2 text-right text-gray-600">{{ number_format($row['año']) }}</td>
                            <td class="px-3 py-2 text-right text-gray-500">{{ number_format(\App\Livewire\IndicadoresDia::META_COBERTURA, 2) }}%</td>
                            <td class="px-3 py-2 text-right text-gray-500">{{ number_format(\App\Livewire\IndicadoresDia::META_SOBREBARRIGA, 2) }}%</td>
                            <td class="px-3 py-2 text-right text-gray-500">{{ number_format(\App\Livewire\IndicadoresDia::META_HEMATOMA, 2) }}%</td>
                            <td class="px-3 py-2 text-right text-gray-500">{{ number_format(\App\Livewire\IndicadoresDia::META_CORTES_PIERNA, 2) }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="16" class="px-3 py-8 text-center text-gray-500">
                                No hay indicadores registrados para este mes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Detalle del día seleccionado (opcional) --}}
    @if($indicadores)
        <div class="mb-4">
            <h3 class="text-lg font-bold text-gray-700 mb-2">Indicadores del día {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</h3>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
                <div class="font-bold text-sm text-gray-600">ANIMALES PROCESADOS</div>
                <div class="text-2xl font-extrabold text-gray-900">{{ number_format($indicadores->animales_procesados ?? 0) }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
                <div class="font-bold text-sm text-gray-600">TOTAL HALLAZGOS</div>
                <div class="text-2xl font-extrabold text-gray-900">{{ number_format($indicadores->total_hallazgos ?? 0) }}</div>
                <div class="text-xs text-gray-500">En {{ number_format($indicadores->medias_canales_total ?? 0) }} medias canales</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-purple-500">
                <div class="font-bold text-sm text-gray-600">PARTICIPACIÓN</div>
                <div class="text-2xl font-extrabold text-purple-600">{{ number_format($indicadores->participacion_total ?? 0, 2) }}%</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-red-500">
                <div class="font-bold text-sm text-gray-600">HALLAZGOS POR PRODUCTO</div>
                <div class="text-lg font-bold text-gray-900">
                    Media Canal 1: {{ number_format($indicadores->medias_canal_1 ?? 0) }} | Media Canal 2: {{ number_format($indicadores->medias_canal_2 ?? 0) }}
                </div>
            </div>
        </div>
        @if(count($hallazgosPorTipo) > 0)
            <div class="mt-4">
                <h4 class="font-bold text-gray-700 mb-2">Desglose de Hallazgos del día</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
                    @foreach($hallazgosPorTipo as $hallazgo)
                        <div class="bg-gray-50 p-3 rounded-lg text-center shadow-sm">
                            <div class="font-semibold text-gray-600 text-sm">{{ $hallazgo['nombre'] }}</div>
                            <div class="text-xl font-bold text-gray-800">{{ $hallazgo['total'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @else
        <div class="text-center py-6 px-4 bg-gray-50 rounded-lg border border-gray-200">
            <p class="text-gray-600">Selecciona una fecha o registra hallazgos para ver el detalle del día.</p>
        </div>
    @endif
</div>
