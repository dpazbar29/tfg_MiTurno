<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Horario;
use Illuminate\Http\Request;

class HorarioController extends Controller
{
    /**
     * Obtener todos los horarios.
     */
    public function index()
    {
        return Horario::with('empleado.usuario')->get();
    }

    /**
     * Almacenar los nuevos horarios creados.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'dia_semana' => 'required|integer|min:0|max:6',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'tipo' => 'required|in:normal,festivo,cierre',
            'activo' => 'boolean',
        ]);

        $horario = Horario::create($data);

        return response()->json($horario->load('empleado.usuario'), 201);
    }

    /**
     * Mostrar horario específico.
     */
    public function show(Horario $horario)
    {
        return $horario->load('empleado.usuario');
    }

    /**
     * Actualizar uno de los horarios guardados.
     */
    public function update(Request $request, Horario $horario)
    {
        $data = $request->validate([
            'empleado_id' => 'sometimes|exists:empleados,id',
            'dia_semana' => 'sometimes|integer|min:0|max:6',
            'hora_inicio' => 'sometimes|date_format:H:i',
            'hora_fin' => 'sometimes|date_format:H:i|after:hora_inicio',
            'tipo' => 'sometimes|in:normal,festivo,cierre',
            'activo' => 'sometimes|boolean',
        ]);

        $horario->update($data);

        return $horario->load('empleado.usuario');
    }

    /**
     * Eliminar un horario específico.
     */
    public function destroy(Horaio $horario)
    {
        $horario->delete();

        return response()->json(null, 204);
    }
}
