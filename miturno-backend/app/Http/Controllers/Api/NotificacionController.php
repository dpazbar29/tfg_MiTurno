<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    /**
     * Obtener todas las notificaciones.
     *
     * Se cargan las relaciones asociadas para mostrar la información completa de la reserva, el usuario y el empleado implicados.
     */
    public function index()
    {
        return Notificacion::with(['reserva.usuario', 'reserva.servicio', 'usuario', 'empleado.usuario'])->get();
    }

    /**
     * Crear una nueva notificación.
     *
     * Valida los datos recibidos y guarda la notificación en la base de datos.
     * Después devuelve la notificación con parte de sus relaciones cargadas.
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

        // Crea la notificación con los datos validados.
        $notificacion = Notificacion::create($data);

        return response()->json($notificacion->load(['reserva.usuario', 'reserva.servicio']), 201);
    }

    /**
     * Mostrar una notificación concreta.
     */
    public function show(Notificacion $notificacion)
    {
        return $notificacion->load(['reserva.usuario', 'reserva.servicio', 'usuario', 'empleado.usuario']);
    }

    /**
     * Actualizar una notificación existente.
     *
     * Permite modificar cualquiera de sus campos principales si se envían en la petición.
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
     * Eliminar una notificación concreta.
     */
    public function destroy(Notificacion $notificacion)
    {
        $notificacion->delete();

        return response()->json(null, 204);
    }
}
