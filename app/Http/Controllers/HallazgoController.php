<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\TipoHallazgo;
use App\Models\Ubicacion;
use App\Models\Lado;
use App\Models\RegistroHallazgo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class HallazgoController extends Controller
{
    public function index()
    {
        return view('hallazgos.index', [
            'productos'        => Producto::all(),
            'tiposHallazgo'    => TipoHallazgo::all(),
            'ubicaciones'      => Ubicacion::all(),
            'lados'            => Lado::all(),
            'registrosRecientes' => RegistroHallazgo::with(['producto', 'tipoHallazgo'])
                ->latest('fecha_registro')
                ->limit(10)
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha_operacion'   => ['required', 'date'],
            'codigo'            => ['required', 'string', 'max:50'],
            'producto_id'       => ['required', 'exists:productos,id'],
            'tipo_hallazgo_id'  => ['required', 'exists:tipos_hallazgo,id'],
            'ubicacion_id'      => ['nullable', 'exists:ubicaciones,id'],
            'lado_id'           => ['nullable', 'exists:lados,id'],
            'observacion'       => ['nullable', 'string', 'max:500'],
            'operario_nombre'   => ['nullable', 'string', 'max:100'],
            'evidencia'         => ['nullable', 'image', 'max:2048'],
        ]);

        // ✅ USAR $validated + GUARDAR IMAGEN
        $data = [
            'fecha_registro'   => now(),
            'fecha_operacion'  => $validated['fecha_operacion'],
            'codigo'           => $validated['codigo'],
            'producto_id'      => $validated['producto_id'],
            'tipo_hallazgo_id' => $validated['tipo_hallazgo_id'],
            'ubicacion_id'     => $validated['ubicacion_id'] ?? null,
            'lado_id'          => $validated['lado_id'] ?? null,
            'observacion'      => $validated['observacion'] ?? null,
            'operario_nombre'  => $validated['operario_nombre'] ?? null,
            'evidencia_path'   => null,
            'operario_id'      => null,
            'usuario_id'       => Auth::id(),
        ];

        // 🔥 GUARDAR FOTO
        if ($request->hasFile('evidencia')) {
            $imagePath = $request->file('evidencia')->store('hallazgos', 'public');
            $data['evidencia_path'] = $imagePath;
            Log::info('✅ Foto guardada: ' . $imagePath);
        }

        RegistroHallazgo::create($data);

        return redirect()
            ->route('hallazgos.index')
            ->with('success', '✅ Hallazgo guardado! ' . ($data['evidencia_path'] ? 'Foto OK' : 'Sin foto'));
    }
}
