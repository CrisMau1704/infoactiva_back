<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permiso;
use Illuminate\Http\JsonResponse;

class PermisoController extends Controller
{
    public function index(): JsonResponse
    {
        $permisos = Permiso::orderBy('categoria')
            ->orderBy('codigo')
            ->get(['id_permiso', 'codigo', 'descripcion', 'categoria']);

        return response()->json(['success' => true, 'data' => $permisos]);
    }
}