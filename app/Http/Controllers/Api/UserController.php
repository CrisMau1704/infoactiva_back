<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\Regional;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Columnas por las que se permite ordenar
    private const ORDENABLES = ['id_usuario', 'nombre', 'email', 'estado'];

    // Relaciones que muestra la tabla (rol, regional y almacén)
    private const RELACIONES = [
        'rol:id_rol,nombre',
        'regional:id_regional,nombre',
        'almacen:id_almacen,nombre',
    ];

    public function index(Request $request): JsonResponse
    {
        $query = User::with(self::RELACIONES);

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('id_rol')) {
            $query->where('id_rol', $request->id_rol);
        }

        $ordenarPor = in_array($request->get('ordenar_por'), self::ORDENABLES, true)
            ? $request->get('ordenar_por')
            : 'id_usuario';
        $direccion = $request->get('direccion') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($ordenarPor, $direccion);

        $porPagina = min(max((int) $request->get('por_pagina', 10), 1), 100);
        $usuarios = $query->paginate($porPagina);

        return response()->json([
            'success' => true,
            'data'    => $usuarios->items(),
            'meta'    => [
                'total'         => $usuarios->total(),
                'por_pagina'    => $usuarios->perPage(),
                'pagina_actual' => $usuarios->currentPage(),
                'ultima_pagina' => $usuarios->lastPage(),
            ],
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = $this->validador($request);

        if ($validator->fails()) {
            return $this->errorValidacion($validator);
        }

        $usuario = User::create([
            'nombre'              => $request->nombre,
            'email'               => $request->email,
            'password'            => Hash::make($request->password),
            'id_rol'              => $request->id_rol,
            'id_regional'         => $request->id_regional,
            'id_almacen'          => $request->id_almacen,
            'estado'              => $request->estado ?? 'activo',
            'fecha_creacion'      => now(),
            'fecha_actualizacion' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuario creado correctamente',
            'data'    => $usuario->load(self::RELACIONES),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $usuario = User::with(self::RELACIONES)->find($id);

        if (! $usuario) {
            return $this->noEncontrado();
        }

        return response()->json(['success' => true, 'data' => $usuario], 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $usuario = User::find($id);

        if (! $usuario) {
            return $this->noEncontrado();
        }

        $validator = $this->validador($request, $id);

        if ($validator->fails()) {
            return $this->errorValidacion($validator);
        }

        $esElMismo = (int) $usuario->id_usuario === (int) $request->user()->id_usuario;

        if ($request->estado === 'inactivo' && $esElMismo) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes desactivar tu propio usuario',
            ], 422);
        }

        $datos = $request->only(['nombre', 'email', 'id_rol', 'id_regional', 'id_almacen', 'estado']);

        if ($request->filled('password')) {
            $datos['password'] = Hash::make($request->password);
        }

        $datos['fecha_actualizacion'] = now();

        $usuario->update($datos);

        // Cierra sus sesiones si lo desactivaron o le cambiaron la contraseña o el rol,
        // para que al volver a entrar cargue los permisos actualizados.
        $cambioSensible = $usuario->estado === 'inactivo'
            || $usuario->wasChanged(['password', 'id_rol']);

        if ($cambioSensible && ! $esElMismo) {
            $usuario->tokens()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado correctamente',
            'data'    => $usuario->load(self::RELACIONES),
        ], 200);
    }

    /**
     * DELETE /usuarios/{id}: desactiva el usuario (no lo borra de la base de datos).
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $usuario = User::find($id);

        if (! $usuario) {
            return $this->noEncontrado();
        }

        if ((int) $usuario->id_usuario === (int) $request->user()->id_usuario) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes desactivar tu propio usuario',
            ], 422);
        }

        $usuario->update([
            'estado'              => 'inactivo',
            'fecha_actualizacion' => now(),
        ]);
        $usuario->tokens()->delete(); // lo desconecta de inmediato

        return response()->json([
            'success' => true,
            'message' => 'Usuario desactivado correctamente',
        ], 200);
    }

    /**
     * Listas para los desplegables del formulario.
     */
    public function catalogos(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'roles'      => Rol::orderBy('nombre')->get(['id_rol', 'nombre']),
                'regionales' => Regional::orderBy('nombre')->get(['id_regional', 'nombre', 'estado']),
                'almacenes'  => Almacen::orderBy('nombre')->get(['id_almacen', 'id_regional', 'nombre', 'estado']),
            ],
        ], 200);
    }

    // ============================================
    // Auxiliares
    // ============================================

    /**
     * Validación común para crear (sin $id) y editar (con $id).
     */
    private function validador(Request $request, ?string $id = null)
    {
        $esCreacion = $id === null;

        $validator = Validator::make($request->all(), [
            'nombre'      => ['required', 'string', 'max:150'],
            'email'       => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($id, 'id_usuario')],
            // En creación es obligatoria; al editar, vacía significa "no cambiar"
            'password'    => [
                $esCreacion ? 'required' : 'nullable',
                'string', 'min:8', 'max:100',
                'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/',
            ],
            'id_rol'      => ['required', 'integer', 'exists:roles,id_rol'],
            'id_regional' => ['nullable', 'integer', 'exists:regionales,id_regional'],
            'id_almacen'  => ['nullable', 'integer', 'exists:almacenes,id_almacen'],
            'estado'      => ['nullable', Rule::in(['activo', 'inactivo'])],
        ], [
            'required'       => 'El campo :attribute es obligatorio',
            'email'          => 'Ingresa un correo electrónico válido',
            'unique'         => 'Ya existe un usuario con ese :attribute',
            'max'            => 'El campo :attribute no puede superar :max caracteres',
            'min'            => 'El campo :attribute debe tener al menos :min caracteres',
            'exists'         => 'El :attribute seleccionado no existe',
            'in'             => 'El :attribute seleccionado no es válido',
            'integer'        => 'El campo :attribute no es válido',
            'password.regex' => 'La contraseña debe incluir letras y números',
        ], [
            'email'       => 'correo electrónico',
            'password'    => 'contraseña',
            'id_rol'      => 'rol',
            'id_regional' => 'regional',
            'id_almacen'  => 'almacén',
        ]);

        // El almacén elegido debe pertenecer a la regional elegida
        $validator->after(function ($v) use ($request) {
            if ($request->filled('id_regional') && $request->filled('id_almacen')) {
                $pertenece = Almacen::where('id_almacen', $request->id_almacen)
                    ->where('id_regional', $request->id_regional)
                    ->exists();

                if (! $pertenece) {
                    $v->errors()->add('id_almacen', 'El almacén no pertenece a la regional seleccionada');
                }
            }
        });

        return $validator;
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
            'message' => 'Usuario no encontrado',
        ], 404);
    }
}