
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

            {{-- Evidencia (Foto) con Compresión --}}
            <div x-data="fotoCompressor()">
                <label class="block text-sm font-medium text-gray-700 mb-3 flex items-center gap-2">
                    📷 Evidencia
                </label>
                <div wire:loading wire:target="foto" class="mb-3 p-3 bg-blue-50 text-blue-700 rounded-lg text-sm flex items-center gap-2">
                    <div class="animate-spin rounded-full h-4 w-4 border-2 border-blue-700 border-t-transparent"></div>
                    ⏳ Comprimiendo y cargando imagen...
                </div>
                
                <div class="space-y-4">
                    {{-- Input oculto con capture --}}
                    <input type="file" 
                           @change="comprimirYCargar($event)"
                           id="foto" 
                           class="hidden"
                           accept="image/*"
                           capture="environment">
                    
                    {{-- Estado de compresión --}}
                    <div x-show="comprimiendo" class="mb-3 p-3 bg-amber-50 text-amber-700 rounded-lg text-sm">
                        <p x-text="`Comprimiendo: ${porcentajeCompresion}%`"></p>
                    </div>

                    {{-- Botón para abrir cámara --}}
                    <label for="foto" class="block cursor-pointer">
                        <div class="relative group">
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-dashed border-blue-400 rounded-lg p-8 text-center hover:border-blue-600 hover:bg-blue-200 transition-all duration-300 transform hover:scale-105">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-blue-600 mx-auto group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm0-13c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5z"/>
                                        <path d="M12 7c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5z"/>
                                    </svg>
                                    <div>
                                        <p class="font-semibold text-gray-800">Tomar Foto</p>
                                        <p class="text-xs text-gray-600 mt-1">Haz clic para abrir la cámara<br>Se comprimirá automáticamente</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </label>

                    {{-- Vista previa de la foto --}}
                    @if ($foto)
                        <div class="relative">
                            <div class="bg-white rounded-lg border-2 border-green-400 p-4 shadow-lg">
                                <div class="flex items-center gap-3 mb-3">
                                    <span class="inline-flex items-center justify-center w-8 h-8 bg-green-100 rounded-full">
                                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </span>
                                    <span class="text-sm font-semibold text-green-700">✅ Imagen lista</span>
                                </div>
                                <img class="w-full h-48 object-cover rounded-lg border border-gray-300" src="{{ $foto->temporaryUrl() }}" alt="Previsualización de evidencia">
                                <button type="button" 
                                        wire:click="$set('foto', null)"
                                        class="mt-3 w-full px-3 py-2 bg-red-100 text-red-700 hover:bg-red-200 rounded-lg text-sm font-semibold transition">
                                    🗑️ Cambiar Foto
                                </button>
                            </div>
                        </div>
                    @else
                        <p class="text-xs text-gray-500 text-center py-2">💡 Toca el área de arriba para capturar una foto</p>
                    @endif
                </div>
                @error('foto') <span class="text-red-500 text-xs mt-2 block">{{ $message }}</span> @enderror
            </div>

            {{-- Script de compresión --}}
            <script>
                function fotoCompressor() {
                    return {
                        comprimiendo: false,
                        porcentajeCompresion: 0,
                        
                        async comprimirYCargar(event) {
                            const archivo = event.target.files[0];
                            if (!archivo) return;

                            this.comprimiendo = true;
                            this.porcentajeCompresion = 0;

                            try {
                                // Crear reader para leer la imagen
                                const reader = new FileReader();
                                reader.onload = async (e) => {
                                    const img = new Image();
                                    img.onload = async () => {
                                        // Crear con compresión
                                        const canvas = document.createElement('canvas');
                                        let { width, height } = img;
                                        
                                        // Escalar si es muy grande (máximo 1280px)
                                        const maxWidth = 1280;
                                        const maxHeight = 1280;
                                        
                                        if (width > maxWidth || height > maxHeight) {
                                            const ratio = Math.min(maxWidth / width, maxHeight / height);
                                            width = Math.round(width * ratio);
                                            height = Math.round(height * ratio);
                                        }
                                        
                                        canvas.width = width;
                                        canvas.height = height;
                                        
                                        const ctx = canvas.getContext('2d');
                                        ctx.drawImage(img, 0, 0, width, height);
                                        
                                        // Comprimir JPEG con calidad progresiva
                                        let quality = 0.9;
                                        let comprimido = null;
                                        
                                        for (let i = 0; i < 3; i++) {
                                            comprimido = await this.canvasToBlob(canvas, 'image/jpeg', quality);
                                            
                                            // Si archivo es menor a 2MB, usar
                                            if (comprimido.size < 2097152) break;
                                            
                                            quality -= 0.2;
                                            this.porcentajeCompresion = Math.round((1 - (comprimido.size / archivo.size)) * 100);
                                        }
                                        
                                        // Convertir a File
                                        const archivoComprimido = new File(
                                            [comprimido],
                                            `foto_${Date.now()}.jpg`,
                                            { type: 'image/jpeg' }
                                        );
                                        
                                        // Simular que se cambió el input pero con el archivo comprimido
                                        const dataTransfer = new DataTransfer();
                                        dataTransfer.items.add(archivoComprimido);
                                        document.getElementById('foto').files = dataTransfer.files;
                                        
                                        // Trigger Livewire update
                                        @this.upload('foto', archivoComprimido, false, null, null, () => {
                                            this.comprimiendo = false;
                                        });
                                    };
                                    img.src = e.target.result;
                                };
                                reader.readAsDataURL(archivo);
                            } catch (error) {
                                console.error('Error al comprimir:', error);
                                this.comprimiendo = false;
                            }
                        },

                        canvasToBlob(canvas, type, quality) {
                            return new Promise((resolve) => {
                                canvas.toBlob(resolve, type, quality);
                            });
                        }
                    }
                }
            </script>

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
