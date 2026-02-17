<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Registro de Animales Procesados
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded bg-green-100 text-green-800 px-4 py-3 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded bg-red-100 text-red-800 px-4 py-3 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            {{-- FORMULARIO DE REGISTRO --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900">Nuevo Registro</h3>

                <form action="{{ route('animales.store') }}" method="POST" class="max-w-2xl">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Fecha --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Fecha de Operación *
                            </label>
                            <input type="date" 
                                   name="fecha_operacion"
                                   value="{{ old('fecha_operacion', now()->toDateString()) }}"
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
                                   value="{{ old('cantidad_animales') }}"
                                   min="1"
                                   max="10000"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="Ej: 450"
                                   required>
                            @error('cantidad_animales')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit"
                                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                            📋 Guardar Registro
                        </button>
                    </div>
                </form>
            </div>

            {{-- TABLA DE REGISTROS --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-6">
                    <div class="mb-4 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">Historial de Registros</h3>
                        <span class="text-sm text-gray-600">Total: {{ $registros->total() }}</span>
                    </div>

                    @if($registros->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registrado por</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($registros as $registro)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $registro->fecha_operacion->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                                <span class="font-semibold text-blue-600">
                                                    {{ number_format($registro->cantidad_animales, 0, ',', '.') }}
                                                </span>
                                                animales
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                {{ $registro->usuario->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                <div class="flex justify-center space-x-2">
                                                    {{-- Botón Editar --}}
                                                    <a href="{{ route('animales.edit', $registro) }}" 
                                                       class="text-blue-600 hover:text-blue-900" 
                                                       title="Editar">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </a>

                                                    {{-- Botón Eliminar --}}
                                                    <form action="{{ route('animales.destroy', $registro) }}" 
                                                          method="POST" 
                                                          class="inline"
                                                          onsubmit="return confirm('¿Estás seguro de eliminar este registro?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="text-red-600 hover:text-red-900" 
                                                                title="Eliminar">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td class="px-6 py-4 text-sm font-semibold text-gray-700">Total:</td>
                                        <td class="px-6 py-4 text-right text-sm font-bold text-blue-600">
                                            {{ number_format($registros->sum('cantidad_animales'), 0, ',', '.') }} animales
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        {{-- Paginación --}}
                        <div class="mt-4">
                            {{ $registros->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="text-gray-400 mb-4 text-4xl">🐄</div>
                            <p class="text-gray-500">No hay registros de animales procesados. ¡Crea el primero!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
