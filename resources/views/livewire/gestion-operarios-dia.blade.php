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
                </div>
            </div>
        </div>

        {{-- Tabla de asignaciones --}}
        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900">
                    Asignación de Operarios a Puestos
                </h3>

                @if($puestos->count() > 0 && $operariosDisponibles->count() > 0)
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
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $puesto->nombre }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <select wire:model="asignaciones.{{ $puesto->id }}"
                                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="">-- Sin asignar --</option>
                                                @foreach($operariosDisponibles as $operario)
                                                    <option value="{{ $operario->id }}">
                                                        {{ $operario->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="text-gray-400 mb-4 text-4xl">⚠️</div>
                        @if($puestos->count() === 0)
                            <p class="text-gray-500">No hay puestos de trabajo registrados.</p>
                        @elseif($operariosDisponibles->count() === 0)
                            <p class="text-gray-500">No hay operarios activos disponibles.</p>
                        @endif
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
    </div>
</div>
