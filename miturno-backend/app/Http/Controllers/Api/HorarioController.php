<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Horario;
use App\Models\Empleado;
use Illuminate\Http\Request;

class HorarioController extends Controller
{
    /**
     * Obtener todos los horarios.
     */
    public function index()
    {
        return Horario::with('empleado.usuario')
            ->orderBy('empleado_id')
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get();
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
            'activo' => 'sometimes|boolean',
        ]);

        $solapa = Horario::where('empleado_id', $data['empleado_id'])
            ->where('dia_semana', $data['dia_semana'])
            ->where('activo', true)
            ->where(function ($query) use ($data) {
                $query->where('hora_inicio', '<', $data['hora_fin'])
                    ->where('hora_fin', '>', $data['hora_inicio']);
            })
            ->exists();
        
        if ($solapa) {
            return response()->json([
                'message' => 'El horario se solapa con otra franja existente.',
                'errors' => [
                    'hora_inicio' => ['El horario se solapa con otra franja existente.'],
                    'hora_fin' => ['El horario se solapa con otra franja existente.'],
                ],
            ], 422);
        }

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
            'hora_fin' => 'sometimes|date_format:H:i',
            'tipo' => 'sometimes|in:normal,festivo,cierre',
            'activo' => 'sometimes|boolean',
        ]);

        $empleadoId = $data['empleado_id'] ?? $horario->empleado_id;
        $diaSemana = $data['dia_semana'] ?? $horario->dia_semana;
        $horaInicio = $data['hora_inicio'] ?? $horario->hora_inicio;
        $horaFin = $data['hora_fin'] ?? $horario->hora_fin;

        if ($horaFin <= $horaInicio) {
            return response()->json([
                'message' => 'La hora de fin debe ser posterior a la hora de inicio.',
                'errors' => [
                    'hora_inicio' => ['La hora de inicio debe ser anterior a la hora de fin.'],
                    'hora_fin' => ['La hora de fin debe ser posterior a la hora de inicio.'],
                ],
            ], 422);
        }

        $solapa = Horario::where('empleado_id', $empleadoId)
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->where('id', '!=', $horario->id)
            ->where(function ($query) use ($horaInicio, $horaFin) {
                $query->where('hora_inicio', '<', $horaFin)
                    ->where('hora_fin', '>', $horaInicio);
            })
            ->exists();
        
        if ($solapa) {
            return response()->json([
                'message' => 'El horario se solapa con otra franja existente.',
                'errors' => [
                    'hora_inicio' => ['El horario se solapa con otra franja existente.'],
                    'hora_fin' => ['El horario se solapa con otra franja existente.'],
                ],
            ], 422);
        }

        $horario->update($data);

        return $horario->fresh()->load('empleado.usuario');
    }

    /**
     * Eliminar un horario específico.
     */
    public function destroy(Horario $horario)
    {
        $horario->delete();

        return response()->json(null, 204);
    }

    public function miHorario(Request $request)
    {
        $user = $request->user();

        $empleado = Empleado::where('usuario_id', $user->id)->first();

        if (!$empleado) {
            return response()->json([
                'message' => 'No existe un perfil de empleado asociado a este usuario.'
            ], 404);
        }

        $horarios = Horario::query()
            ->where('empleado_id', $empleado->id)
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get();

        return response()->json($horarios);
    }
}
