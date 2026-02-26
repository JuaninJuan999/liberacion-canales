<div wire:poll.5s="cargarIndicadores">

    {{-- Selector de Fecha --}}
    <div class="mb-4 flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-800">Indicadores del Día</h2>
        <div>
            <input type="date" wire:model.live="fecha" class="border-gray-300 rounded-md shadow-sm">
        </div>
    </div>

    @if($indicadores)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            {{-- Animales Procesados --}}
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
                <div class="font-bold text-sm text-gray-600">ANIMALES PROCESADOS</div>
                <div class="text-2xl font-extrabold text-gray-900">{{ number_format($indicadores->animales_procesados ?? 0) }}</div>
                <div class="text-xs text-gray-500">Total del día</div>
            </div>

            {{-- Total Hallazgos --}}
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
                <div class="font-bold text-sm text-gray-600">TOTAL HALLAZGOS</div>
                <div class="text-2xl font-extrabold text-gray-900">{{ number_format($indicadores->total_hallazgos ?? 0) }}</div>
                <div class="text-xs text-gray-500">En {{ number_format($indicadores->medias_canales_total ?? 0) }} medias canales</div>
            </div>

            {{-- Participación --}}
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-purple-500">
                <div class="font-bold text-sm text-gray-600">PARTICIPACIÓN</div>
                <div class="text-2xl font-extrabold text-purple-600">{{ number_format($indicadores->participacion_total ?? 0, 2) }}%</div>
                <div class="text-xs text-gray-500">Promedio del día</div>
            </div>

            {{-- Hallazgos por Producto --}}
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-red-500">
                <div class="font-bold text-sm text-gray-600">HALLAZGOS POR PRODUCTO</div>
                <div class="text-lg font-bold text-gray-900">
                    <span>Media Canal 1: {{ number_format($indicadores->medias_canal_1 ?? 0) }}</span> |
                    <span>Media Canal 2: {{ number_format($indicadores->medias_canal_2 ?? 0) }}</span>
                </div>
                 <div class="text-xs text-gray-500">Según producto registrado</div>
            </div>
        </div>

        {{-- Hallazgos por Tipo --}}
        <div class="mt-6">
            <h3 class="text-lg font-bold text-gray-700 mb-2">Desglose de Hallazgos</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
                @forelse($hallazgosPorTipo as $hallazgo)
                    <div class="bg-gray-50 p-3 rounded-lg text-center shadow-sm">
                        <div class="font-semibold text-gray-600 text-sm">{{ $hallazgo['nombre'] }}</div>
                        <div class="text-xl font-bold text-gray-800">{{ $hallazgo['total'] }}</div>
                    </div>
                @empty
                    <p class="text-gray-500 col-span-full">No se han registrado hallazgos específicos hoy.</p>
                @endforelse
            </div>
        </div>

    @else
        <div class="text-center py-10 px-4 bg-yellow-50 rounded-lg border border-yellow-200">
            <p class="font-bold text-yellow-800">No hay datos de indicadores para esta fecha.</p>
            <p class="text-sm text-yellow-600">Los indicadores se generan automáticamente al registrar hallazgos.</p>
        </div>
    @endif

</div>
