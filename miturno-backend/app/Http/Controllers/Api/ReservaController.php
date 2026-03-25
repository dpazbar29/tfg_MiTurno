<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
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
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'usuario_id' => 'required|exists:user,id',
            'empleado_id' => 'nullable|exists:empleados,id',
            'servicio_id' => 'required|exists:servicios,id',
            'fecha_hora_inicio' => 'required|date|after:now',
            'estado' => 'in:pendiente,confirmada,cancelada,completada,ausencia',
            'notas' => 'nullable|string',
        ]);

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
