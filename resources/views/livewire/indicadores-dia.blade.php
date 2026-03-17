<div wire:poll.3s="actualizarDespuesDeRegistro" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    {{-- Header dinámico --}}
    <div class="mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <img src="{{ asset('logo.png') }}" alt="Logo" class="h-12 w-auto object-contain">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">📊 Indicador Diario</h1>
                        <p class="text-gray-500 mt-1 text-sm">Monitoreo en tiempo real de hallazgos</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500">Mes seleccionado:</p>
                    <p class="text-xl font-bold text-blue-600">{{ \Carbon\Carbon::create($anio, $mes, 1)->locale('es')->isoFormat('MMMM Y') }}</p>
                </div>
            </div>
        </div>

        {{-- Filtros mejorados --}}
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-gray-700">📅 Mes:</label>
                    <select wire:model.live="mes" wire:change="cambiarMesAnio" class="px-3 py-2 rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 text-sm cursor-pointer transition">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->locale('es')->isoFormat('MMMM') }}</option>
                        @endfor
                    </select>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-gray-700">📆 Año:</label>
                    <select wire:model.live="anio" wire:change="cambiarMesAnio" class="px-3 py-2 rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 text-sm cursor-pointer transition">
                        @for($a = now()->year - 2; $a <= now()->year + 1; $a++)
                            <option value="{{ $a }}">{{ $a }}</option>
                        @endfor
                    </select>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-gray-700">📍 Día específico:</label>
                    <input type="date" wire:model.live="fecha" wire:change="cargarIndicadores()" class="px-3 py-2 rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 text-sm transition">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-gray-700">📌 Estado:</label>
                    <div class="px-3 py-2 bg-blue-50 rounded-lg border border-blue-200 text-center">
                        <span class="text-sm font-bold text-blue-700">{{ count($historial) }} registros</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Resumen de Metas --}}
    @if(count($historial) > 0)
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        {{-- META COBERTURA --}}
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg shadow-md p-5 border-l-4 border-orange-500 transform transition hover:scale-105">
            <div class="flex justify-between items-start mb-3">
                <h3 class="font-bold text-gray-800 text-sm">🧈 COBERTURA GRASA</h3>
                <span class="bg-orange-500 text-white text-xs font-bold px-2 py-1 rounded">META: {{ \App\Livewire\IndicadoresDia::META_COBERTURA }}%</span>
            </div>
            @php
                $coberturaData = array_filter($historial, function($row) {
                    return !\App\Livewire\IndicadoresDia::cumpleMeta($row['cobertura_pct'], \App\Livewire\IndicadoresDia::META_COBERTURA);
                });
            @endphp
            <div class="text-3xl font-bold text-orange-600 mb-2">{{ count($coberturaData) }}/{{ count($historial) }}</div>
            <div class="w-full bg-gray-300 rounded-full h-2">
                <div class="bg-orange-500 h-2 rounded-full" style="width: {{ (count($coberturaData) / count($historial)) * 100 }}%"></div>
            </div>
            <p class="text-xs text-gray-600 mt-2">{{ count($coberturaData) }} días fuera de meta</p>
        </div>

        {{-- META HEMATOMAS --}}
        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg shadow-md p-5 border-l-4 border-red-500 transform transition hover:scale-105">
            <div class="flex justify-between items-start mb-3">
                <h3 class="font-bold text-gray-800 text-sm">🩸 HEMATOMAS</h3>
                <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">META: {{ \App\Livewire\IndicadoresDia::META_HEMATOMA }}%</span>
            </div>
            @php
                $hematomiasData = array_filter($historial, function($row) {
                    return !\App\Livewire\IndicadoresDia::cumpleMeta($row['hematoma_pct'], \App\Livewire\IndicadoresDia::META_HEMATOMA);
                });
            @endphp
            <div class="text-3xl font-bold text-red-600 mb-2">{{ count($hematomiasData) }}/{{ count($historial) }}</div>
            <div class="w-full bg-gray-300 rounded-full h-2">
                <div class="bg-red-500 h-2 rounded-full" style="width: {{ (count($hematomiasData) / count($historial)) * 100 }}%"></div>
            </div>
            <p class="text-xs text-gray-600 mt-2">{{ count($hematomiasData) }} días fuera de meta</p>
        </div>

        {{-- META CORTES EN PIERNA --}}
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg shadow-md p-5 border-l-4 border-yellow-500 transform transition hover:scale-105">
            <div class="flex justify-between items-start mb-3">
                <h3 class="font-bold text-gray-800 text-sm">🦵 CORTES EN PIERNA</h3>
                <span class="bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded">META: {{ \App\Livewire\IndicadoresDia::META_CORTES_PIERNA }}%</span>
            </div>
            @php
                $cortesData = array_filter($historial, function($row) {
                    return !\App\Livewire\IndicadoresDia::cumpleMeta($row['cortes_pct'], \App\Livewire\IndicadoresDia::META_CORTES_PIERNA);
                });
            @endphp
            <div class="text-3xl font-bold text-yellow-600 mb-2">{{ count($cortesData) }}/{{ count($historial) }}</div>
            <div class="w-full bg-gray-300 rounded-full h-2">
                <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ (count($cortesData) / count($historial)) * 100 }}%"></div>
            </div>
            <p class="text-xs text-gray-600 mt-2">{{ count($cortesData) }} días fuera de meta</p>
        </div>

        {{-- META SOBREBARRIGA ROTA --}}
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg shadow-md p-5 border-l-4 border-purple-500 transform transition hover:scale-105">
            <div class="flex justify-between items-start mb-3">
                <h3 class="font-bold text-gray-800 text-sm">🐄 SOBREBARRIGA ROTA</h3>
                <span class="bg-purple-500 text-white text-xs font-bold px-2 py-1 rounded">META: {{ \App\Livewire\IndicadoresDia::META_SOBREBARRIGA }}%</span>
            </div>
            @php
                $sobrebarrigaData = array_filter($historial, function($row) {
                    return !\App\Livewire\IndicadoresDia::cumpleMeta($row['sobrebarriga_pct'], \App\Livewire\IndicadoresDia::META_SOBREBARRIGA);
                });
            @endphp
            <div class="text-3xl font-bold text-purple-600 mb-2">{{ count($sobrebarrigaData) }}/{{ count($historial) }}</div>
            <div class="w-full bg-gray-300 rounded-full h-2">
                <div class="bg-purple-500 h-2 rounded-full" style="width: {{ (count($sobrebarrigaData) / count($historial)) * 100 }}%"></div>
            </div>
            <p class="text-xs text-gray-600 mt-2">{{ count($sobrebarrigaData) }} días fuera de meta</p>
        </div>
    </div>
    @endif

    {{-- Tabla Historial de Liberación mejorada --}}
    <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg mb-6 border border-gray-200">
        <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
            <h3 class="text-xl font-bold text-gray-900">📋 Historial Mensual de Liberación</h3>
            <p class="text-sm text-gray-600 mt-1">Haz clic en cualquier fila para ver detalles del día</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100 border-b-2 border-gray-300">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Fecha</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">1/2 Canal 1</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">1/2 Canal 2</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Total Hallazgos</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-orange-700 uppercase tracking-wider">🧈 Cobertura</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-red-700 uppercase tracking-wider">🩸 Hematomas</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-yellow-700 uppercase tracking-wider">🦵 Cortes P</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-purple-700 uppercase tracking-wider">🐄 Sobrebarriga</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-blue-700 uppercase tracking-wider">% Participación</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($historial as $row)
                        <tr wire:click="actualizarFecha('{{ $row['fecha_operacion'] }}')" class="hover:bg-blue-50 cursor-pointer transition duration-200 {{ \Carbon\Carbon::parse($row['fecha_operacion'])->format('Y-m-d') == $fecha ? 'bg-blue-100 font-bold' : '' }}">
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">
                                <span class="inline-flex items-center gap-2">
                                    📅 {{ \Carbon\Carbon::parse($row['fecha_operacion'])->locale('es')->isoFormat('dddd, DD MMM') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-sm text-gray-700 font-medium">{{ $row['medias_canal_1'] }}</td>
                            <td class="px-4 py-3 text-center text-sm text-gray-700 font-medium">{{ $row['medias_canal_2'] }}</td>
                            <td class="px-4 py-3 text-center text-sm">
                                <span class="inline-flex items-center justify-center gap-1 px-3 py-1 bg-blue-100 text-blue-700 rounded-full font-bold">{{ $row['total_hallazgos'] }}</span>
                            </td>
                            <td class="px-4 py-3 text-center text-sm">
                                <span class="inline-flex flex-col items-center gap-1">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold {{ \App\Livewire\IndicadoresDia::cumpleMeta($row['cobertura_pct'], \App\Livewire\IndicadoresDia::META_COBERTURA) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ number_format($row['cobertura_pct'], 2) }}%
                                    </span>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-sm">
                                <span class="inline-flex flex-col items-center gap-1">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold {{ \App\Livewire\IndicadoresDia::cumpleMeta($row['hematoma_pct'], \App\Livewire\IndicadoresDia::META_HEMATOMA) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ number_format($row['hematoma_pct'], 2) }}%
                                    </span>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-sm">
                                <span class="inline-flex flex-col items-center gap-1">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold {{ \App\Livewire\IndicadoresDia::cumpleMeta($row['cortes_pct'], \App\Livewire\IndicadoresDia::META_CORTES_PIERNA) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ number_format($row['cortes_pct'], 2) }}%
                                    </span>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-sm">
                                <span class="inline-flex flex-col items-center gap-1">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold {{ \App\Livewire\IndicadoresDia::cumpleMeta($row['sobrebarriga_pct'], \App\Livewire\IndicadoresDia::META_SOBREBARRIGA) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ number_format($row['sobrebarriga_pct'], 2) }}%
                                    </span>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-sm">
                                <span class="inline-flex items-center gap-2 px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full font-bold">
                                    📊 {{ number_format($row['participacion_total'], 1) }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="text-4xl">📭</span>
                                    <p class="text-lg font-medium">No hay indicadores registrados para este mes.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Detalle del día seleccionado mejorado --}}
    @if($indicadores)
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg shadow-lg p-6 text-white mb-6">
            <h3 class="text-2xl font-bold mb-2">📍 Indicadores del {{ \Carbon\Carbon::parse($fecha)->locale('es')->isoFormat('dddd, DD MMMM YYYY') }}</h3>
            <p class="text-blue-100">Haz clic en una fila de la tabla para cambiar el día</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-lg shadow-md border-l-4 border-blue-500 transform transition hover:scale-105">
                <div class="font-bold text-sm text-gray-700 mb-2">🐄 ANIMALES PROCESADOS</div>
                <div class="text-4xl font-extrabold text-blue-600 mb-1">{{ number_format($indicadores->animales_procesados ?? 0) }}</div>
                <div class="text-xs text-gray-600">{{ number_format($indicadores->medias_canales_total ?? 0) }} medias canales</div>
            </div>
            
            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-6 rounded-lg shadow-md border-l-4 border-yellow-500 transform transition hover:scale-105">
                <div class="font-bold text-sm text-gray-700 mb-2">⚠️ TOTAL HALLAZGOS</div>
                <div class="text-4xl font-extrabold text-yellow-600 mb-1">{{ number_format($indicadores->total_hallazgos ?? 0) }}</div>
                <div class="w-full bg-gray-300 rounded-full h-2 mt-2">
                    <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ ($indicadores->total_hallazgos ?? 0) > 0 ? min((($indicadores->total_hallazgos ?? 0) / (($indicadores->medias_canales_total ?? 1) / 10)) * 100, 100) : 0 }}%"></div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-lg shadow-md border-l-4 border-purple-500 transform transition hover:scale-105">
                <div class="font-bold text-sm text-gray-700 mb-2">📈 PARTICIPACIÓN TOTAL</div>
                <div class="text-4xl font-extrabold text-purple-600 mb-1">{{ number_format($indicadores->participacion_total ?? 0, 2) }}%</div>
                <div class="w-full bg-gray-300 rounded-full h-2 mt-2">
                    <div class="bg-purple-500 h-2 rounded-full" style="width: {{ min($indicadores->participacion_total ?? 0, 100) }}%"></div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 p-6 rounded-lg shadow-md border-l-4 border-indigo-500 transform transition hover:scale-105">
                <div class="font-bold text-sm text-gray-700 mb-2">🏭 POR PRODUCTO</div>
                <div class="grid grid-cols-2 gap-2 text-center">
                    <div class="bg-white rounded p-2">
                        <div class="text-xs text-gray-600">Canal 1</div>
                        <div class="text-2xl font-bold text-indigo-600">{{ number_format($indicadores->medias_canal_1 ?? 0) }}</div>
                    </div>
                    <div class="bg-white rounded p-2">
                        <div class="text-xs text-gray-600">Canal 2</div>
                        <div class="text-2xl font-bold text-indigo-600">{{ number_format($indicadores->medias_canal_2 ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if(count($hallazgosPorTipo) > 0)
            <div class="bg-white rounded-lg shadow-lg p-6 border border-gray-200">
                <h4 class="font-bold text-lg text-gray-900 mb-4">📊 Desglose Detallado de Hallazgos</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
                    @foreach($hallazgosPorTipo as $hallazgo)
                        @if(!in_array(strtoupper($hallazgo['nombre']), ['MATERIA FECAL', 'CONTENIDO RUMINAL', 'LECHE VISIBLE']))
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 p-4 rounded-lg text-center shadow-sm border border-gray-200 transform transition hover:scale-105 hover:shadow-md">
                            <div class="font-semibold text-gray-700 text-xs mb-2 leading-tight">{{ $hallazgo['nombre'] }}</div>
                            <div class="text-3xl font-bold text-gray-800">{{ $hallazgo['total'] }}</div>
                            <div class="mt-2 pt-2 border-t border-gray-300">
                                <span class="text-xs text-gray-600 font-medium">{{ $indicadores->medias_canales_total > 0 ? number_format(($hallazgo['total'] / $indicadores->medias_canales_total) * 100, 2) : 0 }}%</span>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    @else
        <div class="text-center py-12 px-4 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg border-2 border-dashed border-gray-300">
            <p class="text-2xl mb-2">👆</p>
            <p class="text-gray-600 font-medium">Selecciona una fecha en la tabla para ver el detalle del día</p>
        </div>
    @endif

    {{-- RESUMEN METAS TOLERANCIA CERO --}}
    @if(count($resumenToleranciaZero) > 0)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8 mt-8">
        {{-- META CONTENIDO RUMINAL --}}
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg shadow-md p-5 border-l-4 border-orange-500 transform transition hover:scale-105">
            <div class="flex justify-between items-start mb-3">
                <h3 class="font-bold text-gray-800 text-sm">🟠 CONTENIDO RUMINAL</h3>
                <span class="bg-orange-500 text-white text-xs font-bold px-2 py-1 rounded">META: {{ \App\Livewire\IndicadoresDia::META_TC }}%</span>
            </div>
            @php
                $crFuera = array_filter($resumenToleranciaZero, function($row) {
                    return !\App\Livewire\IndicadoresDia::cumpleMeta($row['contenido_ruminal_pct'], \App\Livewire\IndicadoresDia::META_TC);
                });
            @endphp
            <div class="text-3xl font-bold text-orange-600 mb-2">{{ count($crFuera) }}/{{ count($resumenToleranciaZero) }}</div>
            <div class="w-full bg-gray-300 rounded-full h-2">
                <div class="bg-orange-500 h-2 rounded-full" style="width: {{ count($resumenToleranciaZero) > 0 ? (count($crFuera) / count($resumenToleranciaZero)) * 100 : 0 }}%"></div>
            </div>
            <p class="text-xs text-gray-600 mt-2">{{ count($crFuera) }} días fuera de meta</p>
        </div>

        {{-- META MATERIA FECAL --}}
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg shadow-md p-5 border-l-4 border-yellow-500 transform transition hover:scale-105">
            <div class="flex justify-between items-start mb-3">
                <h3 class="font-bold text-gray-800 text-sm">🟡 MATERIA FECAL</h3>
                <span class="bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded">META: {{ \App\Livewire\IndicadoresDia::META_TC }}%</span>
            </div>
            @php
                $mfFuera = array_filter($resumenToleranciaZero, function($row) {
                    return !\App\Livewire\IndicadoresDia::cumpleMeta($row['materia_fecal_pct'], \App\Livewire\IndicadoresDia::META_TC);
                });
            @endphp
            <div class="text-3xl font-bold text-yellow-600 mb-2">{{ count($mfFuera) }}/{{ count($resumenToleranciaZero) }}</div>
            <div class="w-full bg-gray-300 rounded-full h-2">
                <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ count($resumenToleranciaZero) > 0 ? (count($mfFuera) / count($resumenToleranciaZero)) * 100 : 0 }}%"></div>
            </div>
            <p class="text-xs text-gray-600 mt-2">{{ count($mfFuera) }} días fuera de meta</p>
        </div>

        {{-- META LECHE VISIBLE --}}
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow-md p-5 border-l-4 border-blue-500 transform transition hover:scale-105">
            <div class="flex justify-between items-start mb-3">
                <h3 class="font-bold text-gray-800 text-sm">🔵 LECHE VISIBLE</h3>
                <span class="bg-blue-500 text-white text-xs font-bold px-2 py-1 rounded">META: {{ \App\Livewire\IndicadoresDia::META_TC }}%</span>
            </div>
            @php
                $lvFuera = array_filter($resumenToleranciaZero, function($row) {
                    return !\App\Livewire\IndicadoresDia::cumpleMeta($row['leche_visible_pct'], \App\Livewire\IndicadoresDia::META_TC);
                });
            @endphp
            <div class="text-3xl font-bold text-blue-600 mb-2">{{ count($lvFuera) }}/{{ count($resumenToleranciaZero) }}</div>
            <div class="w-full bg-gray-300 rounded-full h-2">
                <div class="bg-blue-500 h-2 rounded-full" style="width: {{ count($resumenToleranciaZero) > 0 ? (count($lvFuera) / count($resumenToleranciaZero)) * 100 : 0 }}%"></div>
            </div>
            <p class="text-xs text-gray-600 mt-2">{{ count($lvFuera) }} días fuera de meta</p>
        </div>
    </div>
    @endif

    {{-- TABLA HALLAZGOS TOLERANCIA CERO --}}
    <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg border border-red-300">
        <div class="p-6 border-b border-red-300 bg-gradient-to-r from-red-50 to-orange-50">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-xl font-bold text-red-700 flex items-center gap-2">
                        <span>🚨</span>
                        Hallazgos Tolerancia Cero del Mes
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">{{ \Carbon\Carbon::create($anio, $mes, 1)->locale('es')->isoFormat('MMMM Y') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-3xl font-bold text-red-600">{{ $totalHallazgosTC }}</p>
                    <p class="text-xs text-gray-600">Total registros</p>
                </div>
            </div>
        </div>

        {{-- Tabla de Registros --}}
        @if(count($resumenToleranciaZero) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-red-100 border-b-2 border-red-300">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-red-900 uppercase tracking-wider">Fecha</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-red-900 uppercase tracking-wider">Cuarto Anterior</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-red-900 uppercase tracking-wider">Cuarto Posterior</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-red-900 uppercase tracking-wider">Total Hallazgos</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-red-900 uppercase tracking-wider">Contenido Ruminal</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-red-900 uppercase tracking-wider">Materia Fecal</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-red-900 uppercase tracking-wider">Leche Visible</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-red-900 uppercase tracking-wider">Participación</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($resumenToleranciaZero as $fila)
                            <tr wire:click="seleccionarDiaTC('{{ $fila['fecha_operacion'] }}')" class="hover:bg-red-50 cursor-pointer transition duration-150 {{ $detalleTCDia && $detalleTCDia['fecha_operacion'] === $fila['fecha_operacion'] ? 'bg-red-100 font-bold' : '' }}">
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                    📅 {{ \Carbon\Carbon::parse($fila['fecha_operacion'])->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-center text-sm text-gray-700 font-semibold">
                                    {{ $fila['cuarto_anterior'] }}
                                </td>
                                <td class="px-4 py-3 text-center text-sm text-gray-700 font-semibold">
                                    {{ $fila['cuarto_posterior'] }}
                                </td>
                                <td class="px-4 py-3 text-center text-sm font-bold text-red-700">
                                    {{ $fila['total_hallazgos'] }}
                                </td>
                                <td class="px-4 py-3 text-center text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold {{ \App\Livewire\IndicadoresDia::cumpleMeta($fila['contenido_ruminal_pct'], \App\Livewire\IndicadoresDia::META_TC) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ number_format($fila['contenido_ruminal_pct'], 2) }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold {{ \App\Livewire\IndicadoresDia::cumpleMeta($fila['materia_fecal_pct'], \App\Livewire\IndicadoresDia::META_TC) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ number_format($fila['materia_fecal_pct'], 2) }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold {{ \App\Livewire\IndicadoresDia::cumpleMeta($fila['leche_visible_pct'], \App\Livewire\IndicadoresDia::META_TC) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ number_format($fila['leche_visible_pct'], 2) }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold {{ \App\Livewire\IndicadoresDia::cumpleMeta($fila['participacion'], \App\Livewire\IndicadoresDia::META_TC) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ number_format($fila['participacion'], 2) }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-8 text-center">
                <p class="text-gray-500 text-lg">ℹ️ Sin registros de tolerancia cero en este período</p>
            </div>
        @endif
    </div>

    {{-- Detalle del día seleccionado TC --}}
    @if($detalleTCDia)
        <div class="rounded-lg shadow-lg p-6 mb-6 mt-6" style="background: linear-gradient(to right, #dc2626, #b91c1c); color: #ffffff;">
            <h3 class="text-2xl font-bold mb-2">🚨 Tolerancia Cero del {{ \Carbon\Carbon::parse($detalleTCDia['fecha_operacion'])->locale('es')->isoFormat('dddd, DD MMMM YYYY') }}</h3>
            <p style="color: #fca5a5;">Haz clic en una fila de la tabla para cambiar el día</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
            <div class="bg-gradient-to-br from-red-50 to-red-100 p-6 rounded-lg shadow-md border-l-4 border-red-500 transform transition hover:scale-105">
                <div class="font-bold text-sm text-gray-700 mb-2">⚠️ TOTAL HALLAZGOS TC</div>
                <div class="text-4xl font-extrabold text-red-600 mb-1">{{ $detalleTCDia['total_hallazgos'] }}</div>
                <div class="text-xs text-gray-600">Cuarto Ant: {{ $detalleTCDia['cuarto_anterior'] }} | Cuarto Post: {{ $detalleTCDia['cuarto_posterior'] }}</div>
            </div>

            <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-6 rounded-lg shadow-md border-l-4 border-orange-500 transform transition hover:scale-105">
                <div class="font-bold text-sm text-gray-700 mb-2">🟠 CONTENIDO RUMINAL</div>
                <div class="text-4xl font-extrabold text-orange-600 mb-1">{{ number_format($detalleTCDia['contenido_ruminal_pct'], 2) }}%</div>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs text-gray-600">{{ $detalleTCDia['contenido_ruminal'] }} hallazgos</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ \App\Livewire\IndicadoresDia::cumpleMeta($detalleTCDia['contenido_ruminal_pct'], \App\Livewire\IndicadoresDia::META_TC) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ \App\Livewire\IndicadoresDia::cumpleMeta($detalleTCDia['contenido_ruminal_pct'], \App\Livewire\IndicadoresDia::META_TC) ? '✅ Cumple' : '❌ Fuera' }}
                    </span>
                </div>
            </div>

            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-6 rounded-lg shadow-md border-l-4 border-yellow-500 transform transition hover:scale-105">
                <div class="font-bold text-sm text-gray-700 mb-2">🟡 MATERIA FECAL</div>
                <div class="text-4xl font-extrabold text-yellow-600 mb-1">{{ number_format($detalleTCDia['materia_fecal_pct'], 2) }}%</div>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs text-gray-600">{{ $detalleTCDia['materia_fecal'] }} hallazgos</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ \App\Livewire\IndicadoresDia::cumpleMeta($detalleTCDia['materia_fecal_pct'], \App\Livewire\IndicadoresDia::META_TC) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ \App\Livewire\IndicadoresDia::cumpleMeta($detalleTCDia['materia_fecal_pct'], \App\Livewire\IndicadoresDia::META_TC) ? '✅ Cumple' : '❌ Fuera' }}
                    </span>
                </div>
            </div>

            <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-lg shadow-md border-l-4 border-blue-500 transform transition hover:scale-105">
                <div class="font-bold text-sm text-gray-700 mb-2">🔵 LECHE VISIBLE</div>
                <div class="text-4xl font-extrabold text-blue-600 mb-1">{{ number_format($detalleTCDia['leche_visible_pct'], 2) }}%</div>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs text-gray-600">{{ $detalleTCDia['leche_visible'] }} hallazgos</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ \App\Livewire\IndicadoresDia::cumpleMeta($detalleTCDia['leche_visible_pct'], \App\Livewire\IndicadoresDia::META_TC) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ \App\Livewire\IndicadoresDia::cumpleMeta($detalleTCDia['leche_visible_pct'], \App\Livewire\IndicadoresDia::META_TC) ? '✅ Cumple' : '❌ Fuera' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Barra de participación TC del día --}}
        <div class="bg-white rounded-lg shadow-lg p-6 border border-red-200 mb-6">
            <h4 class="font-bold text-lg text-gray-900 mb-4">📊 Participación TC del Día</h4>
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700">Participación Total TC</span>
                        <span class="text-sm font-bold {{ \App\Livewire\IndicadoresDia::cumpleMeta($detalleTCDia['participacion'], \App\Livewire\IndicadoresDia::META_TC) ? 'text-green-600' : 'text-red-600' }}">{{ number_format($detalleTCDia['participacion'], 2) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-4">
                        <div class="h-4 rounded-full {{ \App\Livewire\IndicadoresDia::cumpleMeta($detalleTCDia['participacion'], \App\Livewire\IndicadoresDia::META_TC) ? 'bg-green-500' : 'bg-red-500' }}" style="width: {{ min($detalleTCDia['participacion'] * 10, 100) }}%"></div>
                    </div>
                    <div class="flex justify-between mt-1">
                        <span class="text-xs text-gray-500">0%</span>
                        <span class="text-xs text-gray-500">Meta: {{ \App\Livewire\IndicadoresDia::META_TC }}%</span>
                        <span class="text-xs text-gray-500">10%</span>
                    </div>
                </div>
            </div>
        </div>
    @elseif(count($resumenToleranciaZero) > 0)
        <div class="text-center py-8 px-4 bg-gradient-to-br from-red-50 to-orange-50 rounded-lg border-2 border-dashed border-red-300 mt-6 mb-6">
            <p class="text-2xl mb-2">👆</p>
            <p class="text-gray-600 font-medium">Selecciona una fecha en la tabla de Tolerancia Cero para ver el detalle del día</p>
        </div>
    @endif
</div>
