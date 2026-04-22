<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\ServicioController;
use App\Http\Controllers\Api\EmpleadoController;
use App\Http\Controllers\Api\HorarioController;
use App\Http\Controllers\Api\ReservaController;
use App\Http\Controllers\Api\NotificacionController;

// AUTH PÚBLICO
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// AUTH PROTEGIDO
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    Route::get('catalogo-servicios', [ServicioController::class, 'catalogo']);
    Route::get('servicios/{servicio}/empleados', [ServicioController::class, 'empleados']);
    Route::patch('reservas/{reserva}/cancelar', [ReservaController::class, 'cancelar']);
    Route::get('disponibilidad', [ReservaController::class, 'disponibilidad']);

    // CLIENTE: sus propias reservas
    Route::get('mis-reservas', [ReservaController::class, 'indexCliente']);
    Route::post('reservas', [ReservaController::class, 'store']);
    Route::get('reservas/{reserva}', [ReservaController::class, 'show']);

    // CLIENTE: notificaciones
    Route::apiResource('notificaciones', NotificacionController::class)->only(['index', 'show']);
});

// ADMIN/EMPLEADO pueden gestionar todo
Route::middleware(['auth:sanctum', 'rol:admin,empleado'])->group(function () {
    Route::apiResource('usuarios', UsuarioController::class);
    Route::apiResource('servicios', ServicioController::class);
    Route::apiResource('empleados', EmpleadoController::class);
    Route::apiResource('horarios', HorarioController::class);
    Route::put('empleados/{empleado}/servicios', [EmpleadoController::class, 'syncServicios']);

    // RESERVAS admin/empleado
    Route::get('admin/reservas', [ReservaController::class, 'indexAdmin']);
    Route::put('reservas/{reserva}', [ReservaController::class, 'update']);
    Route::delete('reservas/{reserva}', [ReservaController::class, 'destroy']);

    // Admin ver reserva concreta 
    Route::get('admin/reservas/{reserva}', [ReservaController::class, 'show']);

    // NOTIFICACIONES admin/empleado
    Route::apiResource('notificaciones', NotificacionController::class)->except(['index', 'show']);
});
