<div class="max-w-full mx-auto py-6 px-4 sm:px-6 lg:px-8">
    {{-- Encabezado --}}
    <div class="mb-6">
        <h2 class="text-3xl font-bold text-gray-900">Consulta y filtra los registros de hallazgos</h2>
        <p class="mt-1 text-sm text-gray-600">Historial de Registros</p>
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
    <div class="bg-white shadow-md rounded-lg p-6 mb-6 border-l-4 border-red-600">
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
                <label class="block text-sm font-medium text-gray-700 mb-2">Producto</label>
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
                <input type="text" wire:model.defer="numero_canal" placeholder="Buscar código..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-red-500 focus:border-red-500">
            </div>
        </div>
        <div class="mt-4 flex justify-between items-center">
            <div>
                <button wire:click="buscar" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 font-medium">
                    🔍 Buscar
                </button>
                <button wire:click="limpiarFiltros" class="ml-2 px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Limpiar Filtros
                </button>
            </div>
        </div>
    </div>

    {{-- Cards de Estadísticas --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border-l-4 border-red-600 rounded-lg p-4 shadow-md">
            <p class="text-gray-600 text-sm font-medium">TOTAL REGISTROS</p>
            <p class="text-3xl font-bold text-red-600">{{ $totalRegistros }}</p>
        </div>
        
        @php
            $coloresPorTipo = [
                'COBERTURA DE GRASA' => ['bg' => 'bg-orange-50', 'text' => 'text-orange-600', 'border' => 'border-orange-600'],
                'SOBREBARRIGA ROTA' => ['bg' => 'bg-red-50', 'text' => 'text-red-600', 'border' => 'border-red-600'],
                'HEMATOMAS' => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-600', 'border' => 'border-yellow-600'],
                'CORTE EN PIERNA' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'border' => 'border-blue-600'],
            ];
        @endphp
        
        @forelse($estadisticasPorTipo as $tipo => $cantidad)
            @php
                $estilos = $coloresPorTipo[$tipo] ?? ['bg' => 'bg-gray-50', 'text' => 'text-gray-600', 'border' => 'border-gray-600'];
            @endphp
            <div class="bg-white border-l-4 {{ $estilos['border'] }} rounded-lg p-4 shadow-md">
                <p class="text-gray-600 text-sm font-medium">{{ strtoupper($tipo) }}</p>
                <p class="text-3xl font-bold {{ $estilos['text'] }}">{{ $cantidad }}</p>
            </div>
        @empty
        @endforelse
    </div>

    {{-- Tabla de Registros --}}
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 border-b-2 border-gray-300">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-red-600 uppercase tracking-wider">Fecha de Registro</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-red-600 uppercase tracking-wider">Código</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-red-600 uppercase tracking-wider">Producto</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-red-600 uppercase tracking-wider">Tipo Hallazgo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-red-600 uppercase tracking-wider">Ubicación Hallazgo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-red-600 uppercase tracking-wider">Detalle (Pierna)</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-red-600 uppercase tracking-wider">Usuario</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-red-600 uppercase tracking-wider">Operario</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-red-600 uppercase tracking-wider">Evidencia</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($registros as $registro)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="inline-flex items-center">📅 {{ $registro->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $registro->codigo ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $registro->producto->nombre ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm">
                               <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium {{ $registro->tipoHallazgo && $registro->tipoHallazgo->es_critico ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $registro->tipoHallazgo->nombre ?? 'N/A' }}
                               </span>
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
                                <span class="inline-flex items-center">👤 {{ $registro->usuario->name ?? 'N/A' }}</span>
                            </td>
                             <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $this->obtenerOperarioResponsable($registro) }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm">
                                @if($registro->evidencia_path)
                                    <button wire:click="mostrarEvidencia({{ $registro->id }})" class="hover:opacity-75 transition-opacity">
                                        <img src="{{ asset('storage/' . $registro->evidencia_path) }}" alt="Evidencia" class="h-12 w-12 object-cover rounded-md cursor-pointer border border-gray-200">
                                    </button>
                                @else
                                    <span class="text-gray-400">N/A</span>
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
