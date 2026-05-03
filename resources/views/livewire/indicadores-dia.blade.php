<div wire:poll.3s="actualizarDespuesDeRegistro" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    {{-- Header dinámico --}}
    <div class="mb-8">
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <img src="{{ asset('logo.png') }}" alt="Logo" class="h-6 sm:h-10 max-w-[40px] sm:max-w-[80px] object-contain flex-shrink-0">
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-2xl font-bold text-gray-900 truncate">📊 Indicador Diario</h1>
                    <p class="text-gray-500 mt-1 text-xs sm:text-sm">Monitoreo en tiempo real de hallazgos</p>
                </div>
            </div>
            <div class="text-left sm:text-right flex-shrink-0">
                <p class="text-xs text-gray-500">Mes seleccionado:</p>
                <p class="text-base sm:text-xl font-bold text-blue-600">{{ \Carbon\Carbon::create($anio, $mes, 1)->locale('es')->isoFormat('MMMM Y') }}</p>
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
            <p class="text-sm text-gray-600 mt-1">Haz clic en un día: el resumen se despliega justo debajo de esa fila</p>
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
                        @php
                            $libDiaKey = \Carbon\Carbon::parse($row['fecha_operacion'])->format('Y-m-d');
                            $libDiaId = str_replace('-', '_', $libDiaKey);
                            $filaLiberActiva = $libDiaKey === \Carbon\Carbon::parse($fecha)->format('Y-m-d');
                        @endphp
                        <tr wire:click="actualizarFecha('{{ $row['fecha_operacion'] }}')" class="hover:bg-blue-50 cursor-pointer transition duration-200 {{ $filaLiberActiva ? 'bg-blue-100 font-bold' : '' }}">
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
                                    <span class="px-2 py-1 rounded-full text-xs font-bold border-2 {{ \App\Livewire\IndicadoresDia::cumpleMeta($row['cobertura_pct'], \App\Livewire\IndicadoresDia::META_COBERTURA) ? 'bg-green-100 text-green-800 border-green-600' : 'bg-red-100 text-red-800 border-red-600' }}">
                                        {{ number_format($row['cobertura_pct'], 2) }}%
                                    </span>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-sm">
                                <span class="inline-flex flex-col items-center gap-1">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold border-2 {{ \App\Livewire\IndicadoresDia::cumpleMeta($row['hematoma_pct'], \App\Livewire\IndicadoresDia::META_HEMATOMA) ? 'bg-green-100 text-green-800 border-green-600' : 'bg-red-100 text-red-800 border-red-600' }}">
                                        {{ number_format($row['hematoma_pct'], 2) }}%
                                    </span>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-sm">
                                <span class="inline-flex flex-col items-center gap-1">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold border-2 {{ \App\Livewire\IndicadoresDia::cumpleMeta($row['cortes_pct'], \App\Livewire\IndicadoresDia::META_CORTES_PIERNA) ? 'bg-green-100 text-green-800 border-green-600' : 'bg-red-100 text-red-800 border-red-600' }}">
                                        {{ number_format($row['cortes_pct'], 2) }}%
                                    </span>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-sm">
                                <span class="inline-flex flex-col items-center gap-1">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold border-2 {{ \App\Livewire\IndicadoresDia::cumpleMeta($row['sobrebarriga_pct'], \App\Livewire\IndicadoresDia::META_SOBREBARRIGA) ? 'bg-green-100 text-green-800 border-green-600' : 'bg-red-100 text-red-800 border-red-600' }}">
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
                        @if($filaLiberActiva && $indicadores)
                        <tr id="lib-detalle-{{ $libDiaId }}" class="bg-slate-50" wire:click.stop>
                            <td colspan="9" class="p-0 border-t-2 border-blue-200">
                                <div class="p-4 sm:p-5 space-y-4">
                                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg shadow p-4 text-white">
                                        <h4 class="text-base sm:text-lg font-bold">📍 Indicadores del {{ \Carbon\Carbon::parse($fecha)->locale('es')->isoFormat('dddd, DD MMMM YYYY') }}</h4>
                                        <p class="text-blue-100 text-xs sm:text-sm mt-1">Resumen vinculado a esta fila</p>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg shadow border-l-4 border-blue-500">
                                            <div class="font-bold text-xs text-gray-700 mb-1">🐄 ANIMALES PROCESADOS</div>
                                            <div class="text-2xl sm:text-3xl font-extrabold text-blue-600">{{ number_format($indicadores->animales_procesados ?? 0) }}</div>
                                            <div class="text-xs text-gray-600 mt-1">{{ number_format($indicadores->medias_canales_total ?? 0) }} medias canales</div>
                                        </div>
                                        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-4 rounded-lg shadow border-l-4 border-yellow-500">
                                            <div class="font-bold text-xs text-gray-700 mb-1">⚠️ TOTAL HALLAZGOS</div>
                                            <div class="text-2xl sm:text-3xl font-extrabold text-yellow-600">{{ number_format($indicadores->total_hallazgos ?? 0) }}</div>
                                            <div class="w-full bg-gray-300 rounded-full h-1.5 mt-2">
                                                <div class="bg-yellow-500 h-1.5 rounded-full" style="width: {{ ($indicadores->total_hallazgos ?? 0) > 0 && ($indicadores->medias_canales_total ?? 0) > 0 ? min((($indicadores->total_hallazgos ?? 0) / (($indicadores->medias_canales_total ?? 0) / 10)) * 100, 100) : 0 }}%"></div>
                                            </div>
                                        </div>
                                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-lg shadow border-l-4 border-purple-500">
                                            <div class="font-bold text-xs text-gray-700 mb-1">📈 PARTICIPACIÓN TOTAL</div>
                                            <div class="text-2xl sm:text-3xl font-extrabold text-purple-600">{{ number_format($indicadores->participacion_total ?? 0, 2) }}%</div>
                                            <div class="w-full bg-gray-300 rounded-full h-1.5 mt-2">
                                                <div class="bg-purple-500 h-1.5 rounded-full" style="width: {{ min($indicadores->participacion_total ?? 0, 100) }}%"></div>
                                            </div>
                                        </div>
                                        <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 p-4 rounded-lg shadow border-l-4 border-indigo-500">
                                            <div class="font-bold text-xs text-gray-700 mb-1">🏭 POR PRODUCTO</div>
                                            <div class="grid grid-cols-2 gap-2 text-center">
                                                <div class="bg-white rounded p-2">
                                                    <div class="text-xs text-gray-600">Canal 1</div>
                                                    <div class="text-xl font-bold text-indigo-600">{{ number_format($indicadores->medias_canal_1 ?? 0) }}</div>
                                                </div>
                                                <div class="bg-white rounded p-2">
                                                    <div class="text-xs text-gray-600">Canal 2</div>
                                                    <div class="text-xl font-bold text-indigo-600">{{ number_format($indicadores->medias_canal_2 ?? 0) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if($indicadores)
                                    <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-4">
                                        <h5 class="font-bold text-sm sm:text-base text-gray-900 mb-2">📊 Desglose de hallazgos</h5>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2 items-stretch">
                                            @foreach($hallazgosPorTipo as $hallazgo)
                                                @if(!in_array(strtoupper($hallazgo['nombre']), ['MATERIA FECAL', 'CONTENIDO RUMINAL', 'LECHE VISIBLE']))
                                                @php
                                                    $pctDesglose = $indicadores->medias_canales_total > 0
                                                        ? round(($hallazgo['total'] / $indicadores->medias_canales_total) * 100, 2)
                                                        : 0.0;
                                                    $metaDesglose = \App\Livewire\IndicadoresDia::metaHallazgoPorNombre((string) $hallazgo['nombre']);
                                                    $tieneMetaDesglose = $metaDesglose !== null;
                                                    $cumpleMetaDesglose = $tieneMetaDesglose && \App\Livewire\IndicadoresDia::cumpleMeta((float) $pctDesglose, $metaDesglose);
                                                    // Borde por style inline (siempre visible; no depende del purge de Tailwind)
                                                    $bordeDesgloseStyle = ! $tieneMetaDesglose
                                                        ? 'border: 2px solid #d1d5db;'
                                                        : ($cumpleMetaDesglose
                                                            ? 'border: 3px solid #059669;'
                                                            : 'border: 3px solid #dc2626;');
                                                @endphp
                                                <div @class([
                                                    'h-full min-h-[7rem] flex flex-col justify-center items-center gap-2 sm:gap-2.5 p-2 sm:p-3 rounded-lg text-center shadow-md box-border',
                                                    'bg-gradient-to-br from-gray-50 to-gray-100 text-gray-900' => ! $tieneMetaDesglose,
                                                    'bg-gradient-to-br from-emerald-50 via-emerald-50 to-teal-100 text-emerald-900' => $tieneMetaDesglose && $cumpleMetaDesglose,
                                                    'bg-gradient-to-br from-red-50 via-rose-50 to-red-100 text-red-900' => $tieneMetaDesglose && ! $cumpleMetaDesglose,
                                                ]) style="{{ $bordeDesgloseStyle }}">
                                                    <div @class([
                                                        'font-semibold text-xs leading-tight px-0.5',
                                                        'text-gray-700' => ! $tieneMetaDesglose,
                                                        'text-emerald-900' => $tieneMetaDesglose && $cumpleMetaDesglose,
                                                        'text-red-900' => $tieneMetaDesglose && ! $cumpleMetaDesglose,
                                                    ])>{{ $hallazgo['nombre'] }}</div>
                                                    <div @class([
                                                        'text-xl sm:text-2xl font-bold tabular-nums leading-none',
                                                        'text-gray-800' => ! $tieneMetaDesglose,
                                                        'text-emerald-900' => $tieneMetaDesglose && $cumpleMetaDesglose,
                                                        'text-red-900' => $tieneMetaDesglose && ! $cumpleMetaDesglose,
                                                    ])>{{ $hallazgo['total'] }}</div>
                                                    <div @class([
                                                        'pt-2 border-t text-xs font-semibold tabular-nums min-w-[4.25rem] mx-auto',
                                                        'border-gray-200 text-gray-600' => ! $tieneMetaDesglose,
                                                        'border-emerald-300/90 text-emerald-900 underline decoration-2 underline-offset-4 decoration-emerald-600' => $tieneMetaDesglose && $cumpleMetaDesglose,
                                                        'border-red-300/90 text-red-900 underline decoration-2 underline-offset-4 decoration-red-600' => $tieneMetaDesglose && ! $cumpleMetaDesglose,
                                                    ])>{{ number_format($pctDesglose, 2) }}%</div>
                                                </div>
                                                @endif
                                            @endforeach

                                            {{-- Misma fila que el desglose: quinta tarjeta Responsables --}}
                                            <div class="flex flex-col h-full min-h-[8.5rem] rounded-lg border-2 border-blue-600 bg-gradient-to-b from-sky-50 to-white p-2 sm:p-2.5 shadow-md text-left ring-1 ring-blue-200/80">
                                                <div class="text-[10px] sm:text-xs font-bold text-blue-900 text-center border-b border-blue-200/90 pb-1.5 mb-2 shrink-0">Responsables</div>
                                                <div class="space-y-1.5 flex-1 min-h-0 overflow-y-auto text-[10px] sm:text-[11px] text-slate-700 leading-snug">
                                                    @foreach($responsablesDesglose as $resp)
                                                        <p class="leading-snug"><span class="font-semibold text-slate-900">{{ $resp['titulo'] }}:</span> {{ $resp['nombres'] }}</p>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endif
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
                    <p class="text-xs text-red-800/80 mt-1">Haz clic en un día: el resumen se despliega debajo de esa fila</p>
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
                            @php
                                $tcDiaId = str_replace('-', '_', $fila['fecha_operacion']);
                                $filaTcActiva = $detalleTCDia && $detalleTCDia['fecha_operacion'] === $fila['fecha_operacion'];
                            @endphp
                            <tr wire:click="seleccionarDiaTC('{{ $fila['fecha_operacion'] }}')" class="hover:bg-red-50 cursor-pointer transition duration-150 {{ $filaTcActiva ? 'bg-red-100 font-bold' : '' }}">
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
                                    <span class="px-2 py-1 rounded-full text-xs font-bold border-2 {{ \App\Livewire\IndicadoresDia::cumpleMeta($fila['contenido_ruminal_pct'], \App\Livewire\IndicadoresDia::META_TC) ? 'bg-green-100 text-green-800 border-green-600' : 'bg-red-100 text-red-800 border-red-600' }}">
                                        {{ number_format($fila['contenido_ruminal_pct'], 2) }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold border-2 {{ \App\Livewire\IndicadoresDia::cumpleMeta($fila['materia_fecal_pct'], \App\Livewire\IndicadoresDia::META_TC) ? 'bg-green-100 text-green-800 border-green-600' : 'bg-red-100 text-red-800 border-red-600' }}">
                                        {{ number_format($fila['materia_fecal_pct'], 2) }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold border-2 {{ \App\Livewire\IndicadoresDia::cumpleMeta($fila['leche_visible_pct'], \App\Livewire\IndicadoresDia::META_TC) ? 'bg-green-100 text-green-800 border-green-600' : 'bg-red-100 text-red-800 border-red-600' }}">
                                        {{ number_format($fila['leche_visible_pct'], 2) }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold border-2 {{ \App\Livewire\IndicadoresDia::cumpleMeta($fila['participacion'], \App\Livewire\IndicadoresDia::META_TC) ? 'bg-green-100 text-green-800 border-green-600' : 'bg-red-100 text-red-800 border-red-600' }}">
                                        {{ number_format($fila['participacion'], 2) }}%
                                    </span>
                                </td>
                            </tr>
                            @if($filaTcActiva)
                            <tr id="tc-detalle-{{ $tcDiaId }}" class="bg-red-50/50" wire:click.stop>
                                <td colspan="8" class="p-0 border-t-2 border-red-300">
                                    <div class="p-3 sm:p-4 space-y-3">
                                        <div class="rounded-lg shadow p-3 sm:p-4" style="background: linear-gradient(to right, #dc2626, #b91c1c); color: #ffffff;">
                                            <h4 class="text-base sm:text-lg font-bold">🚨 Tolerancia Cero del {{ \Carbon\Carbon::parse($detalleTCDia['fecha_operacion'])->locale('es')->isoFormat('dddd, DD MMMM YYYY') }}</h4>
                                            <p class="text-xs mt-1" style="color: #fca5a5;">Desglose de esta fila</p>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
                                            <div class="bg-white/90 p-3 rounded-lg border-l-4 border-red-500 shadow-sm">
                                                <div class="font-bold text-xs text-gray-700 mb-1">⚠️ TOTAL HALLAZGOS TC</div>
                                                <div class="text-2xl font-extrabold text-red-600">{{ $detalleTCDia['total_hallazgos'] }}</div>
                                                <div class="text-xs text-gray-600">Cuarto Ant: {{ $detalleTCDia['cuarto_anterior'] }} | Cuarto Post: {{ $detalleTCDia['cuarto_posterior'] }}</div>
                                            </div>
                                            <div class="bg-white/90 p-3 rounded-lg border-l-4 border-orange-500 shadow-sm">
                                                <div class="font-bold text-xs text-gray-700 mb-1">🟠 CONTENIDO RUMINAL</div>
                                                <div class="text-2xl font-extrabold text-orange-600">{{ number_format($detalleTCDia['contenido_ruminal_pct'], 2) }}%</div>
                                                <div class="flex items-center gap-1 mt-1 flex-wrap text-xs text-gray-600">
                                                    <span>{{ $detalleTCDia['contenido_ruminal'] }} hallazgos</span>
                                                    <span class="px-1.5 py-0.5 rounded-full font-bold border-2 {{ \App\Livewire\IndicadoresDia::cumpleMeta($detalleTCDia['contenido_ruminal_pct'], \App\Livewire\IndicadoresDia::META_TC) ? 'bg-green-100 text-green-800 border-green-600' : 'bg-red-100 text-red-800 border-red-600' }}">{{ \App\Livewire\IndicadoresDia::cumpleMeta($detalleTCDia['contenido_ruminal_pct'], \App\Livewire\IndicadoresDia::META_TC) ? '✅' : '❌' }}</span>
                                                </div>
                                            </div>
                                            <div class="bg-white/90 p-3 rounded-lg border-l-4 border-yellow-500 shadow-sm">
                                                <div class="font-bold text-xs text-gray-700 mb-1">🟡 MATERIA FECAL</div>
                                                <div class="text-2xl font-extrabold text-yellow-600">{{ number_format($detalleTCDia['materia_fecal_pct'], 2) }}%</div>
                                                <div class="flex items-center gap-1 mt-1 flex-wrap text-xs text-gray-600">
                                                    <span>{{ $detalleTCDia['materia_fecal'] }} hallazgos</span>
                                                    <span class="px-1.5 py-0.5 rounded-full font-bold border-2 {{ \App\Livewire\IndicadoresDia::cumpleMeta($detalleTCDia['materia_fecal_pct'], \App\Livewire\IndicadoresDia::META_TC) ? 'bg-green-100 text-green-800 border-green-600' : 'bg-red-100 text-red-800 border-red-600' }}">{{ \App\Livewire\IndicadoresDia::cumpleMeta($detalleTCDia['materia_fecal_pct'], \App\Livewire\IndicadoresDia::META_TC) ? '✅' : '❌' }}</span>
                                                </div>
                                            </div>
                                            <div class="bg-white/90 p-3 rounded-lg border-l-4 border-blue-500 shadow-sm">
                                                <div class="font-bold text-xs text-gray-700 mb-1">🔵 LECHE VISIBLE</div>
                                                <div class="text-2xl font-extrabold text-blue-600">{{ number_format($detalleTCDia['leche_visible_pct'], 2) }}%</div>
                                                <div class="flex items-center gap-1 mt-1 flex-wrap text-xs text-gray-600">
                                                    <span>{{ $detalleTCDia['leche_visible'] }} hallazgos</span>
                                                    <span class="px-1.5 py-0.5 rounded-full font-bold border-2 {{ \App\Livewire\IndicadoresDia::cumpleMeta($detalleTCDia['leche_visible_pct'], \App\Livewire\IndicadoresDia::META_TC) ? 'bg-green-100 text-green-800 border-green-600' : 'bg-red-100 text-red-800 border-red-600' }}">{{ \App\Livewire\IndicadoresDia::cumpleMeta($detalleTCDia['leche_visible_pct'], \App\Livewire\IndicadoresDia::META_TC) ? '✅' : '❌' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bg-white rounded-lg border border-red-200 p-3">
                                            <h5 class="font-bold text-sm text-gray-900 mb-2">📊 Participación TC del Día</h5>
                                            <div class="flex justify-between mb-1 text-xs sm:text-sm">
                                                <span class="font-medium text-gray-700">Participación total</span>
                                                <span class="font-bold {{ \App\Livewire\IndicadoresDia::cumpleMeta($detalleTCDia['participacion'], \App\Livewire\IndicadoresDia::META_TC) ? 'text-green-600' : 'text-red-600' }}">{{ number_format($detalleTCDia['participacion'], 2) }}%</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-3">
                                                <div class="h-3 rounded-full {{ \App\Livewire\IndicadoresDia::cumpleMeta($detalleTCDia['participacion'], \App\Livewire\IndicadoresDia::META_TC) ? 'bg-green-500' : 'bg-red-500' }}" style="width: {{ min($detalleTCDia['participacion'] * 10, 100) }}%"></div>
                                            </div>
                                            <div class="flex justify-between mt-1 text-xs text-gray-500">
                                                <span>0%</span>
                                                <span>Meta: {{ \App\Livewire\IndicadoresDia::META_TC }}%</span>
                                                <span>10%</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endif
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
</div>
