<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Servicio;
use App\Models\Horario;
use App\Models\Empleado;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class ReservaController extends Controller
{
    /**
     * Listado de reservas para cliente.
     *
     * Devuelve únicamente las reservas del usuario autenticado.
     * Ordenadas por fecha de inicio y paginadas.
     */
    public function indexCliente(Request $request)
    {
        $user = $request->user();

        $reservas = Reserva::with([
                'servicio:id,nombre,duracion_minutos,precio',
                'empleado.usuario:id,nombre,apellidos',
                'usuario:id,nombre,apellidos,email',
            ])
            ->where('usuario_id', $user->id)
            ->orderBy('fecha_hora_inicio', 'asc')
            ->paginate(15);

        return response()->json($reservas);
    }

    /**
     * Listado de reservas para administrador.
     *
     * Permite consultar todas las reservas
     * Aplicar filtros por fecha, estado, empleado, usuario, servicio o búsqueda libre.
     */
    public function indexAdmin(Request $request)
    {
        $query = Reserva::with([
                'servicio:id,nombre,duracion_minutos,precio',
                'empleado.usuario:id,nombre,apellidos',
                'usuario:id,nombre,apellidos,email',
            ])
            ->orderBy('fecha_hora_inicio', 'desc');

        // Filtro por fecha concreta.
        if ($request->filled('fecha')) {
            $fecha = Carbon::parse($request->fecha);
            $query->whereDate('fecha_hora_inicio', $fecha->toDateString());
        }

        // Filtro por estado de la reserva.
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro por empleado.
        if ($request->filled('empleado_id')) {
            $query->where('empleado_id', $request->empleado_id);
        }

        // Filtro por usuario.
        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        // Filtro por servicio.
        if ($request->filled('servicio_id')) {
            $query->where('servicio_id', $request->servicio_id);
        }

        // Búsqueda por nombre, apellidos, email del usuario o por nombre del servicio.
        if ($request->filled('busqueda')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('usuario', function ($userQuery) use ($request) {
                    $userQuery->where('nombre', 'like', "%{$request->busqueda}%")
                              ->orWhere('apellidos', 'like', "%{$request->busqueda}%")
                              ->orWhere('email', 'like', "%{$request->busqueda}%");
                })
                ->orWhereHas('servicio', function ($servicioQuery) use ($request) {
                    $servicioQuery->where('nombre', 'like', "%{$request->busqueda}%");
                });
            });
        }

        $reservas = $query->paginate(15)->appends($request->query());

        return response()->json($reservas);
    }

    /**
     * Listado de reservas para empleado.
     *
     * Devuelve solo las reservas asignadas al empleado del usuario autenticado.
     * También acepta filtros por fecha, estado y búsqueda.
     */
    public function indexEmpleado(Request $request)
    {
        $user = $request->user()->load('empleado');

        if (! $user->empleado) {
            return response()->json([
                'message' => 'El usuario autenticado no tiene perfil de empleado.'
            ],403);
        }

        $query = Reserva::with([
            'servicio:id,nombre,duracion_minutos,precio',
            'usuario:id,nombre,apellidos,email,telefono',
            'empleado.usuario:id,nombre,apellidos'
        ])
        ->where('empleado_id', $user->empleado->id)
        ->orderBy('fecha_hora_inicio', 'asc');

        if ($request->filled('fecha')) {
            $fecha = Carbon::parse($request->fecha);
            $query->whereDate('fecha_hora_inicio', $fecha->toDateString());
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('busqueda')) {
            $busqueda = $request->busqueda;

            $query->where(function ($q) use ($busqueda) {
                $q->whereHas('usuario', function ($userQuery) use ($busqueda) {
                    $userQuery->where('nombre', 'like', "%{$busqueda}%")
                    ->orWhere('apellidos', 'like', "%{$busqueda}%")
                    ->orWhere('email', 'like', "%{$busqueda}%")
                    ->orWhere('telefono', 'like', "%{$busqueda}%");
                })
                ->orWhereHas('servicio', function ($servicioQuery) use ($busqueda) {
                    $servicioQuery->where('nombre', 'like', "%{$busqueda}%");
                });
            });
        }

        $reservas = $query->paginate(15)->appends($request->query());

        return response()->json($reservas);
    }

    /**
     * Crear una nueva reserva.
     *
     * Flujo:
     * 1. Valida los datos básicos.
     * 2. Obtiene el servicio para conocer su duración.
     * 3. Calcula la hora de finalización.
     * 4. Comprueba si el empleado tiene horario activo para ese tramo.
     * 5. Verifica que no haya solapamiento con otras reservas activas.
     * 6. Crea la reserva con estado pendiente por defecto si no se envía.
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

        // Comprueba que el empleado tenga un horario normal activo que cubra toda la cita.
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

        // Comprueba si existe otra reserva activa que se solape con la nueva.
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
     * Mostrar una reserva concreta.
     */
    public function show(Reserva $reserva)
    {
        return $reserva->load(['usuario', 'empleado.usuario', 'servicio']);
    }

    /**
     * Actualizar una reserva existente.
     *
     * Repite las comprobaciones de horario y solapamiento si cambian el empleado, la fecha o el servicio.
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

        $nuevoEmpleadoId = array_key_exists('empleado_id', $data)
            ? $data['empleado_id']
            : $reserva->empleado_id;

        $nuevoServicioId = $data['servicio_id'] ?? $reserva->servicio_id;
        $nuevaFechaInicio = $data['fecha_hora_inicio'] ?? $reserva->fecha_hora_inicio;

        $servicio = Servicio::findOrFail($nuevoServicioId);

        $inicio = Carbon::parse($nuevaFechaInicio);
        $fin = $inicio->copy()->addMinutes($servicio->duracion_minutos);
        $diaSemana = $inicio->dayOfWeek;

        if ($nuevoEmpleadoId !== null) {
            $horarioValido = Horario::where('empleado_id', $nuevoEmpleadoId)
                ->where('dia_semana', $diaSemana)
                ->where('activo', true)
                ->where('tipo', 'normal')
                ->where('hora_inicio', '<=', $inicio->format('H:i:s'))
                ->where('hora_fin', '>=', $fin->format('H:i:s'))
                ->exists();

            if (! $horarioValido) {
                return response()->json([
                    'message' => 'La reserva está fuera del horario del empleado.',
                    'errors' => [
                        'fecha_hora_inicio' => ['La reserva está fuera del horario del empleado.'],
                        'empleado_id' => ['El empleado no tiene disponibilidad para ese tramo horario.'],
                    ],
                ], 422);
            }

            $reservasMismoDia = Reserva::with('servicio')
                ->where('empleado_id', $nuevoEmpleadoId)
                ->whereDate('fecha_hora_inicio', $inicio->toDateString())
                ->whereIn('estado', ['pendiente', 'confirmada'])
                ->where('id', '!=', $reserva->id)
                ->get();

            $solapa = $reservasMismoDia->contains(function ($otraReserva) use ($inicio, $fin) {
                if (! $otraReserva->servicio) {
                    return false;
                }

                $inicioExistente = Carbon::parse($otraReserva->fecha_hora_inicio);
                $finExistente = $inicioExistente->copy()->addMinutes($otraReserva->servicio->duracion_minutos);

                return $inicio < $finExistente && $fin > $inicioExistente;
            });

            if ($solapa) {
                return response()->json([
                    'message' => 'El empleado ya tiene una reserva en ese tramo horario.',
                    'errors' => [
                        'fecha_hora_inicio' => ['El empleado ya tiene una reserva en ese tramo horario.'],
                        'empleado_id' => ['El empleado ya tiene una reserva en ese tramo horario.'],
                    ],
                ], 422);
            }
        }

        $reserva->update($data);

        return $reserva->load(['usuario', 'empleado.usuario', 'servicio']);
    }

    /**
     * Eliminar una reserva concreta.
     */
    public function destroy(Reserva $reserva)
    {
        $reserva->delete();
        
        return response()->json(null, 204);
    }

    /**
     * Consultar disponibilidad de un empleado para una fecha y servicio concretos.
     *
     * Recorre los horarios activos del día, genera huecos de 30 minutos y descarta los tramos ocupados por reservas activas.
     */
    public function disponibilidad(Request $request)
    {
        $data = $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'servicio_id' => 'required|exists:servicios,id',
            'fecha' => 'required|date',
        ]);

        $servicio = Servicio::findOrFail($data['servicio_id']);
        $duracion = $servicio->duracion_minutos;

        $fecha = Carbon::parse($data['fecha']);
        $diaSemana = $fecha->dayOfWeek;

        $horarios = Horario::where('empleado_id', $data['empleado_id'])
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->where('tipo', 'normal')
            ->orderBy('hora_inicio')
            ->get();
        
        if ($horarios->isEmpty()) {
            return response()->json([
                'fecha' => $fecha->toDateString(),
                'empleado_id' => (int) $data['empleado_id'],
                'servicio_id' => (int) $data['servicio_id'],
                'slots_disponibles' => [],
                'message' => 'El empleado no tiene horario disponible ese día.'
            ]);
        }

        $reservas = Reserva::with('servicio')
            ->where('empleado_id', $data['empleado_id'])
            ->whereDate('fecha_hora_inicio', $fecha->toDateString())
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->get();
        
        $slotsDisponibles = [];

        foreach ($horarios as $horario) {
            $inicioBloque = Carbon::parse($fecha->toDateString() . ' ' . $horario->hora_inicio);
            $finBloque = Carbon::parse($fecha->toDateString() . ' ' . $horario->hora_fin);

            $slot = $inicioBloque->copy();

            while ($slot->copy()->addMinutes($duracion) <= $finBloque) {
                
                $slotFin = $slot->copy()->addMinutes($duracion);

                $solapa = $reservas->contains(function ($reserva) use ($slot, $slotFin) {

                    $inicioExistente = Carbon::parse($reserva->fecha_hora_inicio);
                    $finExistente = $inicioExistente->copy()->addMinutes($reserva->servicio->duracion_minutos);

                    return $slot < $finExistente && $slotFin > $inicioExistente;
                });

                if (! $solapa) {
                    $slotsDisponibles[] = $slot->format('H:i');
                }

                $slot->addMinutes(30);
            }
        }

        return response()->json([
            'fecha' => $fecha->toDateString(),
            'empleado_id' => (int) $data['empleado_id'],
            'servicio_id' => (int) $data['servicio_id'],
            'duracion_minutos' => $duracion,
            'slots_disponibles' => $slotsDisponibles,
        ]);
    }

    /**
     * Cancelar una reserva.
     *
     * Solo permite cancelar la reserva al usuario propietario de la misma.
     */
    public function cancelar(Request $request, Reserva $reserva)
    {
        $user = $request->user();

        if ($reserva->usuario_id !== $user->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $reserva->estado = 'cancelada';
        $reserva->save();

        return response()->json(['message' => 'Reserva cancelada']);
    }
}
