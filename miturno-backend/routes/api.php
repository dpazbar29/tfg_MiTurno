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

// AUTH PROTEGIDO
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    
    // CLIENTES pueden ver/crear sus reservas y notificaciones
    Route::apiResource('reservas', ReservaController::class)->only(['index', 'store', 'show']);
    Route::apiResource('notificaciones', NotificacionController::class)->only(['index', 'show']);
});

// ADMIN/EMPLEADO pueden gestionar todo
Route::middleware(['auth:sanctum', 'rol:admin,empleado'])->group(function () {
    Route::apiResource('usuarios', UsuarioController::class);
    Route::apiResource('servicios', ServicioController::class);
    Route::apiResource('empleados', EmpleadoController::class);
    Route::apiResource('horarios', HorarioController::class);
    
    // Admin/empleado también gestionan reservas/notificaciones
    Route::apiResource('reservas', ReservaController::class)->except(['index', 'store', 'show']);
    Route::apiResource('notificaciones', NotificacionController::class)->except(['index', 'show']);
});
