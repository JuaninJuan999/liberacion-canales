<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Historial de registros</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 8px; color: #111; margin: 12px 14px; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .header-table td { vertical-align: middle; border: none; padding: 0; }
        .logo-cell { width: 90px; padding-right: 12px; }
        .logo-img { max-width: 82px; max-height: 52px; }
        .title-cell h1 { font-size: 15px; margin: 0 0 6px; text-align: left; }
        .title-cell p { margin: 2px 0; color: #555; font-size: 9px; text-align: left; }
        table.datos { width: 100%; border-collapse: collapse; }
        table.datos th, table.datos td { border: 1px solid #ccc; padding: 4px 5px; text-align: left; vertical-align: middle; }
        table.datos th { background: #b91c1c; color: #fff; font-size: 7px; text-transform: uppercase; }
        table.datos td { font-size: 7px; }
        .critico { color: #b91c1c; font-weight: bold; }
        .leve { color: #a16207; }
        table.datos tr:nth-child(even) { background: #f9fafb; }
        .evidencia-img { width: 38px; height: 38px; object-fit: cover; border: 1px solid #d1d5db; border-radius: 3px; display: block; margin: 0 auto; }
        td.evidencia { text-align: center; width: 48px; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if (! empty($logoDataUri))
                    <img src="{{ $logoDataUri }}" alt="Logo" class="logo-img">
                @endif
            </td>
            <td class="title-cell">
                <h1>Historial de registros de hallazgos</h1>
                <p>
                    Período operativo:
                    {{ \Carbon\Carbon::parse($filtros['fecha_inicio'])->format('d/m/Y') }}
                    —
                    {{ \Carbon\Carbon::parse($filtros['fecha_fin'])->format('d/m/Y') }}
                </p>
                <p>Generado: {{ $generado->format('d/m/Y H:i') }}</p>
            </td>
        </tr>
    </table>

    <table class="datos">
        <thead>
            <tr>
                <th>Fecha registro</th>
                <th>Fecha operación</th>
                <th>Código</th>
                <th>Producto</th>
                <th>Tipo hallazgo</th>
                <th>Ubicación</th>
                <th>Detalle</th>
                <th>Usuario</th>
                <th>Operario</th>
                <th>Evidencia</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($registros as $registro)
                <tr>
                    <td>{{ $registro->created_at?->format('d/m/Y H:i') }}</td>
                    <td>{{ $registro->fecha_operacion?->format('d/m/Y') }}</td>
                    <td>{{ $registro->codigo }}</td>
                    <td>{{ $registro->producto->nombre ?? 'N/A' }}</td>
                    <td class="{{ ($registro->tipoHallazgo->es_critico ?? false) ? 'critico' : 'leve' }}">
                        {{ $registro->tipoHallazgo->nombre ?? 'N/A' }}
                    </td>
                    <td>{{ \App\Support\HistorialRegistrosConsulta::ubicacionHallazgo($registro) }}</td>
                    <td>{{ \App\Support\HistorialRegistrosConsulta::detallePierna($registro) }}</td>
                    <td>{{ $registro->usuario->name ?? 'N/A' }}</td>
                    <td>{{ \App\Support\HistorialRegistrosConsulta::operarioResponsable($registro) }}</td>
                    <td class="evidencia">
                        @php $evidenciaDataUri = \App\Support\HistorialRegistrosConsulta::evidenciaDataUri($registro->evidencia_path); @endphp
                        @if ($evidenciaDataUri)
                            <img src="{{ $evidenciaDataUri }}" alt="Evidencia" class="evidencia-img">
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align:center;">No hay registros para los filtros aplicados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
