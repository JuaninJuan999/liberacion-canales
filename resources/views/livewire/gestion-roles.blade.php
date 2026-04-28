<div class="min-h-full bg-gray-50">

    @if($vista === 'lista')
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Gestión de Roles</h1>
                    <p class="text-sm text-gray-600 mt-1">
                        Define roles y marca qué <strong>módulos</strong> aparecen en la pantalla de bienvenida para cada uno (tarjetas “Módulos disponibles”).
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('usuarios.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Volver a usuarios
                    </a>
                    <button type="button" wire:click="abrirCrear"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-lg cursor-pointer transition"
                            style="background: linear-gradient(to right, #2563eb, #1d4ed8);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        CREAR ROL
                    </button>
                </div>
            </div>

            @if($mensaje)
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                     x-transition class="mb-6 p-4 rounded-lg flex items-center gap-3 border cursor-pointer
                     {{ $tipoMensaje === 'success' ? 'bg-green-50 text-green-800 border-green-200' : 'bg-red-50 text-red-800 border-red-200' }}"
                     wire:click="limpiarMensaje">
                    <span class="text-sm font-medium">{{ $mensaje }}</span>
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                @if($this->rolesList->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50/80">
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rol</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Usuarios</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($this->rolesList as $rol)
                                    <tr wire:key="rol-{{ $rol->id }}" class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <span class="text-sm font-semibold text-gray-900">{{ $rol->nombre }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200">
                                                {{ $rol->users_count }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center justify-center gap-2">
                                                <button type="button" wire:click="abrirEditar({{ $rol->id }})"
                                                        title="Editar rol y módulos"
                                                        class="p-2 rounded-lg text-blue-600 hover:bg-blue-50 transition cursor-pointer">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                                @if(\App\Models\Rol::normalizarNombre($rol->nombre) !== 'ADMINISTRADOR' && $rol->users_count === 0)
                                                    <button type="button" wire:click="eliminar({{ $rol->id }})"
                                                            wire:confirm="¿Eliminar el rol {{ $rol->nombre }}? No debe tener usuarios asignados."
                                                            title="Eliminar rol"
                                                            class="p-2 rounded-lg text-red-600 hover:bg-red-50 transition cursor-pointer">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-16 text-center text-gray-500">
                        No hay roles registrados.
                    </div>
                @endif
            </div>

            <p class="mt-6 text-xs text-gray-500 max-w-3xl">
                Los cambios se aplican a las tarjetas de la página de inicio y al menú lateral: ambos leen los mismos registros de <span class="font-mono text-gray-600">menu_modulos</span> según el rol.
                Algunas pantallas pueden tener comprobaciones extra en código (por ejemplo rutas específicas).
            </p>
        </div>

    @elseif(in_array($vista, ['crear', 'editar'], true))
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="flex items-center justify-between mb-8">
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $vista === 'crear' ? 'Crear rol' : 'Editar rol' }}
                </h1>
                <button type="button" wire:click="volver"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-gray-800 hover:bg-gray-900 rounded-lg transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    VOLVER
                </button>
            </div>

            @if($mensaje)
                <div class="mb-6 p-4 rounded-lg border {{ $tipoMensaje === 'success' ? 'bg-green-50 text-green-800 border-green-200' : 'bg-red-50 text-red-800 border-red-200' }}"
                     wire:click="limpiarMensaje">
                    <span class="text-sm font-medium">{{ $mensaje }}</span>
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <form wire:submit="guardar" class="p-8 space-y-8">

                    <div>
                        <label for="nombre-rol" class="block text-sm font-medium text-gray-700 mb-1">Nombre del rol <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.live="nombre" id="nombre-rol"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase"
                               placeholder="Ej: AUDITORIA"
                               maxlength="255">
                        @error('nombre') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        <p class="text-xs text-gray-500 mt-1">Se guardará en mayúsculas. Debe ser único.</p>
                    </div>

                    <div>
                        <p class="text-sm font-semibold text-gray-900 mb-3">Módulos visibles en la pantalla de bienvenida</p>
                        <p class="text-xs text-gray-600 mb-4">Marca los accesos que este rol puede ver como tarjetas al entrar al sistema.</p>

                        <div class="rounded-lg border border-gray-200 divide-y divide-gray-100 max-h-96 overflow-y-auto">
                            @foreach($this->modulosMenu as $modulo)
                                <label wire:key="mod-check-{{ $modulo->id }}"
                                       class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 cursor-pointer">
                                    <input type="checkbox"
                                           wire:model.live="modulosSeleccionados"
                                           value="{{ $modulo->id }}"
                                           class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="flex-1 min-w-0">
                                        <span class="block text-sm font-medium text-gray-900">{{ $modulo->nombre }}</span>
                                        @if($modulo->vista)
                                            <span class="block text-xs text-gray-500 font-mono truncate">{{ $modulo->vista }}</span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('modulosSeleccionados.*') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-200">
                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:target="guardar"
                                class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white rounded-lg transition disabled:opacity-50"
                                style="background: linear-gradient(to right, #2563eb, #1d4ed8);">
                            <span wire:loading.remove wire:target="guardar">{{ $vista === 'crear' ? 'Crear rol' : 'Guardar cambios' }}</span>
                            <span wire:loading wire:target="guardar">Guardando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
