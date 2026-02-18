<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard Mensual - Indicadores
            </h2>
            <a href="{{ route('dashboard') }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition text-sm">
                ← Volver al Dashboard Diario
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Selector de Mes/Año --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <form method="GET" action="{{ route('dashboard.mensual') }}" class="flex items-center gap-4">
                    <label class="text-sm font-medium text-gray-700">Mes:</label>
                    <select name="mes" 
                            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            onchange="this.form.submit()">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == $mes ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->locale('es')->isoFormat('MMMM') }}
                            </option>
                        @endfor
                    </select>
                    
                    <label class="text-sm font-medium text-gray-700">Año:</label>
                    <select name="anio" 
                            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            onchange="this.form.submit()">
                        @for($a = now()->year - 2; $a <= now()->year + 1; $a++)
                            <option value="{{ $a }}" {{ $a == $anio ? 'selected' : '' }}>{{ $a }}</option>
                        @endfor
                    </select>
                </form>
            </div>

            {{-- Tarjetas Resumen del Mes --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500 uppercase">Días Operados</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $totales['dias_operados'] }}</p>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500 uppercase">Total Animales</p>
                    <p class="text-3xl font-bold text-green-600">{{ number_format($totales['animales'], 0, ',', '.') }}</p>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500 uppercase">Total Hallazgos</p>
                    <p class="text-3xl font-bold text-red-600">{{ number_format($totales['hallazgos'], 0, ',', '.') }}</p>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500 uppercase">Hematomas</p>
                    <p class="text-3xl font-bold text-purple-600">{{ number_format($totales['hematomas'], 0, ',', '.') }}</p>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500 uppercase">Cobertura</p>
                    <p class="text-3xl font-bold text-yellow-600">{{ number_format($totales['cobertura'], 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Tabla de Indicadores Diarios --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Indicadores por Día</h3>
                        <div class="flex gap-2">
                            <a href="{{ route('reportes.mensual.pdf', ['mes' => $mes, 'anio' => $anio]) }}" 
                               class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition text-sm"
                               target="_blank">
                                📝 PDF
                            </a>
                            <a href="{{ route('reportes.mensual.excel', ['mes' => $mes, 'anio' => $anio]) }}" 
                               class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition text-sm">
                                📄 Excel
                            </a>
                        </div>
                    </div>

                    @if($indicadores->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Animales</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Hallazgos</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Participación %</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Hematomas</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Cobertura</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Cortes</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($indicadores as $ind)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 text-sm font-medium">
                                                {{ \Carbon\Carbon::parse($ind->fecha_operacion)->format('d/m/Y') }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-right">{{ number_format($ind->animales_procesados, 0, ',', '.') }}</td>
                                            <td class="px-4 py-2 text-sm text-right">{{ number_format($ind->total_hallazgos, 0, ',', '.') }}</td>
                                            <td class="px-4 py-2 text-sm text-right font-semibold
                                                {{ $ind->participacion_total > 5 ? 'text-red-600' : 'text-green-600' }}">
                                                {{ number_format($ind->participacion_total, 2) }}%
                                            </td>
                                            <td class="px-4 py-2 text-sm text-right">{{ $ind->hematomas }}</td>
                                            <td class="px-4 py-2 text-sm text-right">{{ $ind->cobertura_grasa }}</td>
                                            <td class="px-4 py-2 text-sm text-right">{{ $ind->cortes_piernas }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr class="font-bold">
                                        <td class="px-4 py-3 text-sm">TOTALES</td>
                                        <td class="px-4 py-3 text-sm text-right">{{ number_format($totales['animales'], 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm text-right">{{ number_format($totales['hallazgos'], 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm text-right">
                                            {{ number_format($indicadores->avg('participacion_total'), 2) }}%
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right">{{ number_format($totales['hematomas'], 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm text-right">{{ number_format($totales['cobertura'], 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm text-right">-</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="text-gray-400 mb-4 text-4xl">📅</div>
                            <p class="text-gray-500">No hay indicadores registrados para este mes</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Gráfica de Tendencia (Placeholder) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Tendencia de Participación</h3>
                <div class="text-center py-8 text-gray-400">
                    <p>📈 Gráfica en desarrollo - Próximamente con Chart.js</p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
