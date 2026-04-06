<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Servicio;
use App\Models\Horario;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReservaController extends Controller
{
    /**
     * Obtener todas las reservas.
     */
    public function index()
    {
        return Reserva::with(['usuario', 'empleado.usuario', 'servicio'])->get();
    }

    /**
     * Almacenar las nuevas reservas creadas.
     * 
     * Flujo de trabajo:
     * 1. Valida los datos básicos recibidos desde la petición.
     * 2. Obtiene el servicio para conocer su duración en minutos.
     * 3. Calcula la hora de fin de la reserva a partir de la fecha_hora_inicio.
     * 4. Comprueba que el empleado tenga un horario activo y normal que cubra por completo el tramo horario.
     * 5. Busca reservas activas del mismo empleado en ese mismo día y verifica que no exista solapamiento.
     * 6. Si todo es correcto, crea la reserva con estado por defecto 'pendiente' cuando no se indique otro.
     * 
     * Reglas de negocio aplicadas:
     * - La reserva debe estar dentro del horario del empleado.
     * - No puede solaparse con otra reserva pendiente o confirmada.
     * - La duración real de la cita se toma del servicio seleccionado.
     * 
     * Respuestas:
     * - 201: reserva creada correctamente.
     * - 422: horario inválido o conflicto con otra reserva.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'empleado_id' => 'nullable|exists:empleados,id',
            'servicio_id' => 'required|exists:servicios,id',
            'fecha_hora_inicio' => 'required|date|after:now',
            'estado' => 'in:pendiente,confirmada,cancelada,completada,ausencia',
            'notas' => 'nullable|string',
        ]);

        $servicio = Servicio::findOrFail($data['servicio_id']);

        $inicio = Carbon::parse($data['fecha_hora_inicio']);
        $fin = $inicio->copy()->addMinutes($servicio->duracion_minutos);

        $diaSemana = $inicio->dayOfWeek;

        $horarioValido = Horario::where('empleado_id', $data['empleado_id'])
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->where('tipo', 'normal')
            ->where('hora_inicio', '<=', $inicio->format('H:i:s'))
            ->where('hora_fin', '>=', $fin->format('H:i:s'))
            ->exists();

        if (! $horarioValido) {
            return response()->json([
                'message' => 'La reserva está fuera del horario del empleado.'
            ], 422);
        }

        $solapa = Reserva::where('empleado_id', $data['empleado_id'])
            ->whereDate('fecha_hora_inicio', $inicio->toDateString())
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->get()
            ->contains(function ($reserva) use ($inicio, $fin) {
                $inicioExistente = Carbon::parse($reserva->fecha_hora_inicio);
                $finExistente = $inicioExistente->copy()->addMinutes($reserva->servicio->duracion_minutos);
                return $inicio < $finExistente && $fin > $inicioExistente;
            });
        
        if ($solapa) {
            return response()->json([
                'message' => 'El empleado ya tiene una reserva en ese tramo horario.'
            ], 422);
        }

        $data['estado'] = $data['estado'] ?? 'pendiente';

        $reserva = Reserva::create($data);

        return response()->json($reserva->load(['usuario', 'empleado.usuario', 'servicio']), 201);
    }

    /**
     * Mostrar reserva específica.
     */
    public function show(Reserva $reserva)
    {
        return $reserva->load(['usuario', 'empleado.usuario', 'servicio']);
    }

    /**
     * Actualiza una de las reservas guardadas.
     * 
     * Flujo de trabajo:
     * 1. Valida solo los campos enviados en la petición.
     * 2. Obtiene los valores finales que tendrá la reserva.
     * 3. Recupera el servicio final para calcular su duración.
     * 4. Comprueba que el nuevo tramo horario encaje en el horario del empleado para ese día.
     * 5. Busca posibles solapamientos con otras reservas del mismo empleado, excluyendo la reserva editada.
     * 6. Si no hay conflicto, actualiza la reserva.
     * 
     * Reglas de negocio aplicadas:
     * - Una edición no puede sacar la reserva fuera del horario del empleado.
     * - Una edición no puede generar solapamientos con otras reservas activas.
     * - La propia reserva actual se excluye de la comprobación de conflicto.
     * 
     * Respuestas:
     * - 200: reserva actualizada correctamente.
     * - 422: horario inválido o conflicto con otra reserva.
     */
    public function update(Request $request, Reserva $reserva)
    {
        $data = $request->validate([
            'usuario_id' => 'sometimes|exists:users,id',
            'empleado_id' => 'sometimes|nullable|exists:empleados,id',
            'servicio_id' => 'sometimes|exists:servicios,id',
            'fecha_hora_inicio' => 'sometimes|date',
            'estado' => 'sometimes|in:pendiente,confirmada,cancelada,completada,ausencia',
            'notas' => 'sometimes|nullable|string',
        ]);

        $nuevoEmpleadoId = $data['empleado_id'] ?? $reserva->empleado_id;
        $nuevoServicioId = $data['servicio_id'] ?? $reserva->servicio_id;
        $nuevaFechaInicio = $data['fecha_hora_inicio'] ?? $reserva->fecha_hora_inicio;

        $servicio = Servicio::findOrFail($nuevoServicioId);
        $inicio = Carbon::parse($nuevaFechaInicio);
        $fin = $inicio->copy()->addMinutes($servicio->duracion_minutos);
        $diaSemana = $inicio->dayOfWeek;

        $horarioValido = Horario::where('empleado_id', $nuevoEmpleadoId)
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->where('tipo', 'normal')
            ->where('hora_inicio', '<=', $inicio->format('H:i:s'))
            ->where('hora_fin', '>=', $fin->format('H:i:s'))
            ->exists();

        if (! $horarioValido) {
            return response()->json([
                'message' => 'La reserva está fuera del horario del empleado.'
            ], 422);
        }

        $solapa = Reserva::where('empleado_id', $nuevoEmpleadoId)
            -> whereDate('fecha_hora_inicio', $inicio->toDateString())
            ->whereIn('estado', ['pendiente', 'confirmarda'])
            ->where('id', '!=', $reserva->id)
            ->get()
            ->contains(function ($otraReserva) use ($inicio, $fin) {
                $inicioExistente = Carbon::parse($otraReserva->fecha_hora_inicio);
                $finExistente = $inicioExistente->copy()->addMinutes($otraReserva->servicio->duracion_minutos);

                return $inicio < $finExistente && $fin > $inicioExistente;
            });
        
        if ($solapa) {
            return response()->json([
                'message' => 'El empleado ya tiene una reserva en ese tramo horario.'
            ], 422);
        }

        $reserva->update($data);

        return $reserva->load(['usuario', 'empleado.usuario', 'servicio']);
    }

    /**
     * Eliminar una reserva específica.
     */
    public function destroy(Reserva $reserva)
    {
        $reserva->delete();
        
        return response()->json(null, 204);
    }
}
