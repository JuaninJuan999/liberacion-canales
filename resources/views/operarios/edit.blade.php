<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Operario
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('operarios.update', $operario) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Nombre --}}
                    <div class="mb-4">
                        <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                            Nombre Completo *
                        </label>
                        <input type="text" 
                               id="nombre" 
                               name="nombre" 
                               value="{{ old('nombre', $operario->nombre) }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               required>
                        @error('nombre')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Documento --}}
                    <div class="mb-4">
                        <label for="documento" class="block text-sm font-medium text-gray-700 mb-2">
                            Documento de Identidad
                        </label>
                        <input type="text" 
                               id="documento" 
                               name="documento" 
                               value="{{ old('documento', $operario->documento) }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Opcional">
                        @error('documento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Estado Activo --}}
                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="activo" 
                                   value="1"
                                   {{ old('activo', $operario->activo) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Operario activo</span>
                        </label>
                    </div>

                    {{-- Botones --}}
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('operarios.index') }}" 
                           class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                            Actualizar Operario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
