<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Registro de Hallazgos
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Nuevo hallazgo</h3>

                @if (session('success'))
                    <div class="mb-4 rounded bg-green-100 text-green-800 px-4 py-2 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('hallazgos.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Fecha --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fecha operación *</label>
                            <input type="date" name="fecha_operacion"
                                   value="{{ old('fecha_operacion', now()->toDateString()) }}"
                                   class="mt-1 w-full rounded border-gray-300 shadow-sm focus:ring-indigo-500">
                            @error('fecha_operacion') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Código --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Código *</label>
                            <input type="text" name="codigo" value="{{ old('codigo') }}"
                                   class="mt-1 w-full rounded border-gray-300 shadow-sm focus:ring-indigo-500">
                            @error('codigo') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Producto --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Producto *</label>
                            <select name="producto_id" class="mt-1 w-full rounded border-gray-300 shadow-sm focus:ring-indigo-500">
                                <option value="">Seleccione...</option>
                                @foreach ($productos as $producto)
                                    <option value="{{ $producto->id }}" @selected(old('producto_id') == $producto->id)>
                                        {{ $producto->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('producto_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Tipo hallazgo --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tipo hallazgo *</label>
                            <select name="tipo_hallazgo_id" class="mt-1 w-full rounded border-gray-300 shadow-sm focus:ring-indigo-500">
                                <option value="">Seleccione...</option>
                                @foreach ($tiposHallazgo as $tipo)
                                    <option value="{{ $tipo->id }}" @selected(old('tipo_hallazgo_id') == $tipo->id)>
                                        {{ $tipo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tipo_hallazgo_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Ubicación --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Ubicación</label>
                            <select name="ubicacion_id" class="mt-1 w-full rounded border-gray-300 shadow-sm">
                                <option value="">Sin especificar</option>
                                @foreach ($ubicaciones as $ubicacion)
                                    <option value="{{ $ubicacion->id }}" @selected(old('ubicacion_id') == $ubicacion->id)>
                                        {{ $ubicacion->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Lado --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Lado</label>
                            <select name="lado_id" class="mt-1 w-full rounded border-gray-300 shadow-sm">
                                <option value="">Sin especificar</option>
                                @foreach ($lados as $lado)
                                    <option value="{{ $lado->id }}" @selected(old('lado_id') == $lado->id)>
                                        {{ $lado->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Operario --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Operario</label>
                            <input type="text" name="operario_nombre" value="{{ old('operario_nombre') }}"
                                   class="mt-1 w-full rounded border-gray-300 shadow-sm">
                        </div>
                    </div>

                    {{-- Observación --}}
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700">Observación</label>
                        <textarea name="observacion" rows="3"
                                  class="mt-1 w-full rounded border-gray-300 shadow-sm focus:ring-indigo-500">{{ old('observacion') }}</textarea>
                    </div>

                    {{-- EVIDENCIA --}}
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">📸 Evidencia (opcional)</label>
                        <input type="file" name="evidencia" accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        @error('evidencia') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mt-8 flex justify-end space-x-3">
                        <a href="{{ route('hallazgos.index') }}" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                            Cancelar
                        </a>
                        <button type="submit"
                                class="px-6 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700">
                            Guardar hallazgo
                        </button>
                    </div>
                </form>

                {{-- TABLA CON EVIDENCIA --}}
                <div class="mt-12">
                    <h4 class="text-xl font-bold mb-6 text-gray-900">Últimos registros</h4>
                    @if($registrosRecientes->count() > 0)
                        <div class="overflow-x-auto bg-white shadow rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hallazgo</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Evidencia</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($registrosRecientes as $registro)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $registro->fecha_operacion->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $registro->codigo }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                {{ $registro->producto->nombre }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                {{ $registro->tipoHallazgo->nombre }}
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                @if($registro->evidencia_path)
                                                    <img src="{{ Storage::url($registro->evidencia_path) }}" 
                                                         alt="Evidencia" 
                                                         class="w-20 h-20 object-cover rounded-lg border-2 border-gray-300 hover:border-blue-400 cursor-pointer transition-all mx-auto shadow-sm hover:shadow-md"
                                                         onclick="window.open('{{ Storage::url($registro->evidencia_path) }}', '_blank')"
                                                         title="Click para ver en tamaño completo">
                                                @else
                                                    <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-500 rounded-full text-xs">
                                                        📷 Sin foto
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="text-gray-400 mb-4 text-4xl">📋</div>
                            <p class="text-gray-500">No hay registros aún. ¡Registra el primero!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
