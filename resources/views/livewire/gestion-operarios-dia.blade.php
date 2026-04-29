<div class="gestion-operarios-dia pb-[env(safe-area-inset-bottom)]">
    <div class="space-y-5 sm:space-y-8">
        {{-- Mensajes Flash --}}
        @if (session()->has('success'))
            <div class="flex gap-3 rounded-xl border border-emerald-200/80 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 shadow-sm">
                <svg class="h-5 w-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="leading-snug">{{ session('success') }}</span>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="flex gap-3 rounded-xl border border-red-200/80 bg-red-50 px-4 py-3 text-sm text-red-900 shadow-sm">
                <svg class="h-5 w-5 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="leading-snug">{{ session('error') }}</span>
            </div>
        @endif

        @if (session()->has('info'))
            <div class="flex gap-3 rounded-xl border border-blue-200/80 bg-blue-50 px-4 py-3 text-sm text-blue-900 shadow-sm">
                <svg class="h-5 w-5 shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="leading-snug">{{ session('info') }}</span>
            </div>
        @endif

        {{-- Barra superior: fecha y acciones --}}
        <section class="overflow-hidden rounded-xl border border-gray-200/80 bg-white shadow-md shadow-gray-900/5 ring-1 ring-gray-900/[0.03] sm:rounded-2xl">
            <div class="border-b border-gray-100 bg-gradient-to-r from-slate-50/90 to-white px-4 py-3 sm:px-6 sm:py-4">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600/90">Planificación diaria</p>
                        <h2 class="text-base font-semibold text-gray-900 sm:text-lg">Fecha de operación</h2>
                    </div>
                </div>
            </div>
            <div class="p-4 sm:p-6">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between lg:gap-6">
                    <div class="w-full max-w-full sm:max-w-xs">
                        <label class="mb-2 block text-sm font-medium text-gray-700" for="fecha-operacion-input">
                            Seleccionar día
                        </label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <input id="fecha-operacion-input" type="date" wire:model.live="fecha_operacion"
                                   class="min-h-[44px] w-full rounded-xl border-gray-300 py-2.5 pl-10 pr-3 text-base shadow-sm transition focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:justify-end">
                        <button type="button" wire:click="copiarDiaAnterior"
                                class="inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 active:bg-violet-800 sm:w-auto sm:justify-start">
                            <svg class="h-4 w-4 shrink-0 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                            Copiar día anterior
                        </button>
                        <button type="button" wire:click="limpiarAsignaciones"
                                class="inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 active:bg-gray-100 sm:w-auto sm:justify-start">
                            <svg class="h-4 w-4 shrink-0 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Limpiar
                        </button>
                        <button type="button" wire:click="guardarAsignaciones"
                                class="inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-900/15 transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-800 sm:w-auto sm:justify-start">
                            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l3 3m0 0l-3 3m3-3H12"/></svg>
                            Guardar asignaciones
                        </button>
                        <button type="button" wire:click="refrescarListaOperarios"
                                class="inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50/80 px-4 py-2.5 text-sm font-medium text-indigo-900 transition hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 active:bg-indigo-100 sm:w-auto sm:justify-start"
                                title="Recarga el catálogo desde base de datos sin borrar las asignaciones actuales">
                            <svg class="h-4 w-4 shrink-0 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Actualizar lista
                        </button>
                    </div>
                </div>
            </div>
        </section>

        {{-- Tabla de asignaciones --}}
        <section class="overflow-hidden rounded-xl border border-gray-200/80 bg-white shadow-md shadow-gray-900/5 ring-1 ring-gray-900/[0.03] sm:rounded-2xl">
            <div class="border-b border-gray-100 bg-gradient-to-r from-indigo-50/50 to-white px-4 py-4 sm:px-6 sm:py-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex gap-3">
                        <span class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-base font-semibold tracking-tight text-gray-900 sm:text-lg">Asignación de operarios a puestos</h3>
                            <p class="mt-1 max-w-3xl text-sm leading-relaxed text-gray-600">
                                Cada operario solo puede estar en <strong class="font-semibold text-gray-800">un puesto</strong> a la vez; al elegirlo aquí, deja de mostrarse en los demás hasta que lo quite de ese puesto.
                            </p>
                        </div>
                    </div>
                </div>
                @if($puestos->count() > 0)
                    <div class="mt-4 rounded-xl border border-indigo-100 bg-indigo-50/60 px-3 py-2.5 text-sm leading-snug text-indigo-950 sm:mt-5 sm:px-4 sm:py-3">
                        <span class="font-medium text-indigo-900">Consejo:</span>
                        abra el selector, escriba para filtrar y use «Crear nuevo operario» si necesita dar de alta a alguien al momento.
                    </div>
                @endif
            </div>

            <div class="p-4 pt-4 sm:p-6 sm:pt-5">
                @if($puestos->count() > 0)
                    @if($operariosDisponibles->count() === 0)
                        <div class="mb-5 flex gap-3 rounded-xl border border-amber-200/90 bg-amber-50 px-3 py-3 text-sm leading-snug text-amber-950 sm:mb-6 sm:px-4">
                            <svg class="h-5 w-5 shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>No hay operarios activos en el catálogo. Use «Crear nuevo operario» en el selector o cree uno desde el menú «Gestión de operarios» y pulse «Actualizar lista» si ya existía fuera de esta pantalla.</span>
                        </div>
                    @endif

                    <p class="mb-2 flex items-center gap-2 text-xs text-gray-500 md:hidden">
                        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/></svg>
                        Deslice para ver puesto y operario si no caben en pantalla.
                    </p>
                    <div class="-mx-4 overflow-x-auto overscroll-x-contain rounded-xl border border-gray-200/90 px-4 pb-1 [-webkit-overflow-scrolling:touch] sm:mx-0 sm:overflow-visible sm:rounded-none sm:border-0 sm:px-0 sm:pb-0">
                        <table class="w-full min-w-[560px] divide-y divide-gray-200 sm:min-w-full">
                            <thead class="hidden bg-gradient-to-r from-slate-100/90 to-gray-50/95 md:table-header-group">
                                <tr>
                                    <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                        Puesto de trabajo
                                    </th>
                                    <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                        Operario asignado
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach($puestos as $puesto)
                                    @php
                                        $opcionesCombo = $this->operariosOpcionesPara($puesto->id)
                                            ->map(fn ($o) => ['id' => (string) $o->id, 'nombre' => $o->nombre])
                                            ->values()
                                            ->all();
                                        $comboVersion = md5(json_encode($opcionesCombo));
                                    @endphp
                                    <tr class="transition-colors hover:bg-slate-50/80" wire:key="puesto-row-{{ $puesto->id }}">
                                        <td class="whitespace-nowrap px-4 py-3 align-top sm:px-5 sm:py-4">
                                            <span class="text-sm font-semibold text-slate-800">{{ $puesto->nombre }}</span>
                                        </td>
                                        <td class="min-w-[12rem] px-4 py-3 align-top text-sm text-gray-900 sm:min-w-[14rem] sm:px-5 sm:py-4">
                                            <div
                                                wire:key="combo-{{ $puesto->id }}-{{ $comboVersion }}"
                                                class="relative w-full max-w-none sm:max-w-md"
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
                                                        const vw = window.innerWidth || document.documentElement.clientWidth;
                                                        const pad = 12;
                                                        let width = Math.max(r.width, 220);
                                                        let left = r.left;
                                                        const top = r.bottom + 6;
                                                        if (vw < 640) {
                                                            width = vw - pad * 2;
                                                            left = pad;
                                                        } else {
                                                            width = Math.max(r.width, 260);
                                                            left = r.left;
                                                            if (left + width > vw - pad) left = Math.max(pad, vw - width - pad);
                                                            if (left < pad) left = pad;
                                                        }
                                                        this.top = top;
                                                        this.left = left;
                                                        this.width = width;
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
                                                            return 'Sin asignar';
                                                        }
                                                        const o = this.opts.find(x => String(x.id) === String(id));
                                                        return o ? o.nombre : 'Sin asignar';
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
                                                    class="flex min-h-[48px] w-full items-center justify-between gap-2 rounded-xl border border-gray-200 bg-white px-3.5 py-3 text-left text-base shadow-sm ring-gray-900/5 transition hover:border-indigo-200 hover:bg-indigo-50/40 hover:shadow active:bg-gray-50 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 sm:min-h-0 sm:py-2.5 sm:text-sm"
                                                >
                                                    <span class="truncate text-gray-900" x-text="label()"></span>
                                                    <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </button>

                                                <template x-teleport="body">
                                                    <div
                                                        x-show="open"
                                                        x-cloak
                                                        x-transition.opacity
                                                        class="fixed inset-0 z-[9998] bg-slate-900/25 backdrop-blur-[2px] pb-[env(safe-area-inset-bottom)]"
                                                        style="display: none;"
                                                        @click="cerrarPanel()"
                                                    ></div>
                                                </template>
                                                <template x-teleport="body">
                                                    <div
                                                        x-show="open"
                                                        x-cloak
                                                        x-transition
                                                        class="fixed z-[9999] max-h-[85vh] overflow-hidden rounded-2xl border border-gray-200/90 bg-white shadow-2xl shadow-gray-900/15 ring-1 ring-black/5 sm:max-h-none"
                                                        style="display: none;"
                                                        :style="{ top: top + 'px', left: left + 'px', width: width + 'px' }"
                                                        @click.stop
                                                    >
                                                        <div class="border-b border-gray-100 bg-gray-50/80 px-3 py-2">
                                                            <label class="sr-only">Filtrar operarios</label>
                                                            <div class="relative">
                                                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-gray-400">
                                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                                </span>
                                                                <input
                                                                    type="search"
                                                                    x-ref="qInput"
                                                                    x-model="query"
                                                                    placeholder="Filtrar por nombre…"
                                                                    class="min-h-[44px] w-full rounded-lg border border-gray-200 bg-white py-2 pl-9 pr-3 text-base placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/25 sm:min-h-0 sm:text-sm"
                                                                    autocomplete="off"
                                                                    @keydown.escape="cerrarPanel()"
                                                                >
                                                            </div>
                                                        </div>
                                                        <div class="border-b border-gray-100 py-1">
                                                            <p class="px-3 pb-1 pt-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Acciones</p>
                                                            <button
                                                                type="button"
                                                                class="flex min-h-[48px] w-full items-center gap-2 px-3 py-3 text-left text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50 active:bg-indigo-100 sm:min-h-0 sm:py-2.5"
                                                                @click="abrirCrear()"
                                                            >
                                                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700">
                                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                                </span>
                                                                Crear nuevo operario
                                                            </button>
                                                            <button
                                                                type="button"
                                                                class="min-h-[44px] w-full px-3 py-3 text-left text-sm text-gray-600 transition hover:bg-gray-50 active:bg-gray-100 sm:min-h-0 sm:py-2"
                                                                @click="selectOperario('')"
                                                            >
                                                                Sin asignar
                                                            </button>
                                                        </div>
                                                        <div class="max-h-[min(45vh,16rem)] overflow-y-auto overscroll-contain py-1 sm:max-h-60">
                                                            <p class="px-3 pb-1 pt-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Operarios</p>
                                                            <template x-for="op in filtered" :key="op.id">
                                                                <button
                                                                    type="button"
                                                                    class="min-h-[44px] w-full px-3 py-3 text-left text-sm text-gray-800 transition hover:bg-indigo-50 hover:text-indigo-950 active:bg-indigo-100 sm:min-h-0 sm:py-2"
                                                                    x-text="op.nombre"
                                                                    @click="selectOperario(op.id)"
                                                                ></button>
                                                            </template>
                                                            <div
                                                                class="px-3 py-3 text-center text-xs text-gray-500"
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
                    <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50/80 px-6 py-14 text-center">
                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <p class="font-medium text-gray-900">No hay puestos de trabajo registrados</p>
                        <p class="mt-2 text-sm text-gray-600">Configure primero los puestos en el módulo correspondiente para poder asignar operarios.</p>
                    </div>
                @endif
            </div>
        </section>

        {{-- Resumen --}}
        @if($puestos->count() > 0)
            <section class="rounded-xl border border-indigo-100 bg-gradient-to-br from-indigo-50 via-white to-blue-50/80 p-4 shadow-md shadow-indigo-900/[0.06] ring-1 ring-indigo-900/[0.04] sm:rounded-2xl sm:p-6">
                <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="flex items-center gap-3 rounded-xl bg-white/90 px-4 py-3 shadow-sm ring-1 ring-gray-200/80">
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"/></svg>
                            </span>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total puestos</p>
                                <p class="text-xl font-semibold tabular-nums text-gray-900">{{ $puestos->count() }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-xl bg-white/90 px-4 py-3 shadow-sm ring-1 ring-gray-200/80">
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Asignados hoy</p>
                                <p class="text-xl font-semibold tabular-nums text-gray-900">{{ collect($asignaciones)->filter()->count() }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl bg-indigo-600/95 px-4 py-4 text-white shadow-lg shadow-indigo-900/20 sm:px-5">
                        <p class="text-xs font-medium uppercase tracking-wider text-indigo-200">Fecha de operación</p>
                        <p class="mt-1 text-xl font-semibold tabular-nums tracking-tight sm:text-2xl">{{ \Carbon\Carbon::parse($fecha_operacion)->format('d/m/Y') }}</p>
                    </div>
                </div>
            </section>
        @endif

        {{-- Modal: nuevo operario --}}
        @if ($modalNuevoOperario)
            <div
                class="fixed inset-0 z-[10050] flex items-end justify-center p-0 sm:items-center sm:p-4"
                role="dialog"
                aria-modal="true"
                aria-labelledby="titulo-modal-nuevo-operario"
                x-data
                x-on:keydown.escape.window="$wire.cerrarModalNuevoOperario()"
            >
                <button
                    type="button"
                    class="absolute inset-0 cursor-default bg-slate-900/50 backdrop-blur-sm transition-opacity pb-[env(safe-area-inset-bottom)]"
                    wire:click="cerrarModalNuevoOperario"
                    aria-label="Cerrar"
                ></button>

                <div
                    class="relative z-10 flex max-h-[min(92dvh,calc(100dvh-env(safe-area-inset-bottom)))] w-full max-w-lg flex-col overflow-hidden rounded-t-[1.25rem] bg-white shadow-2xl shadow-gray-900/25 ring-1 ring-gray-200 sm:max-h-[min(92vh,880px)] sm:rounded-2xl"
                    wire:click.stop
                >
                    <div class="relative shrink-0 overflow-hidden bg-gradient-to-br from-indigo-600 via-indigo-600 to-blue-700 px-5 py-5 text-white sm:px-6 sm:py-6">
                        <div class="pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full bg-white/10 blur-2xl"></div>
                        <div class="relative flex gap-4">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/25">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                            </span>
                            <div>
                                <h4 id="titulo-modal-nuevo-operario" class="text-lg font-semibold tracking-tight sm:text-xl">
                                    Nuevo operario
                                </h4>
                                <p class="mt-1 text-sm leading-relaxed text-indigo-100">
                                    Se guardará en el catálogo igual que desde «Gestión de operarios». Si lo deja activo, puede quedar asignado a este puesto al guardar.
                                </p>
                            </div>
                        </div>
                    </div>

                    <form wire:submit.prevent="guardarNuevoOperario" class="flex min-h-0 flex-1 flex-col overflow-y-auto overscroll-contain bg-gray-50/40 px-5 py-5 sm:px-6 sm:py-6">
                        <div class="space-y-5 pb-[env(safe-area-inset-bottom)] sm:pb-0">
                        <div>
                            <label for="modal-op-nombre" class="mb-1.5 block text-sm font-medium text-gray-700">
                                Nombre completo *
                            </label>
                            <input
                                id="modal-op-nombre"
                                type="text"
                                wire:model.blur="nuevo_operario_nombre"
                                class="min-h-[44px] w-full rounded-xl border-gray-300 bg-white px-4 py-2.5 text-base shadow-sm transition focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                autocomplete="name"
                                autofocus
                            >
                            @error('nuevo_operario_nombre')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="modal-op-documento" class="mb-1.5 block text-sm font-medium text-gray-700">
                                Documento de identidad
                            </label>
                            <input
                                id="modal-op-documento"
                                type="text"
                                wire:model.blur="nuevo_operario_documento"
                                class="min-h-[44px] w-full rounded-xl border-gray-300 bg-white px-4 py-2.5 text-base shadow-sm transition focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="Opcional"
                                autocomplete="off"
                            >
                            @error('nuevo_operario_documento')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
                            <label class="flex cursor-pointer items-center gap-3">
                                <input
                                    type="checkbox"
                                    wire:model="nuevo_operario_activo"
                                    class="h-5 w-5 shrink-0 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                >
                                <span class="text-sm font-medium text-gray-800">Operario activo</span>
                            </label>
                            @error('nuevo_operario_activo')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-auto flex flex-col-reverse gap-2 border-t border-gray-200/80 pt-5 sm:flex-row sm:justify-end sm:gap-3">
                            <button
                                type="button"
                                wire:click="cerrarModalNuevoOperario"
                                class="inline-flex min-h-[48px] justify-center rounded-xl border border-gray-300 bg-white px-5 py-3 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 active:bg-gray-100 sm:min-h-0 sm:py-2.5"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                class="inline-flex min-h-[48px] justify-center rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-md shadow-indigo-900/20 transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-800 disabled:opacity-60 sm:min-h-0 sm:py-2.5"
                            >
                                <span wire:loading.remove wire:target="guardarNuevoOperario">Guardar operario</span>
                                <span wire:loading wire:target="guardarNuevoOperario">Guardando…</span>
                            </button>
                        </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
