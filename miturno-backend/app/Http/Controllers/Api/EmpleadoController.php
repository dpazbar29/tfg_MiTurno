<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmpleadoController extends Controller
{
    /**
     * Devuelve el listado de empleados.
     *
     * Carga también las relaciones con el usuario asociado y los serviciospara evitar consultas adicionales.
     */
    public function index()
    {
        return Empleado::with(['usuario', 'servicios'])->get();
    }

    /**
     * Crea un nuevo empleado junto con su usuario asociado.
     *
     * Primero valida los datos recibidos.
     * Crea el registro en users.
     * Crea el registro en empleados.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'telefono' => 'nullable|string|max:255',
            'password' => 'required|string|min:8',
            'especialidades' => 'nullable|string',
            'fecha_contratacion' => 'required|date',
            'activo' => 'required|boolean',
            'fecha_nacimiento' => 'nullable|date|before:today',
        ]);

        // Se usa transacción para evitar datos inconsistentes si falla una parte.
        $empleado = DB::transaction(function () use ($data) {

            // Se crea el usuario con rol "empleado".
            $usuario = User::create([
                'nombre' => $data['nombre'],
                'apellidos' => $data['apellidos'],
                'email' => $data['email'],
                'telefono' => $data['telefono'] ?? null,
                'password' => Hash::make($data['password']),
                'rol' => 'empleado',
                'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            ]);

            // Se crea la entidad empleado vinculada al usuario creado.
            return Empleado::create([
                'usuario_id' => $usuario->id,
                'especialidades' => $data['especialidades'] ?? null,
                'fecha_contratacion' => $data['fecha_contratacion'],
                'activo' => $data['activo'],
            ]);
        });

        // Devuelve el empleado con sus relaciones cargadas.
        return response()->json(
            $empleado->load(['usuario', 'servicios']),
            201
        );
    }

    /**
     * Muestra un empleado concreto.
     */
    public function show(Empleado $empleado)
    {
        return $empleado->load(['usuario', 'servicios']);
    }

    /**
     * Actualiza los datos de un empleado y los de su usuario.
     *
     * Permite modificar tanto la información del usuario asociado como los campos propios del empleado.
     */
    public function update(Request $request, Empleado $empleado)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'apellidos' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $empleado->usuario_id,
            'telefono' => 'sometimes|nullable|string|max:255',
            'password' => 'sometimes|nullable|string|min:8',
            'especialidades' => 'sometimes|nullable|string',
            'fecha_contratacion' => 'sometimes|required|date',
            'activo' => 'sometimes|boolean',
            'fecha_nacimiento' => 'nullable|date|before:today',
        ]);

        // Se actualizan los datos dentro de una transacción para mantener coherencia.
        DB::transaction(function () use ($data, $empleado) {

            // Si cambia algún dato del usuario asociado, se prepara un array con el valor nuevo o el actual.
            if (
                array_key_exists('nombre', $data) ||
                array_key_exists('apellidos', $data) ||
                array_key_exists('email', $data) ||
                array_key_exists('telefono', $data) ||
                array_key_exists('password', $data)
            ) {
                $usuarioData = [
                    'nombre' => $data['nombre'] ?? $empleado->usuario->nombre,
                    'apellidos' => $data['apellidos'] ?? $empleado->usuario->apellidos,
                    'email' => $data['email'] ?? $empleado->usuario->email,
                    'telefono' => array_key_exists('telefono', $data)
                        ? $data['telefono']
                        : $empleado->usuario->telefono,
                    'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
                ];

                // Si se manda contraseña, se cifra antes de guardarla.
                if (!empty($data['password'])) {
                    $usuarioData['password'] = Hash::make($data['password']);
                }

                $empleado->usuario->update($usuarioData);
            }

            // Se actualizan solo los campos propios de la tabla empleados.
            $empleado->update(collect($data)->only([
                'especialidades',
                'fecha_contratacion',
                'activo',
            ])->toArray());
        });

        return $empleado->fresh()->load(['usuario', 'servicios']);
    }

    /**
     * Elimina un empleado.
     *
     * Primero carga su usuario asociado y lo borra si existe.
     * Después elimina el empleado si no hubiera usuario relacionado.
     */
    public function destroy(Empleado $empleado)
    {
        $empleado->load('usuario');

        if ($empleado->usuario) {
            $empleado->usuario->delete();
        } else {
            $empleado->delete();
        }

        return response()->json(null, 204);
    }

    /**
     * Sincroniza los servicios asociados a un empleado.
     *
     * Recibe una lista de IDs de servicios y actualiza la relación N:M entre empleados y servicios mediante sync().
     */
    public function syncServicios(Request $request, Empleado $empleado)
    {
        $data = $request->validate([
            'servicio_ids' => 'array',
            'servicio_ids.*' => 'exists:servicios,id',
        ]);

        $empleado->servicios()->sync($data['servicio_ids'] ?? []);

        return response()->json(
            $empleado->load(['usuario', 'servicios'])
        );
    }
}