<div class="min-h-full bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">👥 Gestión de Usuarios</h1>
            <p class="text-gray-600">Administra los usuarios, roles, permisos y contraseñas del sistema</p>
        </div>

        {{-- Mensajes --}}
        @if($mensaje)
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-init="setTimeout(() => show = false, 4000)"
                 x-transition
                 class="mb-6 p-4 rounded-lg {{ $tipoMensaje === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300' }}"
                 wire:click="limpiarMensaje">
                <p class="font-semibold">{{ $mensaje }}</p>
            </div>
        @endif

        {{-- Contenido principal en dos columnas --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Columna izquierda: Búsqueda y controles --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        🔍 Búsqueda y Filtros
                    </h2>

                    {{-- Búsqueda por nombre/email --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                        <input type="text" 
                               wire:model.live="buscar"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Nombre o email...">
                    </div>

                    {{-- Filtro por rol --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filtrar por Rol</label>
                        <select wire:model.live="filtro_rol" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todos los roles</option>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Botón crear usuario --}}
                    @if($modo === 'vista')
                        <button wire:click="mostrarFormularioCrear" 
                                class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold py-3 rounded-lg hover:shadow-lg transition transform hover:scale-105">
                            ➕ Crear Usuario
                        </button>
                    @endif
                </div>
            </div>

            {{-- Columna derecha: Tabla de usuarios o Formulario --}}
            <div class="lg:col-span-2">
                
                {{-- FORMULARIO DE CREAR/EDITAR --}}
                @if($modo !== 'vista')
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">
                            {{ $modo === 'crear' ? '➕ Crear Usuario' : '✏️ Editar Usuario' }}
                        </h2>

                        <form wire:submit.prevent="guardar" class="space-y-4">
                            {{-- Nombre --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Completo</label>
                                <input type="text" 
                                       wire:model="nombre"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Ej: Juan Pérez">
                                @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Email --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" 
                                       wire:model="email"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Ej: juan@example.com">
                                @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Nombre de Usuario --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de Usuario (Login)</label>
                                <input type="text" 
                                       wire:model="username"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Ej: juan.perez">
                                <p class="text-gray-500 text-xs mt-1">Formato: nombre.apellido (en minúsculas)</p>
                                @error('username') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Contraseña (obligatoria al crear, opcional al editar) --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Contraseña 
                                    @if($modo === 'editar')
                                        <span class="text-gray-500 text-xs">(Dejar en blanco para no cambiar)</span>
                                    @endif
                                </label>
                                <input type="password" 
                                       wire:model="password"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Mínimo 8 caracteres"
                                       @if($modo === 'crear') required @endif>
                                @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Rol --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                                <select wire:model="rol_id"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Seleccionar rol...</option>
                                    @foreach($roles as $rol)
                                        <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('rol_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Estado --}}
                            <div class="flex items-center gap-3">
                                <input type="checkbox" 
                                       wire:model="activo" 
                                       id="activo"
                                       class="h-5 w-5 rounded border-gray-300 text-blue-600">
                                <label for="activo" class="text-sm font-medium text-gray-700">Activo</label>
                            </div>

                            {{-- Actions --}}
                            <div class="flex gap-3 pt-4">
                                <button type="submit" 
                                        class="flex-1 bg-blue-600 text-white font-semibold py-2 rounded-lg hover:bg-blue-700 transition">
                                    {{ $modo === 'crear' ? '✨ Crear' : '💾 Guardar' }}
                                </button>
                                <button type="button" 
                                        wire:click="cancelar"
                                        class="flex-1 bg-gray-200 text-gray-800 font-semibold py-2 rounded-lg hover:bg-gray-300 transition">
                                    ✕ Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- TABLA DE USUARIOS --}}
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100">
                        <h2 class="text-lg font-semibold text-gray-900">
                            📋 Usuarios {{ $usuarios->count() > 0 ? '(' . $usuarios->count() . ')' : '(0)' }}
                        </h2>
                    </div>

                    @if($usuarios->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-100 border-b border-gray-200">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Nombre</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Email</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Rol</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Estado</th>
                                        <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($usuarios as $usuario)
                                        <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                                            <td class="px-6 py-4">
                                                <div class="font-semibold text-gray-900">{{ $usuario->name }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-gray-600 text-sm">{{ $usuario->email }}</td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800 font-medium">
                                                    {{ $usuario->rol->nombre }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($usuario->activo)
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-100 text-green-800 font-medium">
                                                        ✅ Activo
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-red-100 text-red-800 font-medium">
                                                        ❌ Inactivo
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex gap-2 justify-center flex-wrap">
                                                    {{-- Editar --}}
                                                    <button wire:click="mostrarFormularioEditar({{ $usuario->id }})"
                                                            class="px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 text-sm font-medium transition">
                                                        ✏️
                                                    </button>

                                                    {{-- Restablecer contraseña --}}
                                                    <button wire:click="restablecerContrasena({{ $usuario->id }})"
                                                            class="px-3 py-1 bg-amber-100 text-amber-700 rounded hover:bg-amber-200 text-sm font-medium transition"
                                                            title="Restablecer contraseña">
                                                        🔑
                                                    </button>

                                                    {{-- Desactivar/Activar --}}
                                                    <button wire:click="desactivarActivar({{ $usuario->id }})"
                                                            class="px-3 py-1 {{ $usuario->activo ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }} rounded text-sm font-medium transition"
                                                            title="{{ $usuario->activo ? 'Desactivar' : 'Activar' }}">
                                                        {{ $usuario->activo ? '⏸️' : '▶️' }}
                                                    </button>

                                                    {{-- Eliminar (no mostrar para usuario actual) --}}
                                                    @if($usuario->id !== auth()->id())
                                                        <button wire:click="$confirm('¿Eliminar a {{ $usuario->name }}?', () => @this.eliminar({{ $usuario->id }}))"
                                                                class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm font-medium transition"
                                                                title="Eliminar usuario">
                                                            🗑️
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
                        <div class="px-6 py-12 text-center">
                            <p class="text-gray-500 text-lg">No se encontraron usuarios</p>
                        </div>
                    @endif
                </div>

            </div>

        </div>

    </div>
</div>
