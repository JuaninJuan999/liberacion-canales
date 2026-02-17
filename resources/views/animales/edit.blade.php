<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Registro de Animales
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if (session('error'))
                <div class="mb-4 rounded bg-red-100 text-red-800 px-4 py-3 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('animales.update', $registro) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        {{-- Fecha --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Fecha de Operación *
                            </label>
                            <input type="date" 
                                   name="fecha_operacion"
                                   value="{{ old('fecha_operacion', $registro->fecha_operacion->toDateString()) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   required>
                            @error('fecha_operacion')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Cantidad --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Cantidad de Animales *
                            </label>
                            <input type="number" 
                                   name="cantidad_animales"
                                   value="{{ old('cantidad_animales', $registro->cantidad_animales) }}"
                                   min="1"
                                   max="10000"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   required>
                            @error('cantidad_animales')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Info adicional --}}
                        <div class="bg-gray-50 rounded-md p-4">
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">Registrado por:</span> {{ $registro->usuario->name ?? 'N/A' }}
                            </p>
                            <p class="text-sm text-gray-600 mt-1">
                                <span class="font-medium">Fecha de registro:</span> {{ $registro->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>

                    {{-- Botones --}}
                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('animales.index') }}" 
                           class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                            Actualizar Registro
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
