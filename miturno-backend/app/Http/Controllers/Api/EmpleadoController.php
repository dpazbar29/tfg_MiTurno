<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Empleado;

class EmpleadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Empleado::with('usuario')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'usuario_id' => 'required|exists:users,id|unique:empleados,usuario_id',
            'especialidades' => 'nullable|string',
            'fecha_contratacion' => 'required|date',
            'activo' => 'boolean',
        ]);

        $empleado = Empleado::create($data);

        return response()->json($empleado->load('usuario'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Empleado $empleado)
    {
        return $empleado->load('usuario');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Empleado $empleado)
    {
        $data = $request->validate([
            'usuario_id' => 'sometimes|exists:users,id|unique:empleados,usuario_id,' . $empleado->id,
            'especialidades' => 'sometimes|nullable|string',
            'fecha_contratacion' => 'sometimes|required|date',
            'activo' => 'sometimes|boolean',
        ]);

        $empleado->update($data);

        return $empleado->load('usuario');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Empleado $empleado)
    {
        $empleado->delete();

        return response()->json(null, 204);
    }
}
