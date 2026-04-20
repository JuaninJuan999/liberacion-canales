<?php

namespace App\Http\Controllers;

use App\Models\PuestoTrabajo;
use Illuminate\Http\Request;

class PuestoTrabajoController extends Controller
{
    /**
     * Muestra una lista de los puestos de trabajo.
     */
    public function index()
    {
        $puestos = PuestoTrabajo::orderBy('orden', 'asc')->get();

        return view('puestos_trabajo.index', compact('puestos'));
    }

    /**
     * Muestra el formulario para crear un nuevo puesto de trabajo.
     */
    public function create()
    {
        $maxOrden = PuestoTrabajo::withoutGlobalScope('ordered')->max('orden');
        $siguienteOrden = (int) $maxOrden + 1;

        return view('puestos_trabajo.create', compact('siguienteOrden'));
    }

    /**
     * Almacena un nuevo puesto de trabajo en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:puestos_trabajo,nombre',
            'descripcion' => 'nullable|string|max:255',
            'orden' => 'required|integer',
        ]);

        $validated['descripcion'] = $this->normalizarDescripcion($validated['descripcion'] ?? null);

        PuestoTrabajo::create($validated);

        return redirect()->route('puestos_trabajo.index')
            ->with('success', '✅ Puesto de trabajo creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un puesto de trabajo existente.
     */
    public function edit(PuestoTrabajo $puestos_trabajo)
    {
        $puestoTrabajo = $puestos_trabajo;

        return view('puestos_trabajo.edit', compact('puestoTrabajo'));
    }

    /**
     * Actualiza el puesto de trabajo especificado en la base de datos.
     */
    public function update(Request $request, PuestoTrabajo $puestos_trabajo)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:puestos_trabajo,nombre,'.$puestos_trabajo->id,
            'descripcion' => 'nullable|string|max:255',
            'orden' => 'required|integer',
        ]);

        $validated['descripcion'] = $this->normalizarDescripcion($validated['descripcion'] ?? null);

        $puestos_trabajo->update($validated);

        return redirect()->route('puestos_trabajo.index')
            ->with('success', '✅ Puesto de trabajo actualizado exitosamente.');
    }

    /**
     * Elimina el puesto de trabajo especificado de la base de datos.
     */
    public function destroy(PuestoTrabajo $puestos_trabajo)
    {
        // Opcional: Añadir validación para no eliminar si tiene operarios asignados.
        $puestos_trabajo->delete();

        return redirect()->route('puestos_trabajo.index')
            ->with('success', '🗑️ Puesto de trabajo eliminado exitosamente.');
    }

    /**
     * Cadena vacía o solo espacios → null (evita duplicar nombre sin querer en BD).
     */
    private function normalizarDescripcion(?string $descripcion): ?string
    {
        if ($descripcion === null) {
            return null;
        }
        $t = trim($descripcion);

        return $t === '' ? null : $t;
    }
}
