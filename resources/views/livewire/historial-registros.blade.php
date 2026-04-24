<div class="max-w-full mx-auto py-6 px-4 sm:px-6 lg:px-8">
    {{-- Encabezado --}}
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Consulta y filtra los registros de hallazgos</h2>
            <p class="mt-1 text-sm text-gray-600">Historial de Registros</p>
        </div>
        @if($this->usuarioPuedeEliminarHallazgos())
            <button type="button"
                    wire:click="abrirEliminados"
                    class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md text-gray-800 bg-white hover:bg-gray-50 text-sm font-medium shadow-sm">
                📂 Registros eliminados (archivo)
            </button>
        @endif
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
                        @if($this->usuarioPuedeEditarOHistorialHallazgos())
                            <th class="px-4 py-3 text-left text-xs font-medium text-red-600 uppercase tracking-wider">Acciones</th>
                        @endif
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
                            @if($this->usuarioPuedeEditarOHistorialHallazgos())
                                <td class="px-4 py-4 whitespace-nowrap text-sm">
                                    <div class="flex items-center gap-2">
                                        <button type="button"
                                                wire:click="abrirEditar({{ $registro->id }})"
                                                title="Editar"
                                                class="text-xl leading-none p-1 rounded hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500">✏️</button>
                                        @if(is_array($registro->ediciones_historial) && count($registro->ediciones_historial) > 0)
                                            <button type="button"
                                                    wire:click="abrirHistorialEdiciones({{ $registro->id }})"
                                                    title="Ver historial de cambios (antes / después)"
                                                    class="text-xl leading-none p-1 rounded hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500">📜</button>
                                        @endif
                                        @if($this->usuarioPuedeEliminarHallazgos())
                                            <button type="button"
                                                    wire:click="eliminarRegistro({{ $registro->id }})"
                                                    wire:confirm="¿Eliminar este registro del historial? Se guardará una copia (incluida la evidencia) en el archivo de eliminados con tu usuario como responsable."
                                                    title="Eliminar"
                                                    class="text-xl leading-none p-1 rounded hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500">🗑️</button>
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $this->usuarioPuedeEditarOHistorialHallazgos() ? 10 : 9 }}" class="px-6 py-4 text-center text-sm text-gray-500">
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
    @if($mostrarModalEditar)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" wire:click="cerrarEditar">
            <div class="bg-white rounded-lg shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto" @click="$event.stopPropagation()">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Editar registro de hallazgo</h3>
                    <button type="button" wire:click="cerrarEditar" class="text-gray-500 hover:text-gray-800 text-2xl leading-none">&times;</button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de operación</label>
                        <input type="date" wire:model="edit_fecha_operacion" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-red-500 focus:border-red-500">
                        @error('edit_fecha_operacion') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Código (canal)</label>
                        <input type="text" wire:model="edit_codigo" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-red-500 focus:border-red-500">
                        @error('edit_codigo') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Producto</label>
                        <select wire:model="edit_producto_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-red-500 focus:border-red-500">
                            <option value="">Seleccione…</option>
                            @foreach($productos as $producto)
                                <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                            @endforeach
                        </select>
                        @error('edit_producto_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de hallazgo</label>
                        <select wire:model.live="edit_tipo_hallazgo_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-red-500 focus:border-red-500">
                            <option value="">Seleccione…</option>
                            @foreach($tiposHallazgo as $tipo)
                                <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                        @error('edit_tipo_hallazgo_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                    @if($mostrarUbicacionEdit)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ubicación</label>
                            <select wire:model.live="edit_ubicacion_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-red-500 focus:border-red-500">
                                <option value="">Seleccione…</option>
                                @foreach($editUbicacionesFiltradas as $u)
                                    <option value="{{ $u->id }}">{{ $u->nombre }}</option>
                                @endforeach
                            </select>
                            @error('edit_ubicacion_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>
                    @endif
                    @if($mostrarLadoEdit)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Lado (Par / Impar)</label>
                            <select wire:model="edit_lado_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-red-500 focus:border-red-500">
                                <option value="">Seleccione…</option>
                                @foreach($ladosLista as $lado)
                                    <option value="{{ $lado->id }}">{{ $lado->nombre }}</option>
                                @endforeach
                            </select>
                            @error('edit_lado_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad</label>
                        <input type="number" min="1" wire:model="edit_cantidad" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-red-500 focus:border-red-500">
                        @error('edit_cantidad') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observación</label>
                        <textarea wire:model="edit_observacion" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-red-500 focus:border-red-500" placeholder="Opcional"></textarea>
                        @error('edit_observacion') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="p-6 border-t border-gray-200 flex justify-end gap-2 bg-gray-50 rounded-b-lg">
                    <button type="button" wire:click="cerrarEditar" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50">Cancelar</button>
                    <button type="button" wire:click="guardarEdicion" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 font-medium">Guardar cambios</button>
                </div>
            </div>
        </div>
    @endif

    @if($mostrarModalHistorialEdiciones)
        @php $etiquetasHistorial = \App\Livewire\HistorialRegistros::etiquetasCampoHistorial(); @endphp
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-50 p-4" wire:click="cerrarHistorialEdiciones">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto" @click="$event.stopPropagation()">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center sticky top-0 bg-white z-10">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">📜 Trazabilidad de ediciones</h3>
                        <p class="text-sm text-gray-600 mt-1">Antes y después de cada modificación guardada.</p>
                    </div>
                    <button type="button" wire:click="cerrarHistorialEdiciones" class="text-gray-500 hover:text-gray-800 text-2xl leading-none">&times;</button>
                </div>
                <div class="p-6 space-y-8">
                    @foreach($historialEdicionesVista as $idx => $revision)
                        @php
                            $antes = $revision['antes'] ?? [];
                            $despues = $revision['despues'] ?? [];
                            $fechaEd = isset($revision['editado_en']) ? \Carbon\Carbon::parse($revision['editado_en'])->format('d/m/Y H:i') : '—';
                            $por = $revision['usuario_nombre'] ?? '—';
                        @endphp
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <div class="bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700 border-b border-gray-200">
                                Cambio {{ $idx + 1 }} · {{ $fechaEd }} · por {{ $por }}
                            </div>
                            <div class="grid md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-200">
                                <div class="p-4 bg-red-50/50">
                                    <p class="text-xs font-bold text-red-700 uppercase tracking-wide mb-3">Antes</p>
                                    <dl class="space-y-2 text-sm">
                                        @foreach($etiquetasHistorial as $campo => $etiqueta)
                                            <div class="flex flex-col sm:flex-row sm:gap-2">
                                                <dt class="text-gray-500 shrink-0 sm:w-40">{{ $etiqueta }}</dt>
                                                <dd class="text-gray-900 font-medium break-words">{{ $antes[$campo] ?? '—' }}</dd>
                                            </div>
                                        @endforeach
                                    </dl>
                                </div>
                                <div class="p-4 bg-green-50/50">
                                    <p class="text-xs font-bold text-green-700 uppercase tracking-wide mb-3">Después</p>
                                    <dl class="space-y-2 text-sm">
                                        @foreach($etiquetasHistorial as $campo => $etiqueta)
                                            <div class="flex flex-col sm:flex-row sm:gap-2">
                                                <dt class="text-gray-500 shrink-0 sm:w-40">{{ $etiqueta }}</dt>
                                                <dd class="text-gray-900 font-medium break-words">{{ $despues[$campo] ?? '—' }}</dd>
                                            </div>
                                        @endforeach
                                    </dl>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if($mostrarModalEliminados && $registrosEliminados)
        <div class="fixed inset-0 z-[55] flex items-center justify-center bg-black/50 p-3 sm:p-4 overflow-y-auto" wire:click="cerrarEliminados">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-[min(100vw-1.5rem,80rem)] max-h-[min(92vh,56rem)] my-auto overflow-hidden flex flex-col" @click="$event.stopPropagation()">
                <div class="p-4 sm:p-5 border-b border-gray-200 shrink-0 bg-gray-50 flex flex-col sm:flex-row sm:items-start gap-3 sm:justify-between">
                    <div class="flex-1 min-w-0 pr-2">
                        <h3 class="text-lg sm:text-xl font-semibold text-gray-900 break-words">Registros eliminados (archivo)</h3>
                        <p class="text-sm text-gray-600 mt-2 leading-relaxed break-words max-w-3xl">
                            Aquí queda la copia de respaldo al borrar del historial: datos del hallazgo, evidencia conservada en servidor y el usuario que eliminó el registro.
                        </p>
                        <p class="text-xs text-gray-500 mt-2">Si hay muchas columnas, usa el desplazamiento horizontal debajo de la tabla.</p>
                    </div>
                    <button type="button" wire:click="cerrarEliminados" class="shrink-0 self-end sm:self-start text-gray-500 hover:text-gray-800 text-2xl leading-none p-1 rounded hover:bg-gray-200/60" title="Cerrar">&times;</button>
                </div>
                <div class="overflow-y-auto flex-1 min-h-0 p-3 sm:p-4">
                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-inner bg-white">
                        <table class="min-w-[68rem] w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-100 sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th scope="col" class="sticky left-0 z-20 bg-gray-100 px-3 py-2.5 text-left text-xs font-medium text-gray-700 uppercase tracking-wide border-r border-gray-200 whitespace-nowrap">Eliminado el</th>
                                    <th scope="col" class="px-3 py-2.5 text-left text-xs font-medium text-gray-600 uppercase tracking-wide whitespace-nowrap">Eliminó</th>
                                    <th scope="col" class="px-3 py-2.5 text-left text-xs font-medium text-gray-600 uppercase tracking-wide whitespace-nowrap">Registro (fecha)</th>
                                    <th scope="col" class="px-3 py-2.5 text-left text-xs font-medium text-gray-600 uppercase tracking-wide whitespace-nowrap">Código</th>
                                    <th scope="col" class="px-3 py-2.5 text-left text-xs font-medium text-gray-600 uppercase tracking-wide min-w-[8rem]">Producto</th>
                                    <th scope="col" class="px-3 py-2.5 text-left text-xs font-medium text-gray-600 uppercase tracking-wide min-w-[7rem]">Tipo</th>
                                    <th scope="col" class="px-3 py-2.5 text-left text-xs font-medium text-gray-600 uppercase tracking-wide min-w-[7rem]">Registró</th>
                                    <th scope="col" class="px-3 py-2.5 text-left text-xs font-medium text-gray-600 uppercase tracking-wide whitespace-nowrap">Evidencia</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($registrosEliminados as $arch)
                                    @php
                                        $p = $arch->payload ?? [];
                                        $evUrl = \App\Livewire\HistorialRegistros::urlPublicaEvidencia($p['evidencia_path'] ?? null);
                                    @endphp
                                    <tr class="hover:bg-gray-50/80">
                                        <td class="sticky left-0 z-10 bg-white px-3 py-2.5 whitespace-nowrap text-gray-900 border-r border-gray-100 font-medium shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]">{{ $arch->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-3 py-2.5 text-gray-900 font-medium break-words max-w-[12rem]">{{ $arch->eliminado_por_nombre }}</td>
                                        <td class="px-3 py-2.5 whitespace-nowrap text-gray-700">
                                            @if(!empty($p['created_at']))
                                                {{ \Carbon\Carbon::parse($p['created_at'])->format('d/m/Y H:i') }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-3 py-2.5 font-medium text-gray-900 whitespace-nowrap">{{ $p['codigo'] ?? '—' }}</td>
                                        <td class="px-3 py-2.5 text-gray-700 break-words max-w-[14rem]">{{ $p['producto'] ?? '—' }}</td>
                                        <td class="px-3 py-2.5">
                                            @if(!empty($p['tipo_hallazgo']))
                                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ !empty($p['es_critico']) ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">{{ $p['tipo_hallazgo'] }}</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-3 py-2.5 text-gray-700 break-words max-w-[12rem]">{{ $p['usuario_registro_nombre'] ?? '—' }}</td>
                                        <td class="px-3 py-2.5 whitespace-nowrap">
                                            @if($evUrl)
                                                <button type="button"
                                                        wire:click="mostrarEvidenciaArchivoEliminado({{ $arch->id }})"
                                                        class="hover:opacity-80 transition-opacity rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                                                        title="Ver evidencia">
                                                    <img src="{{ $evUrl }}" alt="Evidencia" class="h-12 w-12 object-cover rounded border border-gray-200" loading="lazy">
                                                </button>
                                            @else
                                                <span class="text-gray-400">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-3 py-10 text-center text-gray-500">No hay registros eliminados archivados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($registrosEliminados->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 shrink-0">
                        {{ $registrosEliminados->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($mostrarModalEvidencia)
        <div class="fixed inset-0 z-[70] flex items-center justify-center bg-black bg-opacity-50" wire:click="cerrarModalEvidencia">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-3xl max-h-[90vh] overflow-auto" @click="$event.stopPropagation()">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Evidencia</h3>
                    <button wire:click="cerrarModalEvidencia" class="text-gray-500 hover:text-gray-800 text-2xl">&times;</button>
                </div>
                <div class="flex justify-center bg-gray-100 rounded-lg p-8 min-h-[400px]">
                    @if($evidenciaMostradaUrl)
                        <img src="{{ $evidenciaMostradaUrl }}" 
                             alt="Evidencia" 
                             class="max-w-full max-h-[70vh] object-contain rounded-lg"
                             loading="lazy">
                    @else
                        <div class="text-center text-gray-500 py-8">
                            <p>No hay imagen disponible</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
