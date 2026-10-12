<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\RegionalController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RolController;
use App\Http\Controllers\Api\PermisoController;
use App\Http\Controllers\Api\AlmacenController;



// Rutas públicas (sin autenticación)
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// Rutas protegidas (requieren autenticación)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/usuarios', [UserController::class, 'index'])
        ->middleware('permiso:usuarios.ver');
    Route::post('/usuarios', [UserController::class, 'store'])
        ->middleware('permiso:usuarios.crear');
    Route::get('/usuarios/{id}', [UserController::class, 'show'])
        ->middleware('permiso:usuarios.ver');
    Route::put('/usuarios/{id}', [UserController::class, 'update'])
        ->middleware('permiso:usuarios.editar');
    Route::delete('/usuarios/{id}', [UserController::class, 'destroy'])
        ->middleware('permiso:usuarios.eliminar');
    Route::get('/catalogos/usuarios', [UserController::class, 'catalogos'])
    ->middleware('permiso:usuarios.ver');

   Route::get('/roles', [RolController::class, 'index'])
    ->middleware('permiso:roles.ver');
Route::post('/roles', [RolController::class, 'store'])
    ->middleware('permiso:roles.crear');
Route::get('/roles/{id}', [RolController::class, 'show'])
    ->middleware('permiso:roles.ver');
Route::put('/roles/{id}', [RolController::class, 'update'])
    ->middleware('permiso:roles.editar');
Route::delete('/roles/{id}', [RolController::class, 'destroy'])
    ->middleware('permiso:roles.eliminar');
Route::put('/roles/{id}/permisos', [RolController::class, 'syncPermisos'])
    ->middleware('permiso:roles.editar');

    Route::get('/permisos', [PermisoController::class, 'index'])
    ->middleware('permiso:permisos.ver,roles.ver');
Route::post('/permisos', [PermisoController::class, 'store'])
    ->middleware('permiso:permisos.crear');
Route::put('/permisos/{id}', [PermisoController::class, 'update'])
    ->middleware('permiso:permisos.editar');
Route::delete('/permisos/{id}', [PermisoController::class, 'destroy'])
    ->middleware('permiso:permisos.eliminar');
   

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

    Route::get('/catalogos/almacenes', [AlmacenController::class, 'catalogos'])
    ->middleware('permiso:almacenes.ver');
    Route::get('/almacenes', [AlmacenController::class, 'index'])
        ->middleware('permiso:almacenes.ver');
    Route::post('/almacenes', [AlmacenController::class, 'store'])
        ->middleware('permiso:almacenes.crear');
    Route::get('/almacenes/{id}', [AlmacenController::class, 'show'])
        ->middleware('permiso:almacenes.ver');
    Route::put('/almacenes/{id}', [AlmacenController::class, 'update'])
        ->middleware('permiso:almacenes.editar');
    Route::delete('/almacenes/{id}', [AlmacenController::class, 'destroy'])   // desactiva
        ->middleware('permiso:almacenes.eliminar');
});