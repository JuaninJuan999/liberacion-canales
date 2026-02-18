<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte Mensual - {{ ucfirst($nombreMes) }} {{ $anio }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin: 5px 0;
        }
        .header p {
            margin: 3px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .totales {
            font-weight: bold;
            background-color: #f0f0f0;
        }
        .resumen {
            margin-top: 20px;
            display: flex;
            justify-content: space-around;
        }
        .resumen-card {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            width: 18%;
        }
        .resumen-card h3 {
            margin: 0;
            font-size: 24px;
            color: #4CAF50;
        }
        .resumen-card p {
            margin: 5px 0 0 0;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE MENSUAL DE INDICADORES</h1>
        <p>{{ ucfirst($nombreMes) }} {{ $anio }}</p>
        <p>Generado el: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="resumen">
        <div class="resumen-card">
            <h3>{{ $totales['dias_operados'] }}</h3>
            <p>Días Operados</p>
        </div>
        <div class="resumen-card">
            <h3>{{ number_format($totales['animales'], 0, ',', '.') }}</h3>
            <p>Animales</p>
        </div>
        <div class="resumen-card">
            <h3>{{ number_format($totales['hallazgos'], 0, ',', '.') }}</h3>
            <p>Hallazgos</p>
        </div>
        <div class="resumen-card">
            <h3>{{ number_format($totales['hematomas'], 0, ',', '.') }}</h3>
            <p>Hematomas</p>
        </div>
        <div class="resumen-card">
            <h3>{{ number_format($totales['cobertura'], 0, ',', '.') }}</h3>
            <p>Cobertura</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th class="text-right">Animales</th>
                <th class="text-right">Hallazgos</th>
                <th class="text-right">Participación %</th>
                <th class="text-right">Hematomas</th>
                <th class="text-right">Cobertura</th>
                <th class="text-right">Cortes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($indicadores as $ind)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($ind->fecha_operacion)->format('d/m/Y') }}</td>
                    <td class="text-right">{{ number_format($ind->animales_procesados, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($ind->total_hallazgos, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($ind->participacion_total, 2) }}%</td>
                    <td class="text-right">{{ $ind->hematomas }}</td>
                    <td class="text-right">{{ $ind->cobertura_grasa }}</td>
                    <td class="text-right">{{ $ind->cortes_piernas }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totales">
                <td>TOTALES</td>
                <td class="text-right">{{ number_format($totales['animales'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totales['hallazgos'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($indicadores->avg('participacion_total'), 2) }}%</td>
                <td class="text-right">{{ number_format($totales['hematomas'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totales['cobertura'], 0, ',', '.') }}</td>
                <td class="text-right">-</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
