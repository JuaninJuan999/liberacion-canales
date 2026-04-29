<div>
    <div class="space-y-6">
        {{-- Mensajes Flash --}}
        @if (session()->has('success'))
            <div class="rounded bg-green-100 text-green-800 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="rounded bg-red-100 text-red-800 px-4 py-3 text-sm">
                {{ session('error') }}
            </div>
        @endif

        @if (session()->has('info'))
            <div class="rounded bg-blue-100 text-blue-800 px-4 py-3 text-sm">
                {{ session('info') }}
            </div>
        @endif

        {{-- Controles superiores --}}
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <div class="flex flex-wrap gap-4 items-end">
                {{-- Selector de fecha --}}
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Fecha de Operación
                    </label>
                    <input type="date" 
                           wire:model.live="fecha_operacion"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                {{-- Botones de acción --}}
                <div class="flex gap-2">
                    <button type="button"
                            wire:click="copiarDiaAnterior"
                            class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition text-sm">
                        📋 Copiar día anterior
                    </button>
                    
                    <button type="button"
                            wire:click="limpiarAsignaciones"
                            class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition text-sm">
                        🧹 Limpiar
                    </button>
                    
                    <button type="button"
                            wire:click="guardarAsignaciones"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition text-sm font-semibold">
                        💾 Guardar Asignaciones
                    </button>

                    <button type="button"
                            wire:click="refrescarListaOperarios"
                            class="px-4 py-2 bg-white border border-gray-300 text-gray-800 rounded-md hover:bg-gray-50 transition text-sm"
                            title="Recarga el catálogo desde base de datos sin borrar las asignaciones actuales">
                        🔃 Actualizar lista
                    </button>
                </div>
            </div>
        </div>

        {{-- Tabla de asignaciones --}}
        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900">
                    Asignación de Operarios a Puestos
                </h3>
                <p class="text-sm text-gray-600 mb-4">
                    Cada operario solo puede aparecer en <strong>un</strong> puesto a la vez; al elegirlo en un desplegable, deja de mostrarse en los demás hasta que lo quite de ese puesto.
                </p>

                @if($puestos->count() > 0)
                    @if($operariosDisponibles->count() === 0)
                        <div class="rounded-lg bg-amber-50 border border-amber-200 text-amber-900 px-4 py-3 text-sm mb-4">
                            No hay operarios activos en el catálogo. Use «Crear nuevo operario» en el desplegable o cree uno desde el catálogo y pulse «Actualizar lista» si ya existía fuera de esta pantalla.
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Puesto de Trabajo
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Operario Asignado
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($puestos as $puesto)
                                    @php
                                        $opcionesCombo = $this->operariosOpcionesPara($puesto->id)
                                            ->map(fn ($o) => ['id' => (string) $o->id, 'nombre' => $o->nombre])
                                            ->values()
                                            ->all();
                                        $comboVersion = md5(json_encode($opcionesCombo));
                                    @endphp
                                    <tr class="hover:bg-gray-50" wire:key="puesto-row-{{ $puesto->id }}">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $puesto->nombre }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 align-top min-w-[14rem]">
                                            <div
                                                wire:key="combo-{{ $puesto->id }}-{{ $comboVersion }}"
                                                class="relative w-full max-w-md"
                                                x-data="{
                                                    pid: {{ $puesto->id }},
                                                    opts: @js($opcionesCombo),
                                                    selectedId: @entangle('asignaciones.'.$puesto->id).live,
                                                    query: '',
                                                    open: false,
                                                    top: 0,
                                                    left: 0,
                                                    width: 280,
                                                    _reposHandler: null,
                                                    reposition() {
                                                        const el = this.$refs.triggerBtn;
                                                        if (!el) return;
                                                        const r = el.getBoundingClientRect();
                                                        this.top = r.bottom + 6;
                                                        this.left = r.left;
                                                        this.width = Math.max(r.width, 220);
                                                    },
                                                    cerrarPanel() {
                                                        this.open = false;
                                                        this.query = '';
                                                        if (this._reposHandler) {
                                                            window.removeEventListener('scroll', this._reposHandler, true);
                                                            window.removeEventListener('resize', this._reposHandler);
                                                            this._reposHandler = null;
                                                        }
                                                    },
                                                    toggle() {
                                                        if (this.open) {
                                                            this.cerrarPanel();
                                                            return;
                                                        }
                                                        this.open = true;
                                                        this.query = '';
                                                        this.$nextTick(() => {
                                                            this.reposition();
                                                            this._reposHandler = () => this.reposition();
                                                            window.addEventListener('scroll', this._reposHandler, true);
                                                            window.addEventListener('resize', this._reposHandler);
                                                            this.$refs.qInput && this.$refs.qInput.focus();
                                                        });
                                                    },
                                                    get filtered() {
                                                        const q = (this.query || '').toLowerCase().trim();
                                                        if (!q) return this.opts;
                                                        return this.opts.filter(o =>
                                                            (o.nombre || '').toLowerCase().includes(q)
                                                        );
                                                    },
                                                    label() {
                                                        const id = this.selectedId;
                                                        if (id === null || id === undefined || id === '') {
                                                            return '— Sin asignar —';
                                                        }
                                                        const o = this.opts.find(x => String(x.id) === String(id));
                                                        return o ? o.nombre : '— Sin asignar —';
                                                    },
                                                    selectOperario(id) {
                                                        this.selectedId = (id === null || id === undefined || id === '')
                                                            ? ''
                                                            : String(id);
                                                        this.cerrarPanel();
                                                    },
                                                    abrirCrear() {
                                                        this.cerrarPanel();
                                                        $wire.abrirModalNuevoOperario(this.pid);
                                                    },
                                                }"
                                                @keydown.escape.window="open && cerrarPanel()"
                                            >
                                                <button
                                                    type="button"
                                                    x-ref="triggerBtn"
                                                    @click="toggle()"
                                                    class="flex w-full items-center justify-between gap-2 rounded-md border border-gray-300 bg-white px-3 py-2 text-left text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                                                >
                                                    <span class="truncate" x-text="label()"></span>
                                                    <svg class="h-4 w-4 shrink-0 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </button>

                                                <template x-teleport="body">
                                                    <div
                                                        x-show="open"
                                                        x-cloak
                                                        x-transition.opacity
                                                        class="fixed inset-0 z-[9998] bg-black/20"
                                                        style="display: none;"
                                                        @click="cerrarPanel()"
                                                    ></div>
                                                </template>
                                                <template x-teleport="body">
                                                    <div
                                                        x-show="open"
                                                        x-cloak
                                                        x-transition
                                                        class="fixed z-[9999] rounded-md border border-gray-200 bg-white shadow-xl"
                                                        style="display: none;"
                                                        :style="{ top: top + 'px', left: left + 'px', width: width + 'px' }"
                                                        @click.stop
                                                    >
                                                        <div class="border-b border-gray-100 p-2">
                                                            <label class="sr-only">Filtrar operarios</label>
                                                            <input
                                                                type="search"
                                                                x-ref="qInput"
                                                                x-model="query"
                                                                placeholder="Escriba para filtrar por nombre…"
                                                                class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                                                                autocomplete="off"
                                                                @keydown.escape="cerrarPanel()"
                                                            >
                                                        </div>
                                                        <div class="border-b border-gray-50 py-1">
                                                            <button
                                                                type="button"
                                                                class="w-full px-3 py-2 text-left text-sm font-semibold text-indigo-700 hover:bg-indigo-50"
                                                                @click="abrirCrear()"
                                                            >
                                                                ➕ Crear nuevo operario…
                                                            </button>
                                                            <button
                                                                type="button"
                                                                class="w-full px-3 py-2 text-left text-sm text-gray-600 hover:bg-gray-50"
                                                                @click="selectOperario('')"
                                                            >
                                                                — Sin asignar —
                                                            </button>
                                                        </div>
                                                        <div class="max-h-60 overflow-y-auto py-1">
                                                            <template x-for="op in filtered" :key="op.id">
                                                                <button
                                                                    type="button"
                                                                    class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100"
                                                                    x-text="op.nombre"
                                                                    @click="selectOperario(op.id)"
                                                                ></button>
                                                            </template>
                                                            <div
                                                                class="px-3 py-2 text-xs text-gray-500"
                                                                x-show="filtered.length === 0 && query.trim().length > 0"
                                                            >
                                                                No hay coincidencias con ese texto.
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="text-gray-400 mb-4 text-4xl">⚠️</div>
                        <p class="text-gray-500">No hay puestos de trabajo registrados.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Resumen --}}
        @if($puestos->count() > 0)
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-800">
                            <span class="font-semibold">Total puestos:</span> {{ $puestos->count() }}
                        </p>
                        <p class="text-sm text-blue-800">
                            <span class="font-semibold">Asignados:</span> 
                            {{ collect($asignaciones)->filter()->count() }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-blue-800">
                            <span class="font-semibold">Fecha:</span> 
                            {{ \Carbon\Carbon::parse($fecha_operacion)->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Modal: nuevo operario (mismo criterio que operarios/create) --}}
        @if ($modalNuevoOperario)
            <div
                class="fixed inset-0 z-[10050] flex items-center justify-center p-4"
                role="dialog"
                aria-modal="true"
                aria-labelledby="titulo-modal-nuevo-operario"
                x-data
                x-on:keydown.escape.window="$wire.cerrarModalNuevoOperario()"
            >
                <button
                    type="button"
                    class="absolute inset-0 bg-black/40 cursor-default"
                    wire:click="cerrarModalNuevoOperario"
                    aria-label="Cerrar"
                ></button>

                <div
                    class="relative z-10 w-full max-w-lg rounded-lg bg-white shadow-xl ring-1 ring-black/5"
                    wire:click.stop
                >
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h4 id="titulo-modal-nuevo-operario" class="text-lg font-semibold text-gray-900">
                            Nuevo operario
                        </h4>
                        <p class="mt-1 text-sm text-gray-600">
                            Se guardará en el catálogo de operarios igual que desde «Gestión de operarios» en el menú.
                        </p>
                    </div>

                    <form wire:submit.prevent="guardarNuevoOperario" class="px-6 py-4 space-y-4">
                        <div>
                            <label for="modal-op-nombre" class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre completo *
                            </label>
                            <input
                                id="modal-op-nombre"
                                type="text"
                                wire:model.blur="nuevo_operario_nombre"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                autocomplete="name"
                                autofocus
                            >
                            @error('nuevo_operario_nombre')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="modal-op-documento" class="block text-sm font-medium text-gray-700 mb-1">
                                Documento de identidad
                            </label>
                            <input
                                id="modal-op-documento"
                                type="text"
                                wire:model.blur="nuevo_operario_documento"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Opcional"
                                autocomplete="off"
                            >
                            @error('nuevo_operario_documento')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    wire:model="nuevo_operario_activo"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                <span class="text-sm text-gray-700">Operario activo</span>
                            </label>
                            @error('nuevo_operario_activo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                            <button
                                type="button"
                                wire:click="cerrarModalNuevoOperario"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-md hover:bg-blue-700 transition disabled:opacity-60"
                            >
                                <span wire:loading.remove wire:target="guardarNuevoOperario">Guardar operario</span>
                                <span wire:loading wire:target="guardarNuevoOperario">Guardando…</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
