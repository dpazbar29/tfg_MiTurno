<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Servicio;

class ServicioController extends Controller
{
    /**
     * Obtener todos los servicios.
     *
     * Devuelve el listado completo de servicios sin aplicar filtros.
     */
    public function index()
    {
        return Servicio::all();
    }

    /**
     * Crear un nuevo servicio.
     *
     * Valida los datos recibidos.
     * Crea el servicio en la base de datos.
     * Devuelve el registro recién creado.
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

        // Crea el servicio con los datos validados.
        $servicio = Servicio::create($data);

        return response()->json($servicio, 201);
    }

    /**
     * Mostrar un servicio concreto.
     */
    public function show(Servicio $servicio)
    {
        return $servicio;
    }

    /**
     * Actualizar un servicio existente.
     *
     * Solo se validan y actualizan los campos enviados en la petición.
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
     * Eliminar un servicio concreto.
     */
    public function destroy(Servicio $servicio)
    {
        $servicio->delete();

        return response()->json(null, 204);
    }

    /**
     * Obtener el catálogo de servicios activos.
     *
     * Este método está pensado para mostrar solo los servicios disponibles para los clientes en el frontend o en procesos de reserva.
     */
    public function catalogo()
    {
        return Servicio::where('activo', true)->get();
    }

    /**
     * Obtener los empleados que pueden realizar un servicio concreto.
     *
     * Solo devuelve empleados cuyo usuario asociado esté activo.
     * La respuesta se transforma para devolver únicamente el id y el nombre completo del empleado.
     */
    public function empleados(Servicio $servicio)
    {
        $empleados = $servicio->empleados()
            ->whereHas('usuario', fn($q) => $q->where('activo', true))
            ->with('usuario:id,nombre,apellidos')
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'nombre' => $e->usuario->nombre . ' ' . $e->usuario->apellidos
            ]);

        return response()->json($empleados);
    }
}
