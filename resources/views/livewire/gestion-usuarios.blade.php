<div class="min-h-full bg-gradient-to-br from-slate-50 to-blue-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-8 flex items-center gap-4">
            <div class="w-14 h-14 bg-gradient-to-br from-purple-600 to-purple-800 rounded-2xl flex items-center justify-center shadow-lg">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Gestion de Usuarios</h1>
                <p class="text-gray-500 text-sm mt-0.5">Administra los usuarios, roles, permisos y contrasenas del sistema</p>
            </div>
        </div>

        {{-- Alerta de mensaje --}}
        @if($mensaje)
            <div x-data="{ show: true }"
                 x-show="show"
                 x-init="setTimeout(() => show = false, 5000)"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-end="opacity-0"
                 class="mb-6 p-4 rounded-xl flex items-start gap-3 border cursor-pointer {{ $tipoMensaje === 'success' ? 'bg-green-50 text-green-800 border-green-300' : 'bg-red-50 text-red-800 border-red-300' }}"
                 wire:click="limpiarMensaje" role="alert">
                <div class="shrink-0 mt-0.5">
                    @if($tipoMensaje === 'success')
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @endif
                </div>
                <p class="font-medium text-sm">{{ $mensaje }}</p>
            </div>
        @endif

        {{-- Modal: Contrasena temporal --}}
        @if($mostrarPasswordModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
                    <div class="bg-gradient-to-r from-amber-500 to-amber-600 px-6 py-4 flex items-center gap-3">
                        <svg class="w-6 h-6 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        <h3 class="text-white font-bold text-lg">Contrasena Restablecida</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600 text-sm mb-1">Usuario: <span class="font-semibold text-gray-800">{{ $usuarioPasswordNombre }}</span></p>
                        <p class="text-gray-600 text-sm mb-4">La nueva contrasena temporal es:</p>
                        <div x-data class="bg-amber-50 border-2 border-amber-300 rounded-xl p-4 flex items-center justify-between gap-3">
                            <code class="text-2xl font-bold text-amber-800 tracking-widest select-all">{{ $passwordTemporal }}</code>
                            <button @click="navigator.clipboard.writeText('{{ $passwordTemporal }}'); $el.textContent = 'Copiado!'"
                                    class="shrink-0 px-3 py-1.5 rounded-lg bg-amber-200 hover:bg-amber-300 text-amber-900 text-xs font-bold transition">
                                Copiar
                            </button>
                        </div>
                        <p class="text-xs text-gray-400 mt-3">Copia esta contrasena ahora. No podras verla nuevamente.</p>
                        <button wire:click="cerrarPasswordModal"
                                class="mt-5 w-full bg-gray-800 hover:bg-gray-900 text-white font-semibold py-2.5 rounded-xl transition">
                            Entendido, ya la copie
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Layout principal --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Panel izquierdo --}}
            <div class="lg:col-span-1 space-y-4">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sticky top-4">
                    <h2 class="text-base font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Busqueda y Filtros
                    </h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Buscar</label>
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input type="text" wire:model.live="buscar"
                                       class="w-full pl-9 pr-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                       placeholder="Nombre, email o usuario...">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Filtrar por Rol</label>
                            <select wire:model.live="filtro_rol"
                                    class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                <option value="">Todos los roles</option>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @if($modo === 'vista')
                        <button wire:click="mostrarFormularioCrear"
                                class="mt-5 w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-xl shadow-sm hover:shadow-md transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Crear Usuario
                        </button>
                    @endif
                </div>

                {{-- Estadisticas --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
                        <div class="text-2xl font-bold text-green-600">{{ $usuarios->where('activo', true)->count() }}</div>
                        <div class="text-xs text-gray-500 font-medium mt-0.5">Activos</div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
                        <div class="text-2xl font-bold text-gray-400">{{ $usuarios->where('activo', false)->count() }}</div>
                        <div class="text-xs text-gray-500 font-medium mt-0.5">Inactivos</div>
                    </div>
                </div>
            </div>

            {{-- Panel derecho --}}
            <div class="lg:col-span-2 space-y-4">

                {{-- Formulario --}}
                @if($modo !== 'vista')
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 {{ $modo === 'crear' ? 'bg-gradient-to-r from-blue-50 to-indigo-50' : 'bg-gradient-to-r from-amber-50 to-orange-50' }}">
                            <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                                @if($modo === 'crear')
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                    </svg>
                                    <span class="text-blue-800">Crear Nuevo Usuario</span>
                                @else
                                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span class="text-amber-800">Editar Usuario</span>
                                @endif
                            </h2>
                        </div>
                        <form wire:submit.prevent="guardar" class="p-6 space-y-4">
                            {{-- Mostrar errores de validación --}}
                            @if($errors->any())
                                <div class="bg-red-50 border border-red-300 rounded-lg p-4 mb-4">
                                    <h3 class="text-red-700 font-semibold text-sm mb-2">Errores en la validacion:</h3>
                                    <ul class="list-disc list-inside space-y-1">
                                        @foreach($errors->all() as $error)
                                            <li class="text-red-600 text-xs">{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Nombre Completo</label>
                                    <input type="text" wire:model.live="nombre"
                                           class="w-full px-4 py-2.5 text-sm border {{ $errors->has('nombre') ? 'border-red-500 bg-red-50' : 'border-gray-200' }} rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                           placeholder="Ej: Juan Perez">
                                    @error('nombre') <span class="text-red-500 text-xs mt-1 block font-semibold">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Email</label>
                                    <input type="email" wire:model="email"
                                           class="w-full px-4 py-2.5 text-sm border {{ $errors->has('email') ? 'border-red-500 bg-red-50' : 'border-gray-200' }} rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                           placeholder="ejemplo@empresa.com">
                                    @error('email') <span class="text-red-500 text-xs mt-1 block font-semibold">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Usuario de Login</label>
                                    <input type="text" wire:model="username"
                                           class="w-full px-4 py-2.5 text-sm border {{ $errors->has('username') ? 'border-red-500 bg-red-50' : 'border-gray-200' }} rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition font-mono"
                                           placeholder="juan.perez">
                                    @error('username') <span class="text-red-500 text-xs mt-1 block font-semibold">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                                        Contrasena
                                        @if($modo === 'editar') <span class="text-gray-400 normal-case font-normal">(dejar vacio para no cambiar)</span> @endif
                                    </label>
                                    <input type="password" wire:model="password"
                                           class="w-full px-4 py-2.5 text-sm border {{ $errors->has('password') ? 'border-red-500 bg-red-50' : 'border-gray-200' }} rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                           placeholder="Minimo 8 caracteres"
                                           @if($modo === 'crear') required @endif>
                                    @error('password') <span class="text-red-500 text-xs mt-1 block font-semibold">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Rol</label>
                                    <select wire:model="rol_id"
                                            class="w-full px-4 py-2.5 text-sm border {{ $errors->has('rol_id') ? 'border-red-500 bg-red-50' : 'border-gray-200' }} rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                        <option value="">Seleccionar rol...</option>
                                        @foreach($roles as $rol)
                                            <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('rol_id') <span class="text-red-500 text-xs mt-1 block font-semibold">{{ $message }}</span> @enderror
                                </div>
                                <div class="sm:col-span-2 flex items-center gap-3 bg-green-50 p-3 rounded-lg border border-green-200">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" wire:model="activo" class="sr-only peer" id="activo">
                                        <div class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-green-600 peer-focus:ring-2 peer-focus:ring-green-300 transition-colors after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-transform peer-checked:after:translate-x-5"></div>
                                    </label>
                                    <span class="text-sm font-medium text-green-800">Usuario activo</span>
                                </div>
                            </div>
                            <div class="flex gap-3 pt-5 mt-2 border-t border-gray-100">
                                <button type="submit"
                                        class="flex-1 flex items-center justify-center gap-2 {{ $modo === 'crear' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-amber-600 hover:bg-amber-700' }} text-white font-semibold py-2.5 rounded-xl shadow-sm transition disabled:opacity-50 disabled:cursor-not-allowed"
                                        wire:loading.attr="disabled"
                                        wire:target="guardar">
                                    @if($modo === 'crear')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Crear Usuario
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Guardar Cambios
                                    @endif
                                </button>
                                <button type="button" wire:click="cancelar"
                                        class="px-6 flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2.5 rounded-xl transition disabled:opacity-50 disabled:cursor-not-allowed"
                                        wire:loading.attr="disabled"
                                        wire:target="cancelar,guardar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- Tabla --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Usuarios
                        </h2>
                        <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2.5 py-1 rounded-full">
                            {{ $usuarios->count() }} {{ $usuarios->count() === 1 ? 'usuario' : 'usuarios' }}
                        </span>
                    </div>

                    @if($usuarios->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-100">
                                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Usuario</th>
                                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden sm:table-cell">Login</th>
                                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Rol</th>
                                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Estado</th>
                                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50" wire:key="usuarios-lista">
                                    @foreach($usuarios as $usuario)
                                        @if($editandoUsuarioId === $usuario->id)
                                            {{-- Fila de edición inline --}}
                                            <tr class="bg-blue-50 border-l-4 border-blue-500">
                                                <td colspan="5" class="px-5 py-4">
                                                    <div class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                                                        {{-- Nombre --}}
                                                        <div>
                                                            <label class="text-xs font-semibold text-gray-600 block mb-1">Nombre</label>
                                                            <input type="text" wire:model="editandoNombre" 
                                                                   class="w-full px-3 py-2 text-sm border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 {{ $errors->has('editandoNombre') ? 'border-red-500 bg-red-50' : '' }}"
                                                                   placeholder="Nombre completo">
                                                            @error('editandoNombre') <span class="text-red-600 text-xs block mt-1">{{ $message }}</span> @enderror
                                                        </div>

                                                        {{-- Usuario --}}
                                                        <div>
                                                            <label class="text-xs font-semibold text-gray-600 block mb-1">Usuario</label>
                                                            <input type="text" wire:model="editandoUsername" 
                                                                   class="w-full px-3 py-2 text-sm border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-mono {{ $errors->has('editandoUsername') ? 'border-red-500 bg-red-50' : '' }}"
                                                                   placeholder="usuario">
                                                            @error('editandoUsername') <span class="text-red-600 text-xs block mt-1">{{ $message }}</span> @enderror
                                                        </div>

                                                        {{-- Rol --}}
                                                        <div>
                                                            <label class="text-xs font-semibold text-gray-600 block mb-1">Rol</label>
                                                            <select wire:model="editandoRolId" 
                                                                    class="w-full px-3 py-2 text-sm border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 {{ $errors->has('editandoRolId') ? 'border-red-500 bg-red-50' : '' }}">
                                                                <option value="">Seleccionar...</option>
                                                                @foreach($roles as $rol)
                                                                    <option value="{{ $rol->id }}" @selected($editandoRolId == $rol->id)>{{ $rol->nombre }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error('editandoRolId') <span class="text-red-600 text-xs block mt-1">{{ $message }}</span> @enderror
                                                        </div>

                                                        {{-- Email --}}
                                                        <div>
                                                            <label class="text-xs font-semibold text-gray-600 block mb-1">Email</label>
                                                            <input type="email" wire:model="editandoEmail" 
                                                                   class="w-full px-3 py-2 text-sm border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 {{ $errors->has('editandoEmail') ? 'border-red-500 bg-red-50' : '' }}"
                                                                   placeholder="email@example.com">
                                                            @error('editandoEmail') <span class="text-red-600 text-xs block mt-1">{{ $message }}</span> @enderror
                                                        </div>

                                                        {{-- Botones --}}
                                                        <div class="flex gap-2">
                                                            <button type="button"
                                                                    wire:click="guardarEdicionInline"
                                                                    wire:loading.attr="disabled"
                                                                    wire:target="guardarEdicionInline"
                                                                    class="flex-1 px-3 py-2 rounded-lg bg-green-500 hover:bg-green-600 text-white text-sm font-semibold transition enabled:cursor-pointer disabled:opacity-60">
                                                                <span wire:loading.remove wire:target="guardarEdicionInline">✓ Guardar</span>
                                                                <span wire:loading wire:target="guardarEdicionInline">Guardando...</span>
                                                            </button>
                                                            <button type="button"
                                                                    wire:click="cancelarEdicionInline"
                                                                    class="flex-1 px-3 py-2 rounded-lg bg-gray-500 hover:bg-gray-600 text-white text-sm font-semibold transition cursor-pointer">
                                                                ✕ Cancelar
                                                            </button>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @else
                                            {{-- Fila normal --}}
                                            <tr class="hover:bg-slate-50 transition-colors {{ !$usuario->activo ? 'opacity-60' : '' }}">
                                                <td class="px-5 py-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold shrink-0
                                                            {{ ($usuario->rol->nombre ?? '') === 'ADMINISTRADOR' ? 'bg-gradient-to-br from-purple-500 to-purple-700' : 'bg-gradient-to-br from-blue-500 to-blue-700' }}">
                                                            {{ strtoupper(substr($usuario->name, 0, 1)) }}
                                                        </div>
                                                        <div>
                                                            <div class="font-semibold text-gray-900 text-sm leading-tight">{{ $usuario->name }}</div>
                                                            <div class="text-gray-400 text-xs mt-0.5">{{ $usuario->email }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-4 hidden sm:table-cell">
                                                    <code class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-lg">{{ $usuario->username }}</code>
                                                </td>
                                                <td class="px-5 py-4">
                                                    @php
                                                        $rolNombre = $usuario->rol->nombre ?? 'Sin rol';
                                                        $rolClase = match($rolNombre) {
                                                            'ADMINISTRADOR' => 'bg-purple-100 text-purple-700 border border-purple-200',
                                                            'OPERACIONES'   => 'bg-blue-100 text-blue-700 border border-blue-200',
                                                            default         => 'bg-gray-100 text-gray-600 border border-gray-200',
                                                        };
                                                    @endphp
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {{ $rolClase }}">
                                                        {{ $rolNombre }}
                                                    </span>
                                                </td>
                                                <td class="px-5 py-4 text-center">
                                                    @if($usuario->activo)
                                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 border border-green-200">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
                                                            Activo
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-600 border border-red-200">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-red-400 inline-block"></span>
                                                            Inactivo
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-5 py-4">
                                                    <div class="flex items-center justify-center gap-1.5">
                                                        {{-- Editar inline --}}
                                                        <button wire:click="iniciarEdicionInline({{ $usuario->id }})"
                                                                title="Editar usuario desde tabla"
                                                                class="p-2 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 border border-green-200 hover:border-green-300 transition-all">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                            </svg>
                                                        </button>
                                                        {{-- Restablecer contrasena --}}
                                                        <button wire:click="restablecerContrasena({{ $usuario->id }})"
                                                                wire:confirm="Restablecer la contrasena de {{ $usuario->name }}? Se generara una contrasena temporal."
                                                                title="Restablecer contrasena"
                                                                class="p-2 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 border border-amber-200 hover:border-amber-300 transition-all">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                      d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                                            </svg>
                                                        </button>
                                                        @if($usuario->id !== auth()->id())
                                                            {{-- Activar / Desactivar --}}
                                                            <button wire:click="desactivarActivar({{ $usuario->id }})"
                                                                    title="{{ $usuario->activo ? 'Desactivar usuario' : 'Activar usuario' }}"
                                                                    class="p-2 rounded-lg transition-all border {{ $usuario->activo ? 'bg-yellow-50 text-yellow-600 hover:bg-yellow-100 border-yellow-200 hover:border-yellow-300' : 'bg-green-50 text-green-600 hover:bg-green-100 border-green-200 hover:border-green-300' }}">
                                                                @if($usuario->activo)
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                              d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                                    </svg>
                                                                @else
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                    </svg>
                                                                @endif
                                                            </button>
                                                            {{-- Eliminar --}}
                                                            <button wire:click="eliminar({{ $usuario->id }})"
                                                                    wire:confirm="Eliminar a {{ $usuario->name }}? Esta accion es irreversible."
                                                                    title="Eliminar usuario"
                                                                    class="p-2 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 border border-red-200 hover:border-red-300 transition-all">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                </svg>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="py-16 text-center">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <p class="text-gray-400 font-medium">No se encontraron usuarios</p>
                            @if($buscar || $filtro_rol)
                                <p class="text-gray-400 text-sm mt-1">Intenta con otros filtros</p>
                            @endif
                        </div>
                    @endif
                </div>

            </div>
        </div>

    </div>
</div>
