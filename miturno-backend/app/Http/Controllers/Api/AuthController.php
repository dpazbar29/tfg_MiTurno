<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    /**
     * Registra un nuevo usuario.
     *
     * Valida los datos recibidos
     * Crea el usuario con rol por defecto "cliente"
     * Genera un token de acceso
     * Devuelve la información junto con el token.
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'telefono' => 'nullable|string|max:255',
        ]);

         // Creación del usuario con contraseña cifrada.
        $user = User::create([
            'nombre' => $data['nombre'],
            'apellidos' => $data['apellidos'],
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'rol' => 'cliente',
            'telefono' => $data['telefono'] ?? null,
            'activo' => true,
        ]);

        // Generación de token.
        $token = $user->createToken('api-token')->plainTextToken;

        // Respuesta JSON con mensaje, usuario y token.
        return response()->json([
            'message' => 'Usuario registrado correctamente.',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Autentica a un usuario existente.
     *
     * Verifica email y contraseña
     * Si son correctos genera un nuevo token.
     * Si las credenciales no coinciden lanza un error de validación.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Busca el usuario por email.
        $user = User::where('email', $request->email)->first();

        // Comprueba que el usuario exista y que la contraseña sea correcta.
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Genera token.
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login correcto',
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Devuelve el usuario autenticado actualmente.
     */
    public function me(Request $request)
    {
        return $request->user();
    }

    /**
     * Cierra la sesión del usuario autenticado.
     *
     * Elimina el token actual para invalidar el acceso.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout exitoso']);
    }

    /**
     * Actualiza los datos del usuario autenticado.
     *
     * Permite modificar nombre, apellidos, fecha de nacimiento, email, teléfono y contraseña.
     * La contraseña solo se actualiza si se envía.
     */
    public function updateMe(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'apellidos' => 'sometimes|required|string|max:255',
            'fecha_nacimiento' => 'sometimes|nullable|date|before:today',
            'email' => ['sometimes', 'required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'telefono' => 'sometimes|nullable|string|max:255',
            'password' => 'sometimes|nullable|string|min:6:confirmed',
        ]);

        // Si se envía contraseña, se cifra antes de guardarla.
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Actualiza el usuario con los campos validados.
        $user->update($data);

        return response()->json($user->fresh());
    }

    /**
     * Elimina la cuenta del usuario autenticado.
     *
     * Solo permite que usuarios con rol "cliente" borren su cuenta.
     * Antes de borrar el usuario, se elimina su token.
     */
    public function destroyMe(Request $request)
    {
        $user = $request->user();

        if (!$user->rol !== 'cliente') {
            return response()->json([
                'message' => 'Solo los clientes pueden eliminar su propia cuenta.'
            ], 403);
        }

        $user->currentAccessToken()?->delete();
        $user->delete();

        return response()->json([
            'message' => 'Cuenta eliminada correctamente.'
        ]);
    }
}
