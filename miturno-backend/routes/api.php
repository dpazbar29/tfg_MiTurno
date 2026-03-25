<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\ServicioController;
use App\Http\Controllers\Api\EmpleadoController;
use App\Http\Controllers\Api\HorarioController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rutas API para USUARIOS
Route::apiResource('usuarios', UsuarioController::class);

// Rutas API para SERVICIOS
Route::apiResource('servicios', ServicioController::class);

// Rutas API para EMPLEADOS
Route::apiResource('empleados', EmpleadoController::class);

// Rutas API para HORARIOS
Route::apiResource('horarios', HorarioController::class);