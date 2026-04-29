<div class="min-h-full bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Permisos — Verificadores de titulación</h1>
                <p class="mt-1 text-sm text-gray-600">Marca qué usuarios podrán elegirse como «Verificado» en el formulario de titulación de ácido láctico (independiente del rol).</p>
            </div>
            <a href="{{ route('usuarios.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver a usuarios
            </a>
        </div>

        @if ($mensaje)
            <div class="mb-6 p-4 rounded-lg border bg-green-50 text-green-800 border-green-200 text-sm font-medium">
                {{ $mensaje }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Usuario</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Rol</th>
                            <th class="px-6 py-3 text-center font-semibold text-gray-700">¿Verificador autorizado?</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($usuarios as $usuario)
                            <tr wire:key="pv-{{ $usuario->id }}" class="hover:bg-gray-50/80">
                                <td class="px-6 py-3">
                                    <span class="font-medium text-gray-900">{{ $usuario->name }}</span>
                                    @if ($usuario->username)
                                        <span class="block text-xs text-gray-500">{{ '@'.$usuario->username }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-gray-700">{{ $usuario->rol->nombre ?? '—' }}</td>
                                <td class="px-6 py-3 text-center">
                                    <button type="button"
                                            wire:click="toggleVerificador({{ $usuario->id }})"
                                            class="relative inline-flex h-7 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 {{ $usuario->puede_verificar_titulacion ? 'bg-teal-600' : 'bg-gray-200' }}"
                                            role="switch"
                                            aria-checked="{{ $usuario->puede_verificar_titulacion ? 'true' : 'false' }}">
                                        <span class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow ring-0 transition {{ $usuario->puede_verificar_titulacion ? 'translate-x-5' : 'translate-x-1' }}"></span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <p class="mt-4 text-xs text-gray-500">Solo usuarios <strong>activos</strong> marcados aquí aparecerán en el desplegable «Verificado» del módulo de titulación.</p>
    </div>
</div>
