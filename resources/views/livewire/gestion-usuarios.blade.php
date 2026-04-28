<div class="min-h-full bg-gray-50">

    {{-- ════════════════════════════════════════════════════
         VISTA: LISTA DE USUARIOS
         ════════════════════════════════════════════════════ --}}
    @if($vista === 'lista')
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Gestión de Usuarios</h1>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('usuarios.roles') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Gestión de Roles
                    </a>
                    <button wire:click="abrirCrear"
                            type="button"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-lg cursor-pointer transition"
                            style="background: linear-gradient(to right, #2563eb, #1d4ed8);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        CREAR USUARIO
                    </button>
                </div>
            </div>

            {{-- Alerta --}}
            @if($mensaje)
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                     x-transition class="mb-6 p-4 rounded-lg flex items-center gap-3 border cursor-pointer
                     {{ $tipoMensaje === 'success' ? 'bg-green-50 text-green-800 border-green-200' : 'bg-red-50 text-red-800 border-red-200' }}"
                     wire:click="limpiarMensaje">
                    @if($tipoMensaje === 'success')
                        <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @else
                        <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @endif
                    <span class="text-sm font-medium">{{ $mensaje }}</span>
                </div>
            @endif

            {{-- Buscador --}}
            <div class="mb-6">
                <div class="relative max-w-md">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" wire:model.live.debounce.300ms="buscar"
                           class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
                           placeholder="Buscar por nombre, usuario o email...">
                </div>
            </div>

            {{-- Tabla --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                @if($this->usuarios->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Usuario</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre Completo</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rol</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha Registro</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($this->usuarios as $usuario)
                                    <tr wire:key="usuario-{{ $usuario->id }}" class="hover:bg-gray-50/50 transition-colors">
                                        {{-- Avatar + Username --}}
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                @php
                                                    $iniciales = collect(explode(' ', $usuario->name))->take(2)->map(fn($p) => strtoupper(substr($p, 0, 1)))->join('');
                                                    $colorAvatar = ($usuario->rol->nombre ?? '') === 'ADMINISTRADOR'
                                                        ? 'background: linear-gradient(135deg, #6366f1, #4f46e5);'
                                                        : 'background: linear-gradient(135deg, #93c5fd, #60a5fa);';
                                                @endphp
                                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold shrink-0"
                                                     style="{{ $colorAvatar }}">
                                                    {{ $iniciales }}
                                                </div>
                                                <span class="text-sm text-gray-700 font-medium">{{ $usuario->username }}</span>
                                            </div>
                                        </td>

                                        {{-- Nombre completo --}}
                                        <td class="px-6 py-4">
                                            <span class="text-sm text-gray-900 font-medium">{{ $usuario->name }}</span>
                                        </td>

                                        {{-- Rol --}}
                                        <td class="px-6 py-4">
                                            @php
                                                $rolNombre = $usuario->rol->nombre ?? 'Sin rol';
                                                $rolEstilo = match($rolNombre) {
                                                    'ADMINISTRADOR' => 'background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a;',
                                                    'OPERACIONES'   => 'background-color: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe;',
                                                    'CALIDAD'       => 'background-color: #ede9fe; color: #5b21b6; border: 1px solid #ddd6fe;',
                                                    'GERENCIA'      => 'background-color: #fce7f3; color: #9d174d; border: 1px solid #fbcfe8;',
                                                    default         => 'background-color: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb;',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold" style="{{ $rolEstilo }}">
                                                {{ $rolNombre }}
                                            </span>
                                        </td>

                                        {{-- Estado --}}
                                        <td class="px-6 py-4 text-center">
                                            @if($usuario->activo)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold"
                                                      style="background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0;">
                                                    Activo
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold"
                                                      style="background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca;">
                                                    Inactivo
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Fecha registro --}}
                                        <td class="px-6 py-4 text-center">
                                            <span class="text-sm text-gray-500">{{ $usuario->created_at ? $usuario->created_at->format('d/m/Y') : '—' }}</span>
                                        </td>

                                        {{-- Acciones --}}
                                        <td class="px-6 py-4">
                                            <div class="flex items-center justify-center gap-2">
                                                {{-- Editar --}}
                                                <button wire:click="abrirEditar({{ $usuario->id }})"
                                                        title="Editar usuario"
                                                        class="p-2 rounded-lg text-blue-600 hover:bg-blue-50 transition cursor-pointer">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                                @if($usuario->id !== auth()->id())
                                                    {{-- Desactivar/Activar --}}
                                                    <button wire:click="toggleActivo({{ $usuario->id }})"
                                                            wire:confirm="{{ $usuario->activo ? '¿Desactivar a ' . $usuario->name . '?' : '¿Activar a ' . $usuario->name . '?' }}"
                                                            title="{{ $usuario->activo ? 'Desactivar' : 'Activar' }}"
                                                            class="p-2 rounded-lg transition cursor-pointer {{ $usuario->activo ? 'text-red-500 hover:bg-red-50' : 'text-green-500 hover:bg-green-50' }}">
                                                        @if($usuario->activo)
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                      d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                            </svg>
                                                        @else
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                        @endif
                                                    </button>
                                                    {{-- Eliminar --}}
                                                    <button wire:click="eliminar({{ $usuario->id }})"
                                                            wire:confirm="¿Eliminar permanentemente a {{ $usuario->name }}? Esta acción no se puede deshacer."
                                                            title="Eliminar usuario"
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
                    <div class="py-16 text-center">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <p class="text-gray-400 font-medium">No se encontraron usuarios</p>
                        @if($buscar)
                            <p class="text-gray-400 text-sm mt-1">Intenta con otra búsqueda</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

    {{-- ════════════════════════════════════════════════════
         VISTA: CREAR USUARIO
         ════════════════════════════════════════════════════ --}}
    @elseif($vista === 'crear')
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Crear Usuario</h1>
                <button wire:click="volver"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-gray-800 hover:bg-gray-900 rounded-lg transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    VOLVER
                </button>
            </div>

            {{-- Alerta --}}
            @if($mensaje)
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition
                     class="mb-6 p-4 rounded-lg flex items-center gap-3 border {{ $tipoMensaje === 'success' ? 'bg-green-50 text-green-800 border-green-200' : 'bg-red-50 text-red-800 border-red-200' }}"
                     wire:click="limpiarMensaje">
                    <span class="text-sm font-medium">{{ $mensaje }}</span>
                </div>
            @endif

            {{-- Formulario --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <form wire:submit="guardar" class="p-8 space-y-6">

                    {{-- Errores --}}
                    @if($errors->any())
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)
                                    <li class="text-red-600 text-sm">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Nombre --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.live.debounce.400ms="nombre"
                               class="w-full px-4 py-3 border {{ $errors->has('nombre') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                               placeholder="Ej: Juan">
                        @error('nombre') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Apellido --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Apellido <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.live.debounce.400ms="apellido"
                               class="w-full px-4 py-3 border {{ $errors->has('apellido') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                               placeholder="Ej: Mendoza">
                        @error('apellido') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Username auto-generado --}}
                    @if($username)
                        <p class="text-xs -mt-3" style="color: #2563eb;">El sistema generará automáticamente el usuario: <span class="font-semibold">{{ $username }}</span></p>
                    @endif

                    {{-- Rol --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rol <span class="text-red-500">*</span></label>
                        <select wire:model="rol_id"
                                class="w-full px-4 py-3 border {{ $errors->has('rol_id') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Seleccionar rol...</option>
                            @foreach($this->roles as $rol)
                                <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                            @endforeach
                        </select>
                        @error('rol_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        <p class="text-xs text-gray-500 mt-1.5"><span class="font-semibold text-gray-700">Administrador:</span> Acceso total | <span class="font-semibold text-gray-700">Operaciones:</span> Registro de hallazgos | <span class="font-semibold text-gray-700">Calidad:</span> Supervisión | <span class="font-semibold text-gray-700">Gerencia:</span> Dashboards y reportes</p>
                    </div>

                    {{-- Contraseña --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña <span class="text-red-500">*</span></label>
                        <input type="password" wire:model="password"
                               class="w-full px-4 py-3 border {{ $errors->has('password') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                               placeholder="Mínimo 6 caracteres">
                        @error('password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Confirmar Contraseña --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Contraseña <span class="text-red-500">*</span></label>
                        <input type="password" wire:model="password_confirmation"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                               placeholder="Repite la contraseña">
                    </div>

                    {{-- Botones --}}
                    <div class="flex items-center justify-center gap-4 pt-4 border-t border-gray-100">
                        <button type="button" wire:click="volver"
                                class="px-6 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                            CANCELAR
                        </button>
                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:target="guardar"
                                class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white rounded-lg transition cursor-pointer disabled:opacity-50"
                                style="background: linear-gradient(to right, #2563eb, #1d4ed8);">
                            <span wire:loading.remove wire:target="guardar">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                CREAR USUARIO
                            </span>
                            <span wire:loading wire:target="guardar">Creando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    {{-- ════════════════════════════════════════════════════
         VISTA: EDITAR USUARIO
         ════════════════════════════════════════════════════ --}}
    @elseif($vista === 'editar')
        @php $uEditando = $this->usuarioEditando; @endphp

        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Editar Usuario</h1>
                <button wire:click="volver"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-gray-800 hover:bg-gray-900 rounded-lg transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    VOLVER
                </button>
            </div>

            {{-- Alerta --}}
            @if($mensaje)
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition
                     class="mb-6 p-4 rounded-lg flex items-center gap-3 border {{ $tipoMensaje === 'success' ? 'bg-green-50 text-green-800 border-green-200' : 'bg-red-50 text-red-800 border-red-200' }}"
                     wire:click="limpiarMensaje">
                    <span class="text-sm font-medium">{{ $mensaje }}</span>
                </div>
            @endif

            @if($uEditando)
                {{-- ─── Tarjeta de info del usuario ─── --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex items-center gap-4">
                        @php
                            $iniciales = collect(explode(' ', $uEditando->name))->take(2)->map(fn($p) => strtoupper(substr($p, 0, 1)))->join('');
                            $colorAvatar = ($uEditando->rol->nombre ?? '') === 'ADMINISTRADOR'
                                ? 'background: linear-gradient(135deg, #6366f1, #4f46e5);'
                                : 'background: linear-gradient(135deg, #93c5fd, #60a5fa);';
                        @endphp
                        <div class="w-14 h-14 rounded-full flex items-center justify-center text-white text-lg font-bold shrink-0"
                             style="{{ $colorAvatar }}">
                            {{ $iniciales }}
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">{{ $uEditando->name }}</h2>
                            <p class="text-sm text-gray-500">Usuario: <span class="font-medium text-gray-700">{{ $uEditando->username }}</span></p>
                            <p class="text-xs text-gray-400">Miembro desde {{ $uEditando->created_at ? $uEditando->created_at->format('d/m/Y') : '—' }}</p>
                        </div>
                    </div>
                </div>

                {{-- ─── Formulario de edición ─── --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                    <form wire:submit="guardar" class="p-8 space-y-6">

                        @if($errors->any())
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li class="text-red-600 text-sm">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Nombre --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.live.debounce.400ms="nombre"
                                   class="w-full px-4 py-3 border {{ $errors->has('nombre') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                   placeholder="Nombre">
                            @error('nombre') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Apellido --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Apellido <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.live.debounce.400ms="apellido"
                                   class="w-full px-4 py-3 border {{ $errors->has('apellido') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                   placeholder="Apellido">
                            @error('apellido') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Username preview --}}
                        <p class="text-xs text-blue-600 -mt-3">Nuevo usuario: <span class="font-semibold">{{ $username }}</span></p>

                        {{-- Rol --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rol <span class="text-red-500">*</span></label>
                            <select wire:model="rol_id"
                                    class="w-full px-4 py-3 border {{ $errors->has('rol_id') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <option value="">Seleccionar rol...</option>
                                @foreach($this->roles as $rol)
                                    <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                                @endforeach
                            </select>
                            @error('rol_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Botones --}}
                        <div class="flex items-center justify-center gap-4 pt-4 border-t border-gray-100">
                            <button type="button" wire:click="volver"
                                    class="px-6 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                                CANCELAR
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    wire:target="guardar"
                                    class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white rounded-lg transition cursor-pointer disabled:opacity-50"
                                    style="background: linear-gradient(to right, #2563eb, #1d4ed8);">
                                <span wire:loading.remove wire:target="guardar">
                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    GUARDAR CAMBIOS
                                </span>
                                <span wire:loading wire:target="guardar">Guardando...</span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ─── Sección: Restablecer Contraseña ─── --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-8">
                        <div class="flex items-center gap-3 mb-2">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                            <h3 class="text-lg font-bold text-gray-900">Restablecer Contraseña</h3>
                        </div>
                        <p class="text-sm text-blue-600 mb-6">Cambia la contraseña del usuario. El usuario deberá usar la nueva contraseña en su próximo inicio de sesión.</p>

                        <form wire:submit="guardarContrasena" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nueva Contraseña <span class="text-red-500">*</span></label>
                                <input type="password" wire:model="nuevaContrasena"
                                       class="w-full px-4 py-3 border {{ $errors->has('nuevaContrasena') ? 'border-red-400' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                       placeholder="Mínimo 6 caracteres">
                                @error('nuevaContrasena') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    wire:target="guardarContrasena"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-amber-500 hover:bg-amber-600 rounded-lg transition cursor-pointer disabled:opacity-50">
                                <span wire:loading.remove wire:target="guardarContrasena">
                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                    ACTUALIZAR CONTRASEÑA
                                </span>
                                <span wire:loading wire:target="guardarContrasena">Actualizando...</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
