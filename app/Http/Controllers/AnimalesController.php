<?php

namespace App\Http\Controllers;

use App\Models\AnimalProcesado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnimalesController extends Controller
{
    /**
     * Mostrar el formulario y listado de animales procesados
     */
    public function index()
    {
        $registros = AnimalProcesado::with('usuario')
            ->orderBy('fecha_operacion', 'desc')
            ->paginate(15);

        return view('animales.index', compact('registros'));
    }

    /**
     * Guardar registro de animales procesados
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha_operacion' => ['required', 'date'],
            'cantidad_animales' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);

        // Verificar si ya existe un registro para esa fecha
        $existente = AnimalProcesado::where('fecha_operacion', $validated['fecha_operacion'])->first();

        if ($existente) {
            return redirect()
                ->route('animales.index')
                ->with('error', '❌ Ya existe un registro para esta fecha. Usa la opción de editar.');
        }

        AnimalProcesado::create([
            'fecha_operacion' => $validated['fecha_operacion'],
            'cantidad_animales' => $validated['cantidad_animales'],
            'usuario_id' => Auth::id(),
        ]);

        return redirect()
            ->route('animales.index')
            ->with('success', '✅ Registro de animales guardado exitosamente');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(AnimalProcesado $animale)
    {
        return view('animales.edit', ['registro' => $animale]);
    }

    /**
     * Actualizar registro
     */
    public function update(Request $request, AnimalProcesado $animale)
    {
        $validated = $request->validate([
            'fecha_operacion' => ['required', 'date'],
            'cantidad_animales' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);

        // Verificar si ya existe otro registro para esa fecha (excluyendo el actual)
        $existente = AnimalProcesado::where('fecha_operacion', $validated['fecha_operacion'])
            ->where('id', '!=', $animale->id)
            ->first();

        if ($existente) {
            return redirect()
                ->route('animales.edit', $animale)
                ->with('error', '❌ Ya existe un registro para esta fecha.');
        }

        $animale->update($validated);

        return redirect()
            ->route('animales.index')
            ->with('success', '✅ Registro actualizado exitosamente');
    }

    /**
     * Eliminar registro
     */
    public function destroy(AnimalProcesado $animale)
    {
        $animale->delete();

        return redirect()
            ->route('animales.index')
            ->with('success', '✅ Registro eliminado exitosamente');
    }

    /**
     * Obtener estadísticas generales
     */
    public function estadisticas()
    {
        $stats = [
            'total_animales_mes' => AnimalProcesado::whereMonth('fecha_operacion', now()->month)
                ->whereYear('fecha_operacion', now()->year)
                ->sum('cantidad_animales'),
            'promedio_diario' => AnimalProcesado::whereMonth('fecha_operacion', now()->month)
                ->whereYear('fecha_operacion', now()->year)
                ->avg('cantidad_animales'),
            'dias_registrados' => AnimalProcesado::whereMonth('fecha_operacion', now()->month)
                ->whereYear('fecha_operacion', now()->year)
                ->count(),
        ];

        return response()->json($stats);
    }
}
