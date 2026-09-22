<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
 
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }


        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas',
            ], 401);
        }


        if ($user->estado !== 'activo') {
            return response()->json([
                'success' => false,
                'message' => 'El usuario no está activo',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->load('rol');

        return response()->json([
            'success' => true,
            'message' => 'Login exitoso',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id_usuario' => $user->id_usuario,
                'nombre' => $user->nombre,
                'email' => $user->email,
                'rol' => $user->rol ? [
                    'id_rol' => $user->rol->id_rol,
                    'nombre' => $user->rol->nombre,
                ] : null,
                'permisos' => $user->permisosCodigos(),
            ],
        ], 200);
    }

 
    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('rol', 'regional', 'almacen');

        return response()->json([
            'success' => true,
            'user' => [
                'id_usuario' => $user->id_usuario,
                'nombre' => $user->nombre,
                'email' => $user->email,
                'estado' => $user->estado,
                'rol' => $user->rol,
                'regional' => $user->regional,
                'almacen' => $user->almacen,
                'permisos' => $user->permisosCodigos(),
            ],
        ], 200);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada correctamente',
        ], 200);
    }
}