<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                        <img src="{{ asset('logo.png') }}" alt="Logo" class="h-6 sm:h-10 max-w-[40px] sm:max-w-[80px] object-contain flex-shrink-0">
                        <div class="min-w-0">
                            <h1 class="text-lg sm:text-2xl font-bold text-gray-900 truncate">Promedio del Año</h1>
                            <p class="text-gray-500 mt-1 text-xs sm:text-sm">Indicadores mensuales consolidados</p>
                        </div>
                    </div>
                    <a href="{{ route('dashboard.mensual', ['mes' => now()->month, 'anio' => $anio]) }}"
                       class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition text-xs sm:text-sm flex-shrink-0 text-center">
                        ← Volver al Dashboard Mensual
                    </a>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <form method="GET" action="{{ route('dashboard.mensual.promedio-anual') }}" class="flex flex-wrap items-center gap-3 sm:gap-4">
                    <label class="text-sm font-medium text-gray-700">Año:</label>
                    <select name="anio"
                            class="rounded-md border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600"
                            onchange="this.form.submit()">
                        @for($a = now()->year - 2; $a <= now()->year + 1; $a++)
                            <option value="{{ $a }}" {{ $a == $anio ? 'selected' : '' }}>{{ $a }}</option>
                        @endfor
                    </select>
                </form>
            </div>

            @if(($acumulado['mes_limite'] ?? 0) === 0)
                <div class="bg-white shadow-sm sm:rounded-lg p-10 text-center text-gray-500">
                    No hay datos para mostrar en este año.
                </div>
            @else
                <div class="bg-white shadow-sm sm:rounded-lg p-4 sm:p-6 overflow-x-auto border border-[#7ce8ad]/60">
                    <table class="min-w-full border-2 border-gray-800 text-sm border-collapse">
                        <thead>
                            <tr>
                                <th colspan="{{ count($acumulado['columnas_meses']) + 2 }}"
                                    class="border-2 border-gray-800 bg-[#7ce8ad] px-3 py-2.5 text-center font-bold text-gray-900 text-base sm:text-lg tracking-wide">
                                    ACUMULADO LIBERACION DE CANALES {{ $anio }}
                                </th>
                            </tr>
                            <tr>
                                <th class="border-2 border-gray-800 bg-[#7ce8ad] px-3 py-2 text-left font-bold min-w-[12rem] text-gray-900">Ítem</th>
                                @foreach($acumulado['columnas_meses'] as $col)
                                    <th class="border-2 border-gray-800 bg-[#f9dff8] px-2 py-2 text-center font-bold uppercase whitespace-nowrap text-gray-900">
                                        {{ $col['label'] }}
                                    </th>
                                @endforeach
                                <th class="border-2 border-gray-800 bg-[#f9dff8] px-3 py-2 text-center font-bold min-w-[6rem] text-gray-900">Promedio</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($acumulado['filas'] as $fila)
                                <tr class="{{ !empty($fila['is_total']) ? 'bg-[#7ce8ad]/25' : 'bg-white' }}">
                                    <td class="border-2 border-gray-800 px-3 py-2 font-semibold text-gray-900 {{ !empty($fila['is_total']) ? 'font-bold bg-[#7ce8ad]/40' : 'bg-[#7ce8ad]/15' }}">
                                        {{ $fila['label'] }}
                                    </td>
                                    @foreach($fila['valores'] as $valor)
                                        <td class="border-2 border-gray-800 px-2 py-2 text-center tabular-nums text-gray-900 {{ !empty($fila['is_total']) ? 'font-bold bg-[#f9dff8]/30' : 'bg-[#f9dff8]/10' }}">
                                            {{ \App\Support\AcumuladoAnualLiberacion::formatoPorcentaje($valor) }}
                                        </td>
                                    @endforeach
                                    <td class="border-2 border-gray-800 px-3 py-2 text-center tabular-nums font-bold text-gray-900 bg-[#f9dff8]">
                                        {{ \App\Support\AcumuladoAnualLiberacion::formatoPorcentaje($fila['promedio']) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
