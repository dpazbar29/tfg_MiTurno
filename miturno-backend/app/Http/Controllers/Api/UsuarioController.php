<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    /**
     * Obtener todos los usuarios.
     */
    public function index()
    {
        return User::all();
    }

    /**
     * Almacenar los nuevos usuarios creados.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'rol' => 'required|in:cliente,empleado,admin',
            'telefono' => 'nullable|string|max:255',
            'activo' => 'boolean',
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return response()->json($user,201);
    }

    /**
     * Mostrar usuario específico.
     */
    public function show(User $user)
    {
        return $user;
    }

    /**
     * Actualizar uno de los usuarios almacenados.
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'apellidos' => 'sometimes|required|string|max:255',
            'fecha_nacimiento' => 'sometimes|nullable|date',
            'email' => ['sometimes', 'required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'sometimes|string|min:6',
            'rol' => 'sometimes|in:cliente,empleado,admin',
            'telefono' => 'sometimes|nullable|string|max:255',
            'activo' => 'sometimes|boolean',
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return $user;
    }

    /**
     * Eliminar un usuario específico.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(null, 204);
    }

    public function buscarClientes(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $clientes = User::query()
            ->select('id', 'nombre', 'apellidos', 'email', 'telefono')
            ->where('rol', 'cliente')
            ->where('activo', true)
            ->where(function ($query) use ($q) {
                $query->where('nombre', 'like', "%{$q}%")
                    ->orWhere('apellidos', 'like', "%{$q}%")
                    ->orWhereRaw("CONCAT(nombre, ' ', apellidos) like ?", ["%{$q}%"])
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('telefono', 'like', "%{$q}%");
            })
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->limit(10)
            ->get();
        
        return response()->json($clientes);
    }
}
