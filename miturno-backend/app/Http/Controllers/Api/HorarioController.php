<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Horario;
use App\Models\Empleado;
use Illuminate\Http\Request;

class HorarioController extends Controller
{
    /**
     * Obtener todos los horarios registrados.
     *
     * Se cargan también las relaciones del empleado y su usuario asociado
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
     * Crear un nuevo horario.
     *
     * Valida los datos de entrada
     * Comprueba que no exista solapamiento con otro horario activo del mismo empleado en el mismo día,
     * Guarda el nuevo registro.
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

        // Comprueba si ya existe una franja horaria activa que se solape con la nueva para el mismo empleado y día.
        $solapa = Horario::where('empleado_id', $data['empleado_id'])
            ->where('dia_semana', $data['dia_semana'])
            ->where('activo', true)
            ->where(function ($query) use ($data) {
                $query->where('hora_inicio', '<', $data['hora_fin'])
                    ->where('hora_fin', '>', $data['hora_inicio']);
            })
            ->exists();
        
        // Si existe solapamiento, se devuelve un error de validación.
        if ($solapa) {
            return response()->json([
                'message' => 'El horario se solapa con otra franja existente.',
                'errors' => [
                    'hora_inicio' => ['El horario se solapa con otra franja existente.'],
                    'hora_fin' => ['El horario se solapa con otra franja existente.'],
                ],
            ], 422);
        }

        // Crea el horario con los datos validados.
        $horario = Horario::create($data);

        return response()->json($horario->load('empleado.usuario'), 201);
    }

    /**
     * Mostrar un horario concreto.
     */
    public function show(Horario $horario)
    {
        return $horario->load('empleado.usuario');
    }

    /**
     * Actualizar un horario existente.
     *
     * Se validan solo los campos enviados
     * Se reconstruyen los valores finales
     * Comprueba que la franja sigue siendo válida y que no se solapa
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

        // Se toman los nuevos valores si vienen en la petición, en caso contrario, se usan los actuales del horario.
        $empleadoId = $data['empleado_id'] ?? $horario->empleado_id;
        $diaSemana = $data['dia_semana'] ?? $horario->dia_semana;
        $horaInicio = $data['hora_inicio'] ?? $horario->hora_inicio;
        $horaFin = $data['hora_fin'] ?? $horario->hora_fin;

        // Se comprueba manualmente que la hora de fin sea posterior a la de inicio.
        if ($horaFin <= $horaInicio) {
            return response()->json([
                'message' => 'La hora de fin debe ser posterior a la hora de inicio.',
                'errors' => [
                    'hora_inicio' => ['La hora de inicio debe ser anterior a la hora de fin.'],
                    'hora_fin' => ['La hora de fin debe ser posterior a la hora de inicio.'],
                ],
            ], 422);
        }

        // Comprueba si la nueva franja se solapa con otro horario activo, excluyendo el propio horario que se está actualizando.
        $solapa = Horario::where('empleado_id', $empleadoId)
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->where('id', '!=', $horario->id)
            ->where(function ($query) use ($horaInicio, $horaFin) {
                $query->where('hora_inicio', '<', $horaFin)
                    ->where('hora_fin', '>', $horaInicio);
            })
            ->exists();
        
        // Si hay conflicto con otro horario, devuelve error de validación.
        if ($solapa) {
            return response()->json([
                'message' => 'El horario se solapa con otra franja existente.',
                'errors' => [
                    'hora_inicio' => ['El horario se solapa con otra franja existente.'],
                    'hora_fin' => ['El horario se solapa con otra franja existente.'],
                ],
            ], 422);
        }

        // Actualiza el horario con los campos recibidos.
        $horario->update($data);

        return $horario->fresh()->load('empleado.usuario');
    }

    /**
     * Eliminar un horario concreto.
     */
    public function destroy(Horario $horario)
    {
        $horario->delete();

        return response()->json(null, 204);
    }

    /**
     * Obtener los horarios del empleado autenticado.
     *
     * Busca primero el perfil de empleado asociado al usuario actual.
     * Si existe, devuelve todos sus horarios ordenados por día y hora.
     */
    public function miHorario(Request $request)
    {
        $user = $request->user();

        // Busca el empleado vinculado al usuario autenticado.
        $empleado = Empleado::where('usuario_id', $user->id)->first();

        // Si el usuario no tiene perfil de empleado, devuelve error.
        if (!$empleado) {
            return response()->json([
                'message' => 'No existe un perfil de empleado asociado a este usuario.'
            ], 404);
        }

        // Obtiene todos los horarios del empleado ordenados por día y hora.
        $horarios = Horario::query()
            ->where('empleado_id', $empleado->id)
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get();

        return response()->json($horarios);
    }
}
