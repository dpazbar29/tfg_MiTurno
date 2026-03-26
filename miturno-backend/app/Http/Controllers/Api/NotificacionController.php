<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    /**
     * Obtener todas las notificaciones.
     */
    public function index()
    {
        return Notificacion::with(['reserva.usuario', 'reserva.servicio', 'usuario', 'empleado.usuario'])->get();
    }

    /**
     * Almacenar las nuevas notificaciones creadas.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'reserva_id' => 'required|exists:reservas,id',
            'usuario_id' => 'required|exists:users,id',
            'empleado_id' => 'nullable|exists:empleados,id',
            'tipo' => 'required|in:confirmacion,recordatorio',
            'enviado' => 'boolean',
        ]);

        $notificacion = Notificacion::create($data);

        return response()->json($notificacion->load(['reserva.usuario', 'reserva.servicio']), 201);
    }

    /**
     * Mostrar notificación específica.
     */
    public function show(Notificacion $notificacion)
    {
        return $notificacion->load(['reserva.usuario', 'reserva.servicio', 'usuario', 'empleado.usuario']);
    }

    /**
     * Actualizar una de las notificaciones almacenadas.
     */
    public function update(Request $request, Notificacion $notificacion)
    {
        $data = $request->validate([
            'reserva_id' => 'sometimes|exists:reservas,id',
            'usuario_id' => 'sometimes|exists:users,id',
            'empleado_id' => 'sometimes|nullable|exists:empleados,id',
            'tipo' => 'sometimes|in:confirmacion,recordatorio',
            'enviado' => 'sometimes|boolean',
        ]);

        $notificacion->update($data);

        return $notificacion->load(['reserva.usuario', 'reserva.servicio']);
    }

    /**
     * Elimina una notificación específica.
     */
    public function destroy(Notificacion $notificacion)
    {
        $notificacion->delete();

        return response()->json(null, 204);
    }
}
