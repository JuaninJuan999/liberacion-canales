<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    {{-- Encabezado --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Registro de Hallazgos</h2>
        <p class="mt-1 text-sm text-gray-600">Fecha: {{ \Carbon\Carbon::parse($fecha_actual)->format('d/m/Y') }}</p>
        <p class="text-sm text-gray-600">Total registros hoy: <span class="font-semibold text-blue-600">{{ $total_registros_dia }}</span></p>
    </div>

    {{-- Mensajes --}}
    @if($mensaje)
        <div class="mb-4 p-4 rounded-lg {{ $tipoMensaje === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' }}">
            <p class="font-medium">{{ $mensaje }}</p>
        </div>
    @endif

    {{-- Formulario --}}
    <form wire:submit.prevent="registrar" class="bg-white shadow-md rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            {{-- Número de Canal --}}
            <div>
                <label for="numero_canal" class="block text-sm font-medium text-gray-700 mb-2">
                    Número de Canal <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       wire:model="numero_canal" 
                       id="numero_canal"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Ej: 12345">
                @error('numero_canal') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            {{-- Producto --}}
            <div>
                <label for="producto_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Producto <span class="text-red-500">*</span>
                </label>
                <select wire:model="producto_id" 
                        id="producto_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Seleccione...</option>
                    @foreach($productos as $producto)
                        <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                    @endforeach
                </select>
                @error('producto_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            {{-- Puesto de Trabajo --}}
            <div>
                <label for="puesto_trabajo_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Puesto de Trabajo <span class="text-red-500">*</span>
                </label>
                <select wire:model="puesto_trabajo_id" 
                        wire:change="actualizarOperariosPorPuesto"
                        id="puesto_trabajo_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Seleccione...</option>
                    @foreach($puestosTrabajo as $puesto)
                        <option value="{{ $puesto->id }}">{{ $puesto->nombre }}</option>
                    @endforeach
                </select>
                @error('puesto_trabajo_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            {{-- Operario --}}
            <div>
                <label for="operario_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Operario <span class="text-red-500">*</span>
                </label>
                <select wire:model="operario_id" 
                        id="operario_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        {{ !$puesto_trabajo_id ? 'disabled' : '' }}>
                    <option value="">Seleccione...</option>
                    @foreach($operarios as $operario)
                        <option value="{{ $operario->id }}">{{ $operario->nombre_completo }}</option>
                    @endforeach
                </select>
                @error('operario_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            {{-- Tipo de Hallazgo --}}
            <div>
                <label for="tipo_hallazgo_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Tipo de Hallazgo <span class="text-red-500">*</span>
                </label>
                <select wire:model="tipo_hallazgo_id" 
                        id="tipo_hallazgo_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Seleccione...</option>
                    @foreach($tiposHallazgo as $tipo)
                        <option value="{{ $tipo->id }}" class="{{ $tipo->es_critico ? 'text-red-600 font-semibold' : '' }}">
                            {{ $tipo->nombre }} {{ $tipo->es_critico ? '(CRÍTICO)' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('tipo_hallazgo_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            {{-- Ubicación --}}
            <div>
                <label for="ubicacion_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Ubicación <span class="text-red-500">*</span>
                </label>
                <select wire:model="ubicacion_id" 
                        id="ubicacion_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Seleccione...</option>
                    @foreach($ubicaciones as $ubicacion)
                        <option value="{{ $ubicacion->id }}">{{ $ubicacion->nombre }}</option>
                    @endforeach
                </select>
                @error('ubicacion_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            {{-- Lado --}}
            <div>
                <label for="lado_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Lado <span class="text-red-500">*</span>
                </label>
                <select wire:model="lado_id" 
                        id="lado_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Seleccione...</option>
                    @foreach($lados as $lado)
                        <option value="{{ $lado->id }}">{{ $lado->nombre }}</option>
                    @endforeach
                </select>
                @error('lado_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            {{-- Observaciones --}}
            <div class="md:col-span-2 lg:col-span-3">
                <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-2">
                    Observaciones
                </label>
                <textarea wire:model="observaciones" 
                          id="observaciones"
                          rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Detalles adicionales del hallazgo..."></textarea>
                @error('observaciones') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Botones --}}
        <div class="mt-6 flex justify-end space-x-3">
            <button type="button" 
                    wire:click="limpiarFormulario"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Limpiar
            </button>
            <button type="submit" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Registrar Hallazgo
            </button>
        </div>
    </form>
</div>