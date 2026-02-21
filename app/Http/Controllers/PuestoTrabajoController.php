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
        return view('puestos_trabajo.create');
    }

    /**
     * Almacena un nuevo puesto de trabajo en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:puestos_trabajo,nombre',
            'orden' => 'required|integer',
        ]);

        PuestoTrabajo::create($request->all());

        return redirect()->route('puestos_trabajo.index')
                         ->with('success', '✅ Puesto de trabajo creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un puesto de trabajo existente.
     */
    public function edit(PuestoTrabajo $puestoTrabajo)
    {
        return view('puestos_trabajo.edit', compact('puestoTrabajo'));
    }

    /**
     * Actualiza el puesto de trabajo especificado en la base de datos.
     */
    public function update(Request $request, PuestoTrabajo $puestoTrabajo)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:puestos_trabajo,nombre,' . $puestoTrabajo->id,
            'orden' => 'required|integer',
        ]);

        $puestoTrabajo->update($request->all());

        return redirect()->route('puestos_trabajo.index')
                         ->with('success', '✅ Puesto de trabajo actualizado exitosamente.');
    }

    /**
     * Elimina el puesto de trabajo especificado de la base de datos.
     */
    public function destroy(PuestoTrabajo $puestoTrabajo)
    {
        // Opcional: Añadir validación para no eliminar si tiene operarios asignados.
        $puestoTrabajo->delete();

        return redirect()->route('puestos_trabajo.index')
                         ->with('success', '🗑️ Puesto de trabajo eliminado exitosamente.');
    }
}
