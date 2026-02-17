<?php

namespace App\Http\Controllers;

use App\Models\Operario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OperarioController extends Controller
{
    /**
     * Mostrar lista de operarios
     */
    public function index()
    {
        $operarios = Operario::orderBy('nombre')->paginate(20);
        
        return view('operarios.index', compact('operarios'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('operarios.create');
    }

    /**
     * Guardar nuevo operario
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'documento' => ['nullable', 'string', 'max:20', 'unique:operarios,documento'],
            'activo' => ['boolean'],
        ]);

        $validated['activo'] = $request->has('activo');

        Operario::create($validated);

        return redirect()
            ->route('operarios.index')
            ->with('success', '✅ Operario registrado exitosamente');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Operario $operario)
    {
        return view('operarios.edit', compact('operario'));
    }

    /**
     * Actualizar operario
     */
    public function update(Request $request, Operario $operario)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'documento' => ['nullable', 'string', 'max:20', 'unique:operarios,documento,' . $operario->id],
            'activo' => ['boolean'],
        ]);

        $validated['activo'] = $request->has('activo');

        $operario->update($validated);

        return redirect()
            ->route('operarios.index')
            ->with('success', '✅ Operario actualizado exitosamente');
    }

    /**
     * Eliminar operario
     */
    public function destroy(Operario $operario)
    {
        try {
            $operario->delete();
            
            return redirect()
                ->route('operarios.index')
                ->with('success', '✅ Operario eliminado exitosamente');
        } catch (\Exception $e) {
            Log::error('Error al eliminar operario: ' . $e->getMessage());
            
            return redirect()
                ->route('operarios.index')
                ->with('error', '❌ No se puede eliminar el operario porque tiene registros asociados');
        }
    }

    /**
     * Cambiar estado activo/inactivo
     */
    public function toggleEstado(Operario $operario)
    {
        $operario->update([
            'activo' => !$operario->activo
        ]);

        $estado = $operario->activo ? 'activado' : 'desactivado';
        
        return redirect()
            ->route('operarios.index')
            ->with('success', "✅ Operario {$estado} exitosamente");
    }
}
