<div class="max-w-full mx-auto py-6 px-4 sm:px-6 lg:px-8">
    {{-- Encabezado --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Historial de Registros</h2>
        <p class="mt-1 text-sm text-gray-600">Consulta y filtra los registros de hallazgos</p>
    </div>

    {{-- Mensajes --}}
    @if (session()->has('message'))
        <div class="mb-4 p-4 rounded-lg bg-green-50 text-green-800 border border-green-200">
            <p class="font-medium">{{ session('message') }}</p>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 rounded-lg bg-red-50 text-red-800 border border-red-200">
            <p class="font-medium">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Panel de Filtros --}}
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Filtros de Búsqueda</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Inicio</label>
                <input type="date" wire:model.defer="fecha_inicio" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Fin</label>
                <input type="date" wire:model.defer="fecha_fin" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Producto</label>
                <select wire:model.defer="producto_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos</option>
                    @foreach($productos as $producto)
                        <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo Hallazgo</label>
                <select wire:model.defer="tipo_hallazgo_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos</option>
                    @foreach($tiposHallazgo as $tipo)
                        <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Código</label>
                <input type="text" wire:model.defer="numero_canal" placeholder="Buscar..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
        <div class="mt-4 flex justify-between items-center">
            <div>
                <button wire:click="buscar" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Buscar
                </button>
                <button wire:click="limpiarFiltros" class="ml-2 px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Limpiar Filtros
                </button>
            </div>
            <button wire:click="exportarExcel" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                📊 Exportar a Excel
            </button>
        </div>
    </div>

    {{-- Tabla de Registros --}}
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de registro</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Codigo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hallazgo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Observacion</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detalle (pierna)</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operario</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evidencia</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($registros as $registro)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $registro->created_at->format('n/j/Y g:i A') }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $registro->codigo }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $registro->producto->nombre ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                               {{ $registro->tipoHallazgo->nombre ?? 'N/A' }}
                            </td>
                             <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($registro->tipoHallazgo && strtolower($registro->tipoHallazgo->nombre) == 'cobertura de grasa')
                                    {{ $registro->ubicacion->nombre ?? 'N/A' }}
                                @else
                                    {{ $registro->observacion ?? 'N/A' }}
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if(!(strtolower($registro->tipoHallazgo->nombre ?? '') == 'cobertura de grasa' && strtolower($registro->ubicacion->nombre ?? '') == 'cadera'))
                                    {{ $registro->lado->nombre ?? '' }}
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $registro->usuario->name ?? 'N/A' }}
                            </td>
                             <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $this->obtenerOperarioResponsable($registro) }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($registro->evidencia_path)
                                    <button wire:click="mostrarEvidencia({{ $registro->id }})">
                                        <img src="{{ asset('storage/' . $registro->evidencia_path) }}" alt="Evidencia" class="h-12 w-12 object-cover rounded-md cursor-pointer">
                                    </button>
                                @else
                                    <span>N/A</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">
                                No se encontraron registros que coincidan con los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $registros->links() }}
        </div>
    </div>

    {{-- Modal para mostrar evidencia --}}
    @if($mostrarModalEvidencia)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-xl p-4 max-w-2xl max-h-full overflow-auto">
                <div class="flex justify-end">
                    <button wire:click="cerrarModalEvidencia" class="text-gray-500 hover:text-gray-800">&times;</button>
                </div>
                <div class="mt-4">
                    <img src="{{ $evidenciaMostradaUrl }}" alt="Evidencia en grande" class="max-w-full h-auto">
                </div>
            </div>
        </div>
    @endif
</div>
