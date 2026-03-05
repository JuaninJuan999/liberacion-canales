<div class="bg-gradient-to-br from-red-50 to-orange-50 rounded-lg shadow-lg p-6 border-2 border-red-300" wire:poll.3s="actualizar">
    {{-- Header --}}
    <div class="mb-6">
        <h3 class="text-xl font-bold text-red-700 flex items-center gap-2">
            <span class="text-2xl">🚨</span>
            Hallazgos Tolerancia Cero - Hoy
        </h3>
        <p class="text-sm text-gray-600 mt-1">{{ Carbon\Carbon::parse($fecha)->format('d \d\e F \d\e Y') }}</p>
    </div>

    {{-- Tarjetas de Conteos --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        {{-- Total General --}}
        <div class="bg-white rounded-lg p-4 border-l-4 border-red-600 shadow">
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Total Hallazgos</p>
            <p class="text-3xl font-bold text-red-600 mt-2">{{ $totalHallazgos }}</p>
            <p class="text-xs text-gray-500 mt-2">Registros del día</p>
        </div>

        {{-- Materia Fecal --}}
        <div class="bg-white rounded-lg p-4 border-l-4 border-yellow-600 shadow">
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Materia Fecal</p>
            <p class="text-3xl font-bold text-yellow-600 mt-2">{{ $materiaFecal }}</p>
            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-3">
                <div class="bg-yellow-600 h-1.5 rounded-full" style="width: {{ $totalHallazgos > 0 ? ($materiaFecal / $totalHallazgos) * 100 : 0 }}%"></div>
            </div>
        </div>

        {{-- Contenido Ruminal --}}
        <div class="bg-white rounded-lg p-4 border-l-4 border-orange-600 shadow">
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Contenido Ruminal</p>
            <p class="text-3xl font-bold text-orange-600 mt-2">{{ $contenidoRuminal }}</p>
            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-3">
                <div class="bg-orange-600 h-1.5 rounded-full" style="width: {{ $totalHallazgos > 0 ? ($contenidoRuminal / $totalHallazgos) * 100 : 0 }}%"></div>
            </div>
        </div>

        {{-- Leche Visible --}}
        <div class="bg-white rounded-lg p-4 border-l-4 border-blue-600 shadow">
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Leche Visible</p>
            <p class="text-3xl font-bold text-blue-600 mt-2">{{ $lecheVisible }}</p>
            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-3">
                <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $totalHallazgos > 0 ? ($lecheVisible / $totalHallazgos) * 100 : 0 }}%"></div>
            </div>
        </div>
    </div>

    {{-- Gráfico de Barras Horizontal --}}
    @if($totalHallazgos > 0)
    <div class="bg-white rounded-lg p-4 mt-6">
        <h4 class="font-semibold text-gray-800 mb-4">Distribución por Tipo</h4>
        
        <div class="space-y-3">
            {{-- Materia Fecal --}}
            <div>
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium text-gray-700">Materia Fecal</span>
                    <span class="text-sm font-bold text-yellow-600">{{ $materiaFecal }} ({{ number_format(($materiaFecal / $totalHallazgos) * 100, 1) }}%)</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-yellow-600 h-2 rounded-full" style="width: {{ ($materiaFecal / $totalHallazgos) * 100 }}%"></div>
                </div>
            </div>

            {{-- Contenido Ruminal --}}
            <div>
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium text-gray-700">Contenido Ruminal</span>
                    <span class="text-sm font-bold text-orange-600">{{ $contenidoRuminal }} ({{ number_format(($contenidoRuminal / $totalHallazgos) * 100, 1) }}%)</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-orange-600 h-2 rounded-full" style="width: {{ ($contenidoRuminal / $totalHallazgos) * 100 }}%"></div>
                </div>
            </div>

            {{-- Leche Visible --}}
            <div>
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium text-gray-700">Leche Visible</span>
                    <span class="text-sm font-bold text-blue-600">{{ $lecheVisible }} ({{ number_format(($lecheVisible / $totalHallazgos) * 100, 1) }}%)</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($lecheVisible / $totalHallazgos) * 100 }}%"></div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="bg-gray-50 rounded-lg p-8 text-center">
        <p class="text-gray-500 text-lg">ℹ️ Sin registros de tolerancia cero por hoy</p>
    </div>
    @endif
</div>
