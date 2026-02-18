<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    {{-- Encabezado --}}
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Indicadores del Día</h2>
            <p class="mt-1 text-sm text-gray-600">Métricas detalladas de desempeño</p>
        </div>
        <input type="date" 
               wire:model="fecha" 
               wire:change="cambiarFecha($event.target.value)"
               class="px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
    </div>

    @if($indicadores)
        {{-- Indicadores Principales --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow-md p-6 border border-blue-200">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-blue-900">Canales Procesadas</p>
                        <p class="text-4xl font-bold text-blue-900 mt-2">{{ number_format($indicadores->total_animales) }}</p>
                    </div>
                    <div class="text-5xl opacity-20">🐄</div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow-md p-6 border border-green-200">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-green-900">Tasa de Liberación</p>
                        <p class="text-4xl font-bold text-green-900 mt-2">{{ number_format($indicadores->porcentaje_liberacion, 1) }}%</p>
                        @if(isset($comparacionDiaAnterior['liberacion']))
                            <p class="text-xs mt-1 {{ $comparacionDiaAnterior['liberacion']['tendencia'] === 'up' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $comparacionDiaAnterior['liberacion']['tendencia'] === 'up' ? '↑' : '↓' }}
                                {{ abs($comparacionDiaAnterior['liberacion']['diferencia']) }}% vs ayer
                            </p>
                        @endif
                    </div>
                    <div class="text-5xl opacity-20">✅</div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg shadow-md p-6 border border-red-200">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-red-900">Hallazgos Críticos</p>
                        <p class="text-4xl font-bold text-red-900 mt-2">{{ number_format($indicadores->hallazgos_criticos) }}</p>
                        @if(isset($comparacionDiaAnterior['criticos']))
                            <p class="text-xs mt-1 {{ $comparacionDiaAnterior['criticos']['tendencia'] === 'up' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $comparacionDiaAnterior['criticos']['tendencia'] === 'up' ? '↑' : '↓' }}
                                {{ abs($comparacionDiaAnterior['criticos']['diferencia']) }} vs ayer
                            </p>
                        @endif
                    </div>
                    <div class="text-5xl opacity-20">❌</div>
                </div>
            </div>
        </div>

        {{-- Indicadores por Operario --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Desempeño por Operario</h3>
            @if(count($indicadoresPorOperario) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Operario</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total Hallazgos</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Críticos</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Leves</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Efectividad</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($indicadoresPorOperario as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item['operario'] }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-gray-900">{{ $item['total'] }}</td>
                                    <td class="px-4 py-3 text-sm text-center">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                            {{ $item['criticos'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                            {{ $item['leves'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center">
                                        <div class="flex items-center justify-center">
                                            <div class="w-full max-w-xs bg-gray-200 rounded-full h-2 mr-2">
                                                <div class="bg-green-600 h-2 rounded-full" style="width: {{ $item['efectividad'] }}%"></div>
                                            </div>
                                            <span class="font-semibold text-gray-900">{{ number_format($item['efectividad'], 1) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No hay datos de operarios para este día</p>
            @endif
        </div>

        {{-- Comparación con Día Anterior --}}
        @if(!empty($comparacionDiaAnterior))
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Comparación con Día Anterior</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 border border-gray-200 rounded-lg">
                        <p class="text-sm text-gray-600">Liberación</p>
                        <p class="text-2xl font-bold mt-1">{{ number_format($comparacionDiaAnterior['liberacion']['actual'], 2) }}%</p>
                        <p class="text-xs text-gray-500 mt-1">Ayer: {{ number_format($comparacionDiaAnterior['liberacion']['anterior'], 2) }}%</p>
                    </div>
                    <div class="p-4 border border-gray-200 rounded-lg">
                        <p class="text-sm text-gray-600">Total Hallazgos</p>
                        <p class="text-2xl font-bold mt-1">{{ $comparacionDiaAnterior['hallazgos']['actual'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Ayer: {{ $comparacionDiaAnterior['hallazgos']['anterior'] }}</p>
                    </div>
                    <div class="p-4 border border-gray-200 rounded-lg">
                        <p class="text-sm text-gray-600">Críticos</p>
                        <p class="text-2xl font-bold text-red-600 mt-1">{{ $comparacionDiaAnterior['criticos']['actual'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Ayer: {{ $comparacionDiaAnterior['criticos']['anterior'] }}</p>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <p class="text-yellow-800 font-medium">No hay indicadores calculados para esta fecha</p>
        </div>
    @endif
</div>