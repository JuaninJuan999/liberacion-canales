<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    {{-- Encabezado con navegación de mes --}}
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Dashboard Mensual</h2>
            <p class="mt-1 text-sm text-gray-600">Resumen de indicadores del mes</p>
        </div>
        <div class="flex items-center space-x-3">
            <button wire:click="cambiarMes('anterior')" 
                    class="px-3 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                ◀ Anterior
            </button>
            <span class="px-4 py-2 bg-blue-50 border border-blue-200 rounded-md font-semibold text-blue-900">
                {{ \Carbon\Carbon::create($anio, $mes, 1)->format('F Y') }}
            </span>
            <button wire:click="cambiarMes('siguiente')" 
                    class="px-3 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                Siguiente ▶
            </button>
            <button wire:click="irAHoy" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Hoy
            </button>
        </div>
    </div>

    @if($indicadoresMes)
        {{-- Tarjetas Resumen Mensual --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            {{-- Total Animales del Mes --}}
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <p class="text-sm font-medium text-gray-600">Total Animales</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($indicadoresMes['total_animales']) }}</p>
                <p class="text-xs text-gray-500 mt-1">En {{ $indicadoresMes['dias_procesados'] }} días</p>
            </div>

            {{-- Promedio Liberación --}}
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <p class="text-sm font-medium text-gray-600">Promedio Liberación</p>
                <p class="text-3xl font-bold text-green-600 mt-2">{{ number_format($indicadoresMes['promedio_liberacion'], 2) }}%</p>
                <p class="text-xs text-gray-500 mt-1">{{ number_format($indicadoresMes['canales_liberadas']) }} canales</p>
            </div>

            {{-- Total Hallazgos --}}
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
                <p class="text-sm font-medium text-gray-600">Total Hallazgos</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($indicadoresMes['total_hallazgos']) }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ number_format($indicadoresMes['hallazgos_leves']) }} leves</p>
            </div>

            {{-- Hallazgos Críticos --}}
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
                <p class="text-sm font-medium text-gray-600">Hallazgos Críticos</p>
                <p class="text-3xl font-bold text-red-600 mt-2">{{ number_format($indicadoresMes['hallazgos_criticos']) }}</p>
                <p class="text-xs text-gray-500 mt-1">
                    {{ $indicadoresMes['total_hallazgos'] > 0 ? number_format(($indicadoresMes['hallazgos_criticos'] / $indicadoresMes['total_hallazgos']) * 100, 2) : 0 }}% del total
                </p>
            </div>
        </div>

        {{-- Gráfico de Tendencia --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Tendencia del Mes</h3>
            <div class="h-64">
                <canvas id="chartMensual" wire:ignore></canvas>
            </div>
        </div>

        {{-- Tabla de Indicadores Diarios --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Detalle por Día</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Animales</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">% Liberación</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Hallazgos</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Críticos</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($indicadoresDiarios as $dia)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    {{ \Carbon\Carbon::parse($dia->fecha)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($dia->total_animales) }}</td>
                                <td class="px-4 py-3 text-sm text-right">
                                    <span class="font-semibold {{ $dia->porcentaje_liberacion >= 90 ? 'text-green-600' : ($dia->porcentaje_liberacion >= 80 ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ number_format($dia->porcentaje_liberacion, 2) }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($dia->total_hallazgos) }}</td>
                                <td class="px-4 py-3 text-sm text-right">
                                    <span class="font-semibold text-red-600">{{ number_format($dia->hallazgos_criticos) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('livewire:navigated', function() {
                const ctx = document.getElementById('chartMensual');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'line',
                        data: @json($graficoDatos),
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }
            });
        </script>
        @endpush
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <p class="text-yellow-800 font-medium">No hay datos disponibles para este mes</p>
        </div>
    @endif
</div>