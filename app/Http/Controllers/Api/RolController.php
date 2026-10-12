<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RolController extends Controller
{
   
    public function index(): JsonResponse
    {
        $roles = Rol::withCount(['usuarios', 'permisos'])->orderBy('nivel')->get();

        return response()->json(['success' => true, 'data' => $roles], 200);
    }

  
    public function show(string $id): JsonResponse
    {
        $rol = Rol::with('permisos')->find($id);

        if (! $rol) {
            return $this->noEncontrado();
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id_rol'       => $rol->id_rol,
                'nombre'       => $rol->nombre,
                'descripcion'  => $rol->descripcion,
                'nivel'        => $rol->nivel,
                'permisos_ids' => $rol->permisos->pluck('id_permiso')->values(),
            ],
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = $this->validador($request);

        if ($validator->fails()) {
            return $this->errorValidacion($validator);
        }

        $rol = Rol::create([
            'nombre'              => $request->nombre,
            'descripcion'         => $request->descripcion,
            'nivel'               => $request->nivel,
            'fecha_creacion'      => now(),
            'fecha_actualizacion' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rol creado correctamente',
            'data'    => $rol,
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $rol = Rol::find($id);

        if (! $rol) {
            return $this->noEncontrado();
        }

        $validator = $this->validador($request, $id);

        if ($validator->fails()) {
            return $this->errorValidacion($validator);
        }

        $rol->update([
            'nombre'              => $request->nombre,
            'descripcion'         => $request->descripcion,
            'nivel'               => $request->nivel,
            'fecha_actualizacion' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rol actualizado correctamente',
            'data'    => $rol,
        ], 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $rol = Rol::find($id);

        if (! $rol) {
            return $this->noEncontrado();
        }

        if ($rol->usuarios()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: existen usuarios con este rol',
            ], 422);
        }

        DB::transaction(function () use ($rol) {
            $rol->permisos()->detach();
            $rol->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Rol eliminado correctamente',
        ], 200);
    }

  
    public function syncPermisos(Request $request, string $id): JsonResponse
    {
        $rol = Rol::find($id);

        if (! $rol) {
            return $this->noEncontrado();
        }

        $validator = Validator::make($request->all(), [
            'permisos'   => ['present', 'array'],
            'permisos.*' => ['integer', 'distinct', 'exists:permisos,id_permiso'],
        ], [
            'present'  => 'Debes enviar la lista de permisos',
            'array'    => 'La lista de permisos no es válida',
            'integer'  => 'Hay un permiso que no es válido',
            'distinct' => 'Hay permisos repetidos',
            'exists'   => 'Hay un permiso que no existe',
        ]);

        if ($validator->fails()) {
            return $this->errorValidacion($validator);
        }

        $ids = $request->permisos;

        DB::transaction(function () use ($rol, $ids) {
            $actuales = $rol->permisos()->pluck('permisos.id_permiso')->all();

            // Quita los que ya no están marcados
            $rol->permisos()->detach(array_diff($actuales, $ids));

            // Agrega los nuevos (la tabla pide las fechas)
            $nuevos = collect(array_diff($ids, $actuales))
                ->mapWithKeys(fn ($idPermiso) => [
                    $idPermiso => ['fecha_creacion' => now(), 'fecha_actualizacion' => now()],
                ])
                ->all();

            $rol->permisos()->attach($nuevos);
        });

        return response()->json([
            'success' => true,
            'message' => 'Permisos actualizados correctamente',
        ], 200);
    }


    private function validador(Request $request, ?string $id = null)
    {
        return Validator::make($request->all(), [
            'nombre'      => ['required', 'string', 'max:50', Rule::unique('roles', 'nombre')->ignore($id, 'id_rol')],
            'descripcion' => ['required', 'string', 'max:255'],
            'nivel'       => ['required', 'integer', 'between:1,255'],
        ], [
            'required' => 'El campo :attribute es obligatorio',
            'unique'   => 'Ya existe un rol con ese :attribute',
            'max'      => 'El campo :attribute no puede superar :max caracteres',
            'integer'  => 'El campo :attribute debe ser un número entero',
            'between'  => 'El campo :attribute debe estar entre :min y :max',
        ], [
            'descripcion' => 'descripción',
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
            'message' => 'Rol no encontrado',
        ], 404);
    }
}