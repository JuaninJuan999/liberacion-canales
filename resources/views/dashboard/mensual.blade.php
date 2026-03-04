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
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-7 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-sm text-gray-500 uppercase">Días Operados</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $totales['dias_operados'] }}</p>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-sm text-gray-500 uppercase">Total Animales</p>
                    <p class="text-3xl font-bold text-green-600">{{ number_format($totales['animales'], 0, ',', '.') }}</p>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-sm text-gray-500 uppercase">Total Hallazgos</p>
                    <p class="text-3xl font-bold text-red-600">{{ number_format($totales['hallazgos'], 0, ',', '.') }}</p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-sm text-gray-500 uppercase">Sobrebarriga Rotas</p>
                    <p class="text-3xl font-bold text-orange-600">{{ number_format($totales['sobrebarriga_rotas'], 0, ',', '.') }}</p>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-sm text-gray-500 uppercase">Hematomas</p>
                    <p class="text-3xl font-bold text-purple-600">{{ number_format($totales['hematomas'], 0, ',', '.') }}</p>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-sm text-gray-500 uppercase">Cobertura Grasa</p>
                    <p class="text-3xl font-bold text-yellow-600">{{ number_format($totales['cobertura'], 0, ',', '.') }}</p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-sm text-gray-500 uppercase">Cortes Piernas</p>
                    <p class="text-3xl font-bold text-pink-600">{{ number_format($totales['cortes_piernas'], 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Gráficos de Tendencia --}}
            @if($indicadores->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800">Indicador de sobrebarriga rotas</h3>
                    <div class="h-64">
                        <canvas id="chartSobrebarriga"></canvas>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800">Indicador de hematomas</h3>
                    <div class="h-64">
                        <canvas id="chartHematomas"></canvas>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800">Indicador de corte en piernas</h3>
                    <div class="h-64">
                        <canvas id="chartCortePiernas"></canvas>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800">Indicador de cobertura grasa</h3>
                    <div class="h-64">
                        <canvas id="chartCoberturaGrasa"></canvas>
                    </div>
                </div>
            </div>
            @else
                <div class="text-center py-12 bg-white rounded-lg shadow-sm">
                    <div class="text-gray-400 mb-4 text-4xl">📅</div>
                    <p class="text-gray-500">No hay indicadores registrados para este mes</p>
                </div>
            @endif

        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if(isset($chartData) && $indicadores->count() > 0)
            const chartData = @json($chartData);
            
            if (typeof window.initDashboardCharts === 'function') {
                window.initDashboardCharts(chartData);
            }
            @endif
        });
    </script>
    @endpush
</x-app-layout>
