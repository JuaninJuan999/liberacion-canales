<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    {{-- Encabezado con selector de fecha --}}
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Dashboard del Día</h2>
            <p class="mt-1 text-sm text-gray-600">Indicadores y métricas de calidad</p>
        </div>
        <div class="flex items-center space-x-3">
            <input type="date" 
                   wire:model="fecha" 
                   wire:change="cambiarFecha($event.target.value)"
                   class="px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            <button wire:click="recalcularIndicadores" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                🔄 Recalcular
            </button>
        </div>
    </div>

    @if($indicadores)
        {{-- Tarjetas de Indicadores Principales --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            {{-- Total Animales --}}
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Animales Procesados</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($indicadores->total_animales) }}</p>
                    </div>
                    <div class="text-4xl">🐄</div>
                </div>
            </div>

            {{-- Porcentaje de Liberación --}}
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">% Liberación</p>
                        <p class="text-3xl font-bold text-green-600 mt-2">{{ number_format($indicadores->porcentaje_liberacion, 2) }}%</p>
                        <p class="text-xs text-gray-500 mt-1">{{ number_format($indicadores->canales_liberadas) }} canales</p>
                    </div>
                    <div class="text-4xl">✅</div>
                </div>
            </div>

            {{-- Total Hallazgos --}}
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Hallazgos</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($indicadores->total_hallazgos) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ number_format($indicadores->porcentaje_hallazgos, 2) }}% del total</p>
                    </div>
                    <div class="text-4xl">⚠️</div>
                </div>
            </div>

            {{-- Hallazgos Críticos --}}
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Hallazgos Críticos</p>
                        <p class="text-3xl font-bold text-red-600 mt-2">{{ number_format($indicadores->hallazgos_criticos) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ number_format($indicadores->hallazgos_leves) }} leves</p>
                    </div>
                    <div class="text-4xl">❌</div>
                </div>
            </div>
        </div>

        {{-- Sección de Gráficos --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Hallazgos por Puesto --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Hallazgos por Puesto de Trabajo</h3>
                @if(count($hallazgosPorPuesto) > 0)
                    <div class="space-y-3">
                        @foreach($hallazgosPorPuesto as $item)
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-medium text-gray-700">{{ $item['puesto'] }}</span>
                                    <span class="text-sm font-bold text-gray-900">{{ $item['total'] }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" 
                                         style="width: {{ ($item['total'] / $indicadores->total_hallazgos) * 100 }}%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Críticos: {{ $item['criticos'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">No hay datos disponibles</p>
                @endif
            </div>

            {{-- Hallazgos por Tipo --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Hallazgos por Tipo</h3>
                @if(count($hallazgosPorTipo) > 0)
                    <div class="space-y-3">
                        @foreach($hallazgosPorTipo as $item)
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-medium text-gray-700">{{ $item['tipo'] }}</span>
                                    <span class="text-sm font-bold {{ $item['es_critico'] ? 'text-red-600' : 'text-gray-900' }}">{{ $item['total'] }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="{{ $item['es_critico'] ? 'bg-red-500' : 'bg-yellow-500' }} h-2 rounded-full" 
                                         style="width: {{ ($item['total'] / $indicadores->total_hallazgos) * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">No hay datos disponibles</p>
                @endif
            </div>
        </div>

        {{-- Últimos Hallazgos Registrados --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Últimos Hallazgos Registrados</h3>
            @if($ultimosHallazgos->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hora</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Canal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Puesto</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Operario</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($ultimosHallazgos as $hallazgo)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $hallazgo->created_at->format('H:i') }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $hallazgo->numero_canal }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $hallazgo->tipoHallazgo->es_critico ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $hallazgo->tipoHallazgo->nombre }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $hallazgo->puestoTrabajo->nombre }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $hallazgo->operario->nombre_completo }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No hay hallazgos registrados hoy</p>
            @endif
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <p class="text-yellow-800 font-medium">No hay datos disponibles para esta fecha</p>
            <p class="text-yellow-600 text-sm mt-2">Registra hallazgos para ver los indicadores</p>
        </div>
    @endif
</div>