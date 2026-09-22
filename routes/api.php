<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\RegionalController;


// Rutas públicas (sin autenticación)
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas (requieren autenticación)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/clientes', [ClienteController::class, 'index'])
        ->middleware('permiso:clientes.ver');
    Route::post('/clientes', [ClienteController::class, 'store'])
        ->middleware('permiso:clientes.crear');
    Route::get('/clientes/{id}', [ClienteController::class, 'show'])
        ->middleware('permiso:clientes.ver');
    Route::put('/clientes/{id}', [ClienteController::class, 'update'])
        ->middleware('permiso:clientes.editar');
    Route::delete('/clientes/{id}', [ClienteController::class, 'destroy'])
        ->middleware('permiso:clientes.eliminar');


    Route::get('/regionales', [RegionalController::class, 'index'])
        ->middleware('permiso:regionales.ver');
    Route::post('/regionales', [RegionalController::class, 'store'])
        ->middleware('permiso:regionales.crear');
    Route::get('/regionales/{id}', [RegionalController::class, 'show'])
        ->middleware('permiso:regionales.ver');
    Route::put('/regionales/{id}', [RegionalController::class, 'update'])
        ->middleware('permiso:regionales.editar');
    Route::delete('/regionales/{id}', [RegionalController::class, 'destroy'])
        ->middleware('permiso:regionales.eliminar');
});