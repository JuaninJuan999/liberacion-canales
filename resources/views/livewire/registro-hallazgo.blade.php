
<div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Liberación de Canales</h2>
            <p class="mt-1 text-sm text-gray-600">Fecha: {{ \Carbon\Carbon::parse($fecha_actual)->format('d/m/Y') }} | Total hoy: <span class="font-semibold text-blue-600">{{ $total_registros_dia }}</span></p>
        </div>
    </div>

    {{-- Messages --}}
    @if($mensaje)
        <div 
            x-data="{ show: true }" 
            x-show="show" 
            x-init="setTimeout(() => show = false, 3000)"
            x-transition
            class="mb-4 p-4 rounded-lg {{ $tipoMensaje === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' }}"
            wire:click="limpiarMensaje"
        >
            <p class="font-medium">{{ $mensaje }}</p>
        </div>
    @endif

    {{-- Form --}}
    <form wire:submit.prevent="registrar" class="bg-white shadow-md rounded-lg p-6">
        <div class="space-y-6">
            
            {{-- Código (Número de Canal) --}}
            <div>
                <label for="numero_canal" class="block text-sm font-medium text-gray-700 mb-1">
                    Código <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       wire:model.lazy="numero_canal" 
                       id="numero_canal"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Ej: 2306-02256"
                       required>
                @error('numero_canal') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            {{-- Producto --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Producto <span class="text-red-500">*</span>
                </label>
                <div class="flex space-x-2">
                    @foreach($productos as $producto)
                        <button type="button"
                                wire:click="$set('producto_id', {{ $producto->id }})"
                                class="flex-1 px-4 py-3 border rounded-md text-center font-semibold transition-colors duration-200 ease-in-out
                                       {{ $producto_id == $producto->id 
                                            ? 'bg-blue-600 text-white border-blue-600' 
                                            : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }}">
                            {{ $producto->nombre }}
                        </button>
                    @endforeach
                </div>
                @error('producto_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            {{-- Hallazgo --}}
            <div>
                <label for="tipo_hallazgo_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Hallazgo <span class="text-red-500">*</span>
                </label>
                <select wire:model.live="tipo_hallazgo_id" 
                        id="tipo_hallazgo_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        required>
                    <option value="">Seleccione un hallazgo...</option>
                    @foreach($tiposHallazgo as $tipo)
                        <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                    @endforeach
                </select>
                @error('tipo_hallazgo_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            {{-- Conditional Ubicacion Field --}}
            @if($mostrarUbicacion)
                <div class="transition-all duration-300 ease-in-out">
                    <label for="ubicacion_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Observación <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.live="ubicacion_id" 
                            id="ubicacion_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                            required>
                        <option value="">Seleccione una opción...</option>
                        @foreach($ubicaciones as $ubicacion)
                            <option value="{{ $ubicacion->id }}">{{ $ubicacion->nombre }}</option>
                        @endforeach
                    </select>
                    @error('ubicacion_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            @endif

            {{-- Conditional Lado Field --}}
            @if($mostrarLado)
                <div class="transition-all duration-300 ease-in-out">
                    <label for="lado_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Detalle (Pierna) <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.live="lado_id" 
                            id="lado_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                            required>
                        <option value="">Seleccione par/impar...</option>
                        @foreach($lados as $lado)
                            <option value="{{ $lado->id }}">{{ $lado->nombre }}</option>
                        @endforeach
                    </select>
                    @error('lado_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            @endif

            {{-- Evidencia (Foto) --}}
            <div>
                <label for="foto" class="block text-sm font-medium text-gray-700 mb-1">
                    Evidencia
                </label>
                <div wire:loading wire:target="foto" class="text-sm text-gray-500">Cargando...</div>
                <div class="mt-1 flex items-center space-x-4">
                    <input type="file" wire:model="foto" id="foto" class="hidden">
                    <label for="foto" class="cursor-pointer bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 flex items-center justify-center w-full">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </label>
                    @if ($foto)
                        <div class="flex-shrink-0">
                            <img class="h-16 w-16 object-cover rounded" src="{{ $foto->temporaryUrl() }}" alt="Previsualización de evidencia">
                        </div>
                    @endif
                </div>
                @error('foto') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

        </div>

        {{-- Buttons --}}
        <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end space-x-3">
            <button type="button" 
                    wire:click="limpiarFormulario"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Cancelar
            </button>
            <button type="submit" 
                    class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    wire:loading.attr="disabled"
                    wire:loading.class="bg-blue-400">
                <span wire:loading.remove wire:target="registrar">Guardar</span>
                <span wire:loading wire:target="registrar">Guardando...</span>
            </button>
        </div>
    </form>
</div>
