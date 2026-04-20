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
     * Display a listing of the resource.
     */
    public function index()
    {
        return Empleado::with(['usuario', 'servicios'])->get();
    }

    /**
     * Store a newly created resource in storage.
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

        $empleado = DB::transaction(function () use ($data) {
            $usuario = User::create([
                'nombre' => $data['nombre'],
                'apellidos' => $data['apellidos'],
                'email' => $data['email'],
                'telefono' => $data['telefono'] ?? null,
                'password' => Hash::make($data['password']),
                'rol' => 'empleado',
                'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            ]);

            return Empleado::create([
                'usuario_id' => $usuario->id,
                'especialidades' => $data['especialidades'] ?? null,
                'fecha_contratacion' => $data['fecha_contratacion'],
                'activo' => $data['activo'],
            ]);
        });

        return response()->json(
            $empleado->load(['usuario', 'servicios']),
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Empleado $empleado)
    {
        return $empleado->load(['usuario', 'servicios']);
    }

    /**
     * Update the specified resource in storage.
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

        DB::transaction(function () use ($data, $empleado) {
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

                if (!empty($data['password'])) {
                    $usuarioData['password'] = Hash::make($data['password']);
                }

                $empleado->usuario->update($usuarioData);
            }

            $empleado->update(collect($data)->only([
                'especialidades',
                'fecha_contratacion',
                'activo',
            ])->toArray());
        });

        return $empleado->fresh()->load(['usuario', 'servicios']);
    }

    /**
     * Remove the specified resource from storage.
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