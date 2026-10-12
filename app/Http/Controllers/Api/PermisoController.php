<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PermisoController extends Controller
{
    /**
     * Devuelve todos los permisos (son pocos). El filtrado, el orden y la
     * paginación de la tabla se hacen en el navegador.
     * También lo usa la pantalla de Roles para mostrar las casillas.
     */
    public function index(): JsonResponse
    {
        $permisos = Permiso::withCount('roles')
            ->orderBy('categoria')
            ->orderBy('codigo')
            ->get();

        return response()->json(['success' => true, 'data' => $permisos], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = $this->validador($request);

        if ($validator->fails()) {
            return $this->errorValidacion($validator);
        }

        $permiso = DB::transaction(function () use ($request) {
            $permiso = Permiso::create([
                'codigo'              => $request->codigo,
                'descripcion'         => $request->descripcion,
                'categoria'           => $request->categoria,
                'fecha_creacion'      => now(),
                'fecha_actualizacion' => now(),
            ]);

            // El Administrador tiene acceso total: recibe el permiso nuevo automáticamente
            $idAdmin = Rol::where('nombre', 'Administrador')->value('id_rol');
            if ($idAdmin) {
                $permiso->roles()->attach($idAdmin, [
                    'fecha_creacion'      => now(),
                    'fecha_actualizacion' => now(),
                ]);
            }

            return $permiso;
        });

        return response()->json([
            'success' => true,
            'message' => 'Permiso creado correctamente',
            'data'    => $permiso,
        ], 201);
    }

    /**
     * Solo se editan la descripción y la categoría.
     * El código no cambia porque las rutas del sistema lo usan (ej: permiso:cajas.ver).
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $permiso = Permiso::find($id);

        if (! $permiso) {
            return $this->noEncontrado();
        }

        $validator = $this->validador($request, $id);

        if ($validator->fails()) {
            return $this->errorValidacion($validator);
        }

        $permiso->update([
            'descripcion'         => $request->descripcion,
            'categoria'           => $request->categoria,
            'fecha_actualizacion' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permiso actualizado correctamente',
            'data'    => $permiso,
        ], 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $permiso = Permiso::find($id);

        if (! $permiso) {
            return $this->noEncontrado();
        }

        // Si otro rol (que no sea el Administrador) lo usa, no se puede eliminar
        $idAdmin = Rol::where('nombre', 'Administrador')->value('id_rol');
        $enUso = $permiso->roles()->where('roles.id_rol', '<>', $idAdmin ?? 0)->exists();

        if ($enUso) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: el permiso está asignado a otros roles. Quítalo primero desde Roles.',
            ], 422);
        }

        DB::transaction(function () use ($permiso) {
            $permiso->roles()->detach();
            $permiso->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Permiso eliminado correctamente',
        ], 200);
    }

    // ============================================
    // Auxiliares
    // ============================================

    /**
     * Validación para crear (sin $id) y editar (con $id).
     */
    private function validador(Request $request, ?string $id = null)
    {
        $reglas = [
            'descripcion' => ['required', 'string', 'max:255'],
            'categoria'   => ['required', 'string', 'max:100'],
        ];

        // El código solo se valida al crear
        if ($id === null) {
            $reglas['codigo'] = [
                'required', 'string', 'max:100',
                'regex:/^[a-z_]+\.[a-z_]+$/',
                'unique:permisos,codigo',
            ];
        }

        return Validator::make($request->all(), $reglas, [
            'required'     => 'El campo :attribute es obligatorio',
            'max'          => 'El campo :attribute no puede superar :max caracteres',
            'unique'       => 'Ya existe un permiso con ese :attribute',
            'codigo.regex' => 'El código debe tener el formato modulo.accion, en minúsculas (ej: cajas.ver)',
        ], [
            'codigo'      => 'código',
            'descripcion' => 'descripción',
            'categoria'   => 'categoría',
        ]);
    }

    private function errorValidacion($validator): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Hay campos con errores',
            'errors'  => $validator->errors(),
        ], 422);
    }

    private function noEncontrado(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Permiso no encontrado',
        ], 404);
    }
}