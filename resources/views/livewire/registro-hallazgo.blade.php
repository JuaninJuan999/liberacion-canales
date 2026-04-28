
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

    {{-- Form: no usar disabled en el botón (Livewire/Alpine lo dejaban pegado). Bloqueo de envío solo si hay preview local y el servidor aún no tiene foto. --}}
    <form x-data="fotoCompressor()"
          x-on:submit.prevent="
              const servidorTieneFoto = $wire.foto != null && $wire.foto !== '';
              if (previewInstantanea && !servidorTieneFoto) return;
              $wire.registrar();
          "
          class="bg-white shadow-md rounded-lg p-6">
        <div class="space-y-6">
            
            {{-- Código (Número de Canal) --}}
            <div>
                <label for="numero_canal" class="block text-sm font-medium text-gray-700 mb-1">
                    Código <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       inputmode="numeric"
                       wire:model.lazy="numero_canal"
                       id="numero_canal"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Ej: 2306-02256"
                       autocomplete="off"
                       enterkeyhint="done"
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
                        @foreach($ubicacionesFiltradas as $ubicacion)
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
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3 flex items-center gap-2">
                    📷 Evidencia
                </label>
                <div wire:loading wire:target="foto" class="mb-3 p-3 bg-blue-50 text-blue-700 rounded-lg text-sm flex items-center gap-2">
                    <div class="animate-spin rounded-full h-4 w-4 border-2 border-blue-700 border-t-transparent"></div>
                    ⏳ Subiendo evidencia...
                </div>
                
                <div class="space-y-4">
                    {{-- Galería: sin capture → el sistema ofrece galería / archivos (no fuerza cámara) --}}
                    <input type="file"
                           @change="comprimirYCargar($event)"
                           id="foto-galeria"
                           class="hidden"
                           accept="image/*">

                    {{-- Cámara: solo cuando el usuario elige explícitamente tomar foto --}}
                    <input type="file"
                           @change="comprimirYCargar($event)"
                           id="foto-camara"
                           class="hidden"
                           accept="image/*"
                           capture="environment">

                    {{-- Galería / cámara solo hasta elegir foto; al tener evidencia solo vista previa + Cambiar foto --}}
                    @if (!$foto)
                        {{-- Vista previa instantánea (blob local) mientras Livewire comprime/sube --}}
                        <div x-show="previewInstantanea" x-cloak class="relative mb-4">
                            <div class="bg-white rounded-lg border-2 border-green-400 p-4 shadow-lg">
                                <div class="flex items-center gap-3 mb-3">
                                    <span class="inline-flex items-center justify-center w-8 h-8 bg-green-100 rounded-full">
                                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </span>
                                    <span class="text-sm font-semibold text-green-700">✅ Evidencia seleccionada</span>
                                </div>
                                <img class="w-full h-48 object-cover rounded-lg border border-gray-300" x-bind:src="previewInstantanea" alt="Previsualización de evidencia">
                                <button type="button"
                                        @click="cancelarPreviewLocal()"
                                        class="mt-3 w-full px-3 py-2 bg-red-100 text-red-700 hover:bg-red-200 rounded-lg text-sm font-semibold transition">
                                    🗑️ Cambiar Foto
                                </button>
                            </div>
                        </div>
                        <div x-show="!previewInstantanea">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label for="foto-galeria" class="block cursor-pointer">
                                <div class="relative group h-full">
                                    <div class="bg-gradient-to-br from-slate-50 to-slate-100 border-2 border-dashed border-slate-400 rounded-lg p-6 text-center hover:border-blue-500 hover:bg-blue-50 transition-all duration-300 h-full flex flex-col items-center justify-center min-h-[140px]">
                                        <svg class="w-10 h-10 text-slate-600 mx-auto mb-2 group-hover:scale-105 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <p class="font-semibold text-gray-800">Elegir de galería</p>
                                        <p class="text-xs text-gray-600 mt-1">Fotos ya guardadas en el dispositivo</p>
                                    </div>
                                </div>
                            </label>
                            <label for="foto-camara" class="block cursor-pointer">
                                <div class="relative group h-full">
                                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-dashed border-blue-400 rounded-lg p-6 text-center hover:border-blue-600 hover:bg-blue-200 transition-all duration-300 h-full flex flex-col items-center justify-center min-h-[140px]">
                                        <svg class="w-10 h-10 text-blue-600 mx-auto mb-2 group-hover:scale-105 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 15.2a3.2 3.2 0 100-6.4 3.2 3.2 0 000 6.4z"/>
                                            <path d="M9 2L7.17 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2h-3.17L15 2H9zm3 15c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5z"/>
                                        </svg>
                                        <p class="font-semibold text-gray-800">Tomar foto</p>
                                        <p class="text-xs text-gray-600 mt-1">Abre la cámara para capturar ahora</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 text-center py-2">💡 Fotos JPG/PNG/WebP ≤ ~520 KB se suben tal cual (rápido); si pesan más, se comprimen antes de enviar.</p>
                        </div>
                    @endif

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
                                        @click="revocarPreview(); document.getElementById('foto-galeria').value=''; document.getElementById('foto-camara').value='';"
                                        class="mt-3 w-full px-3 py-2 bg-red-100 text-red-700 hover:bg-red-200 rounded-lg text-sm font-semibold transition">
                                    🗑️ Cambiar Foto
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
                @error('foto') <span class="text-red-500 text-xs mt-2 block">{{ $message }}</span> @enderror
            </div>

            {{-- Script de compresión: objectURL (evita base64), menos px, menos pasadas toBlob --}}
            <script>
                function fotoCompressor() {
                    return {
                        /** Vista previa local (blob:) al elegir archivo; hasta que Livewire confirme `foto`. */
                        previewInstantanea: null,

                        init() {
                            if (this.previewInstantanea === undefined) {
                                this.previewInstantanea = null;
                            }
                            const self = this;
                            const alTerminarSubida = function () {
                                self.revocarPreview();
                            };
                            window.addEventListener('livewire-upload-finish', alTerminarSubida);
                            window.addEventListener('livewire-upload-error', alTerminarSubida);
                            window.addEventListener('livewire-upload-cancel', alTerminarSubida);
                        },

                        revocarPreview() {
                            if (this.previewInstantanea) {
                                try { URL.revokeObjectURL(this.previewInstantanea); } catch (e) {}
                                this.previewInstantanea = null;
                            }
                        },

                        cancelarPreviewLocal() {
                            this.revocarPreview();
                            document.getElementById('foto-galeria').value = '';
                            document.getElementById('foto-camara').value = '';
                        },

                        /** Máximo lado en px (evidencia; bajar = menos trabajo en el celular). */
                        MAX_LADO: 1024,
                        /** Tope Laravel: max:2048 (KB); dejamos margen en bytes. */
                        MAX_BYTES: 2 * 1024 * 1024,
                        /** Por debajo de esto no reencodamos (subida directa Livewire, lo más rápido en campo). */
                        UMBRAL_SIN_COMPRIMIR: 520 * 1024,

                        /** MIME estándar + image/jpg (algunos Android). Si type viene vacío, usamos extensión del nombre. */
                        esJpegPngWebp(archivo) {
                            const t = (archivo.type || '').trim().toLowerCase();
                            if (/^image\/(jpeg|jpg|png|webp)$/i.test(t)) return true;
                            const n = (archivo.name || '').toLowerCase();
                            return /\.(jpe?g|png|webp)$/i.test(n);
                        },

                        async comprimirYCargar(event) {
                            const inputOrigen = event.target;
                            const archivo = inputOrigen.files[0];
                            if (!archivo) return;

                            this.revocarPreview();
                            this.previewInstantanea = URL.createObjectURL(archivo);

                            // Fotos ya livianas: sin canvas/toBlob (evidencia intacta para validación / menos trabajo en celular)
                            if (
                                archivo.size <= this.UMBRAL_SIN_COMPRIMIR &&
                                this.esJpegPngWebp(archivo)
                            ) {
                                try {
                                    // Livewire 3: upload(name, file, onFinish, onError, onProgress) — no usar firma antigua con false/null
                                    @this.upload(
                                        'foto',
                                        archivo,
                                        () => { this.revocarPreview(); },
                                        () => { this.revocarPreview(); },
                                        () => {}
                                    );
                                } catch (e) {
                                    console.error(e);
                                    this.revocarPreview();
                                }
                                return;
                            }

                            let objectUrl = null;
                            try {
                                objectUrl = URL.createObjectURL(archivo);
                                const img = new Image();
                                img.src = objectUrl;
                                if (typeof img.decode === 'function') {
                                    await img.decode();
                                } else {
                                    await new Promise(function (resolve, reject) {
                                        img.onload = resolve;
                                        img.onerror = reject;
                                    });
                                }

                                let w = img.naturalWidth || img.width;
                                let h = img.naturalHeight || img.height;
                                const maxLado = this.MAX_LADO;
                                if (w > maxLado || h > maxLado) {
                                    const ratio = Math.min(maxLado / w, maxLado / h);
                                    w = Math.round(w * ratio);
                                    h = Math.round(h * ratio);
                                }

                                const canvas = document.createElement('canvas');
                                canvas.width = w;
                                canvas.height = h;
                                const ctx = canvas.getContext('2d');
                                if (ctx && ctx.imageSmoothingEnabled !== undefined) {
                                    ctx.imageSmoothingQuality = 'medium';
                                }
                                ctx.drawImage(img, 0, 0, w, h);

                                // Una pasada según tamaño original; segunda solo si sigue pasando el límite
                                let q = archivo.size > 5 * 1024 * 1024 ? 0.72 : 0.82;
                                let comprimido = await this.canvasToBlob(canvas, 'image/jpeg', q);
                                if (comprimido.size > this.MAX_BYTES) {
                                    comprimido = await this.canvasToBlob(canvas, 'image/jpeg', 0.62);
                                }
                                if (comprimido.size > this.MAX_BYTES) {
                                    comprimido = await this.canvasToBlob(canvas, 'image/jpeg', 0.52);
                                }

                                const archivoComprimido = new File(
                                    [comprimido],
                                    'foto_' + Date.now() + '.jpg',
                                    { type: 'image/jpeg' }
                                );

                                const dataTransfer = new DataTransfer();
                                dataTransfer.items.add(archivoComprimido);
                                inputOrigen.files = dataTransfer.files;

                                @this.upload(
                                    'foto',
                                    archivoComprimido,
                                    () => { this.revocarPreview(); },
                                    () => { this.revocarPreview(); },
                                    () => {}
                                );
                            } catch (error) {
                                console.error('Error al comprimir:', error);
                                this.revocarPreview();
                            } finally {
                                if (objectUrl) {
                                    try { URL.revokeObjectURL(objectUrl); } catch (e) {}
                                }
                            }
                        },

                        canvasToBlob(canvas, type, quality) {
                            return new Promise(function (resolve, reject) {
                                try {
                                    canvas.toBlob(function (blob) {
                                        if (blob) resolve(blob);
                                        else reject(new Error('toBlob vacío'));
                                    }, type, quality);
                                } catch (e) {
                                    reject(e);
                                }
                            });
                        }
                    };
                }
            </script>

        </div>

        {{-- Buttons --}}
        <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end space-x-3">
            {{-- Enlace directo (recarga completa) para no romper el JS del sidebar con wire:navigate --}}
            <a href="{{ route('home') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Cancelar
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    wire:loading.class="opacity-70 cursor-wait"
                    wire:target="registrar,foto">
                <span wire:loading.remove wire:target="registrar,foto">Guardar</span>
                <span wire:loading wire:target="foto">Esperando foto…</span>
                <span wire:loading wire:target="registrar">Guardando...</span>
            </button>
        </div>
    </form>
</div>
