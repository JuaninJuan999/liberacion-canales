<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard - Indicadores de Calidad
            </h2>
            <a href="{{ route('dashboard.mensual') }}" 
               class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition text-sm">
                Ver Dashboard Mensual
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Selector de Fecha --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-4">
                    <label class="text-sm font-medium text-gray-700">Fecha:</label>
                    <input type="date" 
                           name="fecha" 
                           value="{{ $fecha }}"
                           class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           onchange="this.form.submit()">
                    <span class="text-sm text-gray-600">
                        {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }} - 
                        {{ \Carbon\Carbon::parse($fecha)->locale('es')->isoFormat('dddd') }}
                    </span>
                </form>
            </div>

            {{-- Tarjetas de Indicadores Principales --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Animales Procesados --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Animales Procesados</p>
                                <p class="text-3xl font-bold text-blue-600">{{ number_format($animalesProcesados, 0, ',', '.') }}</p>
                            </div>
                            <div class="text-4xl text-blue-400">🐄</div>
                        </div>
                    </div>
                </div>

                {{-- Total Hallazgos --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Total Hallazgos</p>
                                <p class="text-3xl font-bold text-red-600">{{ $indicador?->total_hallazgos ?? 0 }}</p>
                            </div>
                            <div class="text-4xl text-red-400">⚠️</div>
                        </div>
                    </div>
                </div>

                {{-- Participación --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Participación</p>
                                <p class="text-3xl font-bold text-orange-600">{{ number_format($indicador?->participacion_total ?? 0, 2) }}%</p>
                            </div>
                            <div class="text-4xl text-orange-400">📊</div>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">Promedio mes: {{ number_format($promediosMes['participacion'] ?? 0, 2) }}%</p>
                    </div>
                </div>

                {{-- Medias Canales --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 uppercase">Medias Canales</p>
                                <p class="text-3xl font-bold text-green-600">{{ $indicador?->medias_canales_total ?? 0 }}</p>
                            </div>
                            <div class="text-4xl text-green-400">🥩</div>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">
                            MC1: {{ $indicador?->medias_canal_1 ?? 0 }} | MC2: {{ $indicador?->medias_canal_2 ?? 0 }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Desglose de Hallazgos --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">Cobertura de Grasa</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $indicador?->cobertura_grasa ?? 0 }}</p>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">Hematomas</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $indicador?->hematomas ?? 0 }}</p>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">Cortes en Piernas</p>
                    <p class="text-2xl font-bold text-red-600">{{ $indicador?->cortes_piernas ?? 0 }}</p>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">Sobrebarriga Rota</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $indicador?->sobrebarriga_rota ?? 0 }}</p>
                </div>
            </div>

            {{-- Top 5 Hallazgos --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Top 5 Tipos de Hallazgos</h3>
                    @if($topHallazgos->count() > 0)
                        <div class="space-y-2">
                            @foreach($topHallazgos as $item)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                                    <span class="text-sm font-medium">{{ $item->tipoHallazgo->nombre }}</span>
                                    <span class="text-sm font-bold text-blue-600">{{ $item->total }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">No hay hallazgos registrados para esta fecha</p>
                    @endif
                </div>
            </div>

            {{-- Últimos Hallazgos --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Últimos Hallazgos Registrados</h3>
                    @if($hallazgosDia->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Hora</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Hallazgo</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ubicación</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($hallazgosDia->take(10) as $hallazgo)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 text-sm">{{ $hallazgo->created_at->format('H:i') }}</td>
                                            <td class="px-4 py-2 text-sm font-medium">{{ $hallazgo->codigo }}</td>
                                            <td class="px-4 py-2 text-sm">{{ $hallazgo->producto->nombre }}</td>
                                            <td class="px-4 py-2 text-sm">{{ $hallazgo->tipoHallazgo->nombre }}</td>
                                            <td class="px-4 py-2 text-sm">{{ $hallazgo->ubicacion?->nombre ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">No hay hallazgos registrados para esta fecha</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
