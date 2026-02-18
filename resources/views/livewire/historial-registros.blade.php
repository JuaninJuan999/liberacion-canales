<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
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
            {{-- Fecha Inicio --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Inicio</label>
                <input type="date" 
                       wire:model="fecha_inicio" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>

            {{-- Fecha Fin --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Fin</label>
                <input type="date" 
                       wire:model="fecha_fin" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>

            {{-- Producto --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Producto</label>
                <select wire:model="producto_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos</option>
                    @foreach($productos as $producto)
                        <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Tipo de Hallazgo --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo Hallazgo</label>
                <select wire:model="tipo_hallazgo_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos</option>
                    @foreach($tiposHallazgo as $tipo)
                        <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Puesto de Trabajo --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Puesto</label>
                <select wire:model="puesto_trabajo_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos</option>
                    @foreach($puestos as $puesto)
                        <option value="{{ $puesto->id }}">{{ $puesto->nombre }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Operario --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Operario</label>
                <select wire:model="operario_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos</option>
                    @foreach($operarios as $operario)
                        <option value="{{ $operario->id }}">{{ $operario->nombre_completo }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Número de Canal --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Número Canal</label>
                <input type="text" 
                       wire:model="numero_canal" 
                       placeholder="Buscar..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>

            {{-- Solo Críticos --}}
            <div class="flex items-end">
                <label class="flex items-center">
                    <input type="checkbox" 
                           wire:model="solo_criticos" 
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Solo hallazgos críticos</span>
                </label>
            </div>
        </div>

        {{-- Botones de Acción --}}
        <div class="mt-4 flex justify-between items-center">
            <button wire:click="limpiarFiltros" 
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Limpiar Filtros
            </button>
            <button wire:click="exportarExcel" 
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                📊 Exportar a Excel
            </button>
        </div>
    </div>

    {{-- Estadísticas --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-sm text-blue-600 font-medium">Total Registros</p>
            <p class="text-2xl font-bold text-blue-900">{{ $totalRegistros }}</p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <p class="text-sm text-red-600 font-medium">Hallazgos Críticos</p>
            <p class="text-2xl font-bold text-red-900">{{ $totalCriticos }}</p>
        </div>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <p class="text-sm text-yellow-600 font-medium">Hallazgos Leves</p>
            <p class="text-2xl font-bold text-yellow-900">{{ $totalLeves }}</p>
        </div>
    </div>

    {{-- Tabla de Registros --}}
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha/Hora</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Canal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo Hallazgo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Puesto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operario</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ubicación</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($registros as $registro)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $registro->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $registro->numero_canal }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $registro->producto->nombre ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $registro->tipoHallazgo->es_critico ?? false ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $registro->tipoHallazgo->nombre ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $registro->puestoTrabajo->nombre ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $registro->operario->nombre_completo ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $registro->ubicacion->nombre ?? 'N/A' }} - {{ $registro->lado->nombre ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button wire:click="eliminarRegistro({{ $registro->id }})" 
                                        wire:confirm="¿Está seguro de eliminar este registro?"
                                        class="text-red-600 hover:text-red-900">
                                    🗑️
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                No se encontraron registros
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
</div>