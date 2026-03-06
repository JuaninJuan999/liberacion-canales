<div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow-lg p-8">
        {{-- Header --}}
        <div class="mb-8 border-b-2 border-red-500 pb-4">
            <h2 class="text-3xl font-bold text-red-700">🚨 Registro Hallazgos - Tolerancia Cero</h2>
            <p class="text-gray-600 mt-2">Registra los hallazgos críticos de MATERIA FECAL, CONTENIDO RUMINAL y LECHE VISIBLE</p>
            <p class="text-sm text-gray-500 mt-1">Registros del día: <span class="font-bold text-lg text-red-600">{{ $total_registros_dia }}</span></p>
        </div>

        {{-- Mensaje de Feedback --}}
        @if($mensaje)
            <div class="mb-6 p-4 rounded-lg {{ $tipoMensaje === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700' }}">
                {{ $mensaje }}
            </div>
        @endif

        <form wire:submit="registrar" class="space-y-6">
            {{-- Cuarto (Anterior/Posterior) --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    🥩 Cuarto<span class="text-red-500">*</span>
                </label>
                <div class="flex space-x-2">
                    @foreach($productos as $producto)
                        <button type="button"
                                wire:click="$set('producto_id', {{ $producto->id }})"
                                class="flex-1 px-4 py-3 border rounded-md text-center font-semibold transition-colors duration-200 ease-in-out
                                       {{ $producto_id == $producto->id 
                                            ? 'bg-red-600 text-white border-red-600' 
                                            : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }}">
                            {{ $producto->nombre }}
                        </button>
                    @endforeach
                </div>
                @error('producto_id') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                @if($nombreProductoSeleccionado)
                    <p class="text-sm text-blue-600 mt-2">✓ {{ $nombreProductoSeleccionado }} seleccionado</p>
                @endif
            </div>

            <div>
                {{-- Campo: Tipo de Hallazgo --}}
                <label for="tipo_hallazgo_id" class="block text-sm font-semibold text-gray-700 mb-2">
                    ⚠️ Tipo de Hallazgo<span class="text-red-500">*</span>
                </label>
                <select 
                    id="tipo_hallazgo_id"
                    wire:model.live="tipo_hallazgo_id"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition cursor-pointer"
                    required
                >
                    <option value="">-- Selecciona un tipo --</option>
                    @foreach($tiposHallazgo as $tipo)
                        <option value="{{ $tipo['id'] }}">{{ $tipo['nombre'] }}</option>
                    @endforeach
                </select>
                @error('tipo_hallazgo_id') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                @if($nombreTipoSeleccionado)
                    <p class="text-sm text-blue-600 mt-2">✓ {{ $nombreTipoSeleccionado }} seleccionado</p>
                @endif
            </div>

            {{-- Campo Condicional: Ubicación --}}
            @if($mostrarUbicacion)
                <div class="transition-all duration-300 ease-in-out">
                    <label for="ubicacion_id" class="block text-sm font-semibold text-gray-700 mb-2">
                        📍 Ubicación Específica <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="ubicacion_id"
                        wire:model.live="ubicacion_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition cursor-pointer"
                        required
                    >
                        <option value="">-- Selecciona una ubicación --</option>
                        @foreach($ubicacionesDisponibles as $ubicacion)
                            <option value="{{ $ubicacion->id }}">{{ $ubicacion->nombre }}</option>
                        @endforeach
                    </select>
                    @error('ubicacion_id') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                </div>
            @endif

            {{-- Botones --}}
            <div class="flex gap-3 justify-end pt-4 border-t border-gray-200">
                <button 
                    type="reset"
                    class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition font-semibold"
                >
                    🔄 Limpiar
                </button>
                <button 
                    type="submit"
                    class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold shadow-lg"
                >
                    ✅ Registrar Hallazgo
                </button>
            </div>
        </form>

        {{-- Resumen de Hallazgos del Día --}}
        <div class="mt-8 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 mb-4">📊 Resumen de Hoy</h3>
            <p class="text-center text-lg text-gray-600">
                Total registros: <span class="font-bold text-red-600">{{ $total_registros_dia }}</span>
            </p>
        </div>
    </div>
</div>
