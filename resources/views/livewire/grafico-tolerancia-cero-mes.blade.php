<div class="bg-gradient-to-br from-red-50 to-orange-50 rounded-lg shadow-lg p-6 border-2 border-red-300" wire:poll.5s="actualizar">
    {{-- Header --}}
    <div class="mb-6">
        <h3 class="text-xl font-bold text-red-700 flex items-center gap-2">
            <span class="text-2xl">📊</span>
            Tolerancia Cero - Mes
        </h3>
        <p class="text-sm text-gray-600 mt-1">{{ Carbon\Carbon::create($anio, $mes, 1)->locale('es')->isoFormat('MMMM Y') }}</p>
    </div>

    {{-- Tarjetas Principales --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        {{-- Total Hallazgos --}}
        <div class="bg-white rounded-lg p-4 border-l-4 border-red-600 shadow">
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Total Hallazgos</p>
            <p class="text-3xl font-bold text-red-600 mt-2">{{ $totalHallazgosMes }}</p>
            <p class="text-xs text-gray-500 mt-2">En {{ $diasConDatos }} días laborales</p>
        </div>

        {{-- Promedio Diario vs Meta --}}
        <div class="bg-white rounded-lg p-4 border-l-4 {{ $cumpleMeta ? 'border-green-600' : 'border-yellow-600' }} shadow">
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Promedio Diario</p>
            <p class="text-3xl font-bold {{ $cumpleMeta ? 'text-green-600' : 'text-yellow-600' }} mt-2">
                {{ number_format($promedioDiario, 2) }}
            </p>
            <p class="text-xs {{ $cumpleMeta ? 'text-green-600' : 'text-yellow-600' }} font-semibold mt-2">
                {{ $cumpleMeta ? '✅ Cumple meta' : '⚠️ Fuera de meta' }} (Meta: {{ $metaMensual }})
            </p>
        </div>

        {{-- Indicador de Meta --}}
        <div class="bg-white rounded-lg p-4 border-l-4 {{ $cumpleMeta ? 'border-green-500' : 'border-red-500' }} shadow">
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Estado Meta</p>
            <div class="mt-3">
                <div class="text-center">
                    <p class="text-4xl font-bold {{ $cumpleMeta ? 'text-green-600' : 'text-red-600' }}">
                        {{ $cumpleMeta ? '✅' : '⚠️' }}
                    </p>
                    <p class="text-xs text-gray-600 mt-2">
                        {{ $cumpleMeta ? 'Bajo control' : 'Requiere atención' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Desglose por Tipo --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Materia Fecal --}}
        <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
            <h4 class="font-semibold text-yellow-800 mb-3">🟨 Materia Fecal</h4>
            <p class="text-3xl font-bold text-yellow-600">{{ $materiaFecalTotal }}</p>
            @if($totalHallazgosMes > 0)
                <p class="text-xs text-gray-600 mt-2">{{ number_format(($materiaFecalTotal / $totalHallazgosMes) * 100, 1) }}% del total</p>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                    <div class="bg-yellow-600 h-2 rounded-full" style="width: {{ ($materiaFecalTotal / $totalHallazgosMes) * 100 }}%"></div>
                </div>
            @endif
        </div>

        {{-- Contenido Ruminal --}}
        <div class="bg-orange-50 rounded-lg p-4 border border-orange-200">
            <h4 class="font-semibold text-orange-800 mb-3">🟠 Contenido Ruminal</h4>
            <p class="text-3xl font-bold text-orange-600">{{ $contenidoRuminalTotal }}</p>
            @if($totalHallazgosMes > 0)
                <p class="text-xs text-gray-600 mt-2">{{ number_format(($contenidoRuminalTotal / $totalHallazgosMes) * 100, 1) }}% del total</p>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                    <div class="bg-orange-600 h-2 rounded-full" style="width: {{ ($contenidoRuminalTotal / $totalHallazgosMes) * 100 }}%"></div>
                </div>
            @endif
        </div>

        {{-- Leche Visible --}}
        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
            <h4 class="font-semibold text-blue-800 mb-3">🔵 Leche Visible</h4>
            <p class="text-3xl font-bold text-blue-600">{{ $lecheVisibleTotal }}</p>
            @if($totalHallazgosMes > 0)
                <p class="text-xs text-gray-600 mt-2">{{ number_format(($lecheVisibleTotal / $totalHallazgosMes) * 100, 1) }}% del total</p>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($lecheVisibleTotal / $totalHallazgosMes) * 100 }}%"></div>
                </div>
            @endif
        </div>
    </div>

    {{-- Nota informativa --}}
    <div class="mt-6 p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-700">
        <p>💡 <strong>Meta Mensual:</strong> Mantener un promedio de {{ $metaMensual }} hallazgo por día o menos para garantizar el cumplimiento de tolerancia cero.</p>
    </div>
</div>
