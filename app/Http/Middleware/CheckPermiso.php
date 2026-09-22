<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermiso
{
    /**
     * Verifica que el usuario autenticado tenga el permiso indicado.
     * Uso en rutas: ->middleware('permiso:clientes.ver')
     */
    public function handle(Request $request, Closure $next, string $permiso): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado',
            ], 401);
        }

        if (!$user->tienePermiso($permiso)) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permiso para realizar esta acción',
                'permiso_requerido' => $permiso,
            ], 403);
        }

        return $next($request);
    }
}