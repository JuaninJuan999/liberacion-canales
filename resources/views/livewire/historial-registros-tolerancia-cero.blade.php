<div class="max-w-full mx-auto py-6 px-4 sm:px-6 lg:px-8">
    {{-- Encabezado --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-red-700 flex items-center gap-2">
            <span>🚨</span>
            Historial Hallazgos - Tolerancia Cero
        </h2>
        <p class="mt-1 text-sm text-gray-600">Consulta y filtra los registros de hallazgos críticos</p>
    </div>

    {{-- Panel de Filtros --}}
    <div class="bg-white shadow-md rounded-lg p-6 mb-6 border-l-4 border-red-500">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Filtros de Búsqueda</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Inicio</label>
                <input type="date" wire:model.defer="fecha_inicio" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-red-500 focus:border-red-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Fin</label>
                <input type="date" wire:model.defer="fecha_fin" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-red-500 focus:border-red-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cuarto</label>
                <select wire:model.defer="producto_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-red-500 focus:border-red-500">
                    <option value="">Todos</option>
                    @foreach($productos as $producto)
                        <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo Hallazgo</label>
                <select wire:model.defer="tipo_hallazgo_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-red-500 focus:border-red-500">
                    <option value="">Todos</option>
                    @foreach($tiposHallazgo as $tipo)
                        <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Código</label>
                <input type="text" wire:model.defer="codigo" placeholder="Buscar código..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-red-500 focus:border-red-500">
            </div>
        </div>
        <div class="mt-4 flex justify-between items-center">
            <div>
                <button wire:click="buscar" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 font-semibold">
                    🔍 Buscar
                </button>
                <button wire:click="limpiarFiltros" class="ml-2 px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Limpiar Filtros
                </button>
            </div>
        </div>
    </div>

    {{-- Estadísticas Rápidas --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-red-50 rounded-lg p-4 border-l-4 border-red-600 shadow">
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Total Registros</p>
            <p class="text-3xl font-bold text-red-600 mt-2">{{ $totalRegistros }}</p>
        </div>
        <div class="bg-yellow-50 rounded-lg p-4 border-l-4 border-yellow-600 shadow">
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Materia Fecal</p>
            <p class="text-3xl font-bold text-yellow-600 mt-2">{{ $materiaFecalTotal }}</p>
        </div>
        <div class="bg-orange-50 rounded-lg p-4 border-l-4 border-orange-600 shadow">
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Contenido Ruminal</p>
            <p class="text-3xl font-bold text-orange-600 mt-2">{{ $contenidoRuminalTotal }}</p>
        </div>
        <div class="bg-blue-50 rounded-lg p-4 border-l-4 border-blue-600 shadow">
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Leche Visible</p>
            <p class="text-3xl font-bold text-blue-600 mt-2">{{ $lecheVisibleTotal }}</p>
        </div>
    </div>

    {{-- Tabla de Registros --}}
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-red-50 border-b-2 border-red-300">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-red-900 uppercase tracking-wider">Fecha de Registro</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-red-900 uppercase tracking-wider">Código</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-red-900 uppercase tracking-wider">Cuarto</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-red-900 uppercase tracking-wider">Media</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-red-900 uppercase tracking-wider">Par/impar</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-red-900 uppercase tracking-wider">Tipo Hallazgo</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-red-900 uppercase tracking-wider">Ubicación Hallazgo</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-red-900 uppercase tracking-wider">Operario</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-red-900 uppercase tracking-wider">Usuario</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($registros as $registro)
                        <tr class="hover:bg-red-50 transition duration-150">
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                📅 {{ $registro->fecha_registro->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-900 max-w-[10rem] truncate" title="{{ $registro->codigo }}">
                                {{ $registro->codigo ?? '—' }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold">
                                    🥩 {{ $registro->producto->nombre ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($registro->media_canal)
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-800 text-xs font-semibold">Canal {{ $registro->media_canal }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 capitalize">
                                {{ $registro->par_impar ? strtoupper($registro->par_impar) : '—' }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm">
                                @php
                                    $tipoColor = match($registro->tipoHallazgo->nombre ?? '') {
                                        'MATERIA FECAL' => 'bg-yellow-100 text-yellow-700',
                                        'CONTENIDO RUMINAL' => 'bg-orange-100 text-orange-700',
                                        'LECHE VISIBLE' => 'bg-blue-100 text-blue-700',
                                        default => 'bg-gray-100 text-gray-700'
                                    };
                                @endphp
                                <span class="px-2 py-1 {{ $tipoColor }} rounded-full text-xs font-semibold">
                                    {{ $registro->tipoHallazgo->nombre ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-700">
                                <span class="inline-block max-w-xs truncate" title="{{ $registro->ubicacion->nombre ?? 'N/A' }}">
                                    {{ $registro->ubicacion->nombre ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $this->obtenerOperarioResponsable($registro) }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                👤 {{ $registro->usuario->name ?? 'N/A' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <p class="text-3xl mb-2">📭</p>
                                    <p class="text-sm text-gray-500 font-medium">
                                        No se encontraron registros que coincidan con los filtros aplicados.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $registros->links() }}
        </div>
    </div>
</div>
