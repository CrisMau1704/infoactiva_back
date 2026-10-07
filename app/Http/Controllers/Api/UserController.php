<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;                          // ✅ unificado
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('rol');           // ✅ trae el rol anidado

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

        $ordenarPor = $request->get('ordenar_por', 'id_usuario');
        $direccion  = $request->get('direccion', 'desc');
        $query->orderBy($ordenarPor, $direccion);

        $usuarios = $query->paginate($request->get('por_pagina', 10));

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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre'      => 'required|string|max:150',
            'email'       => 'required|email|max:150|unique:usuarios,email',
            'password'    => 'required|string|min:6|max:255',
            'id_rol'      => 'required|integer|exists:roles,id_rol',   // ✅ valida que exista
            'id_regional' => 'nullable|integer',
            'id_almacen'  => 'nullable|integer',
            'estado'      => 'nullable|in:activo,inactivo',
        ], [
            'nombre.required'   => 'El nombre es obligatorio',
            'email.required'    => 'El email es obligatorio',
            'email.unique'      => 'Ya existe un usuario con ese email',
            'password.required' => 'La contraseña es obligatoria',
            'id_rol.exists'     => 'El rol seleccionado no existe',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $usuario = User::create([          // ✅ User, no Usuario
            'nombre'             => $request->nombre,
            'email'              => $request->email,
            'password'           => Hash::make($request->password),
            'id_rol'             => $request->id_rol,
            'id_regional'        => $request->id_regional,
            'id_almacen'         => $request->id_almacen,
            'estado'             => $request->estado ?? 'activo',
            'fecha_creacion'     => now(),
            'fecha_actualizacion'=> now(),
        ]);

        $usuario->load('rol');             // ✅ devuelve el rol

        return response()->json([
            'success' => true,
            'message' => 'Usuario creado correctamente',
            'data'    => $usuario,
        ], 201);
    }

    public function show(string $id)
    {
        $usuario = User::with('rol')->find($id);   // ✅ User

        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $usuario,
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        $usuario = User::find($id);         // ✅ User

        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre'      => 'sometimes|required|string|max:150',
            'email'       => 'sometimes|required|email|max:150|unique:usuarios,email,' . $id . ',id_usuario',
            'password'    => 'nullable|string|min:6|max:255',
            'id_rol'      => 'sometimes|required|integer|exists:roles,id_rol',
            'id_regional' => 'nullable|integer',
            'id_almacen'  => 'nullable|integer',
            'estado'      => 'sometimes|in:activo,inactivo',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $datos = $request->only([
            'nombre', 'email', 'id_rol', 'id_regional', 'id_almacen', 'estado'
        ]);

        if ($request->filled('password')) {
            $datos['password'] = Hash::make($request->password);
        }

        $datos['fecha_actualizacion'] = now();

        $usuario->update($datos);
        $usuario->load('rol');              // ✅ refresca rol

        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado correctamente',
            'data'    => $usuario,
        ], 200);
    }

    public function destroy(string $id)
    {
        $usuario = User::find($id);         // ✅ User

        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado',
            ], 404);
        }

        $usuario->delete();

        return response()->json([
            'success' => true,
            'message' => 'Usuario eliminado correctamente',
        ], 200);
    }
}