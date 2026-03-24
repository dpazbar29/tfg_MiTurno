<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Servicio;

class ServicioController extends Controller
{
    /**
     * Obtener todos los servicios.
     */
    public function index()
    {
        return Servicio::all();
    }

    /**
     * Almacenar los nuevos servicios creados.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'duracion_minutos' => 'required|integer|min:1',
            'precio' => 'required|numeric|min:0',
            'activo' => 'boolean',
        ]);

        $servicio = Servicio::create($data);

        return response()->json($servicio, 201);
    }

    /**
     * Mostrar servicio específico.
     */
    public function show(Servicio $servicio)
    {
        return $servicio;
    }

    /**
     * Actualizar uno de los servicios almacenados.
     */
    public function update(Request $request, Servicio $servicio)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'sometimes|nullable|string',
            'duracion_minutos' => 'sometimes|required|integer|min:1',
            'precio' => 'sometimes|required|numeric|min:0',
            'activo' => 'sometimes|boolean',
        ]);

        $servicio->update($data);

        return $servicio;
    }

    /**
     * Eliminar un servicio específico.
     */
    public function destroy(Servicio $servicio)
    {
        $servicio->delete();

        return response()->json(null, 204);
    }
}
