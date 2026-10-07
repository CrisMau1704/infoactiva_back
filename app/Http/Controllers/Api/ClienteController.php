<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
    
    public function index(Request $request)
    {
        $query = Cliente::query();

        // Búsqueda por nombre o NIT
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('nit', 'like', "%{$buscar}%");
            });
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Ordenamiento
        $ordenarPor = $request->get('ordenar_por', 'id_cliente');
        $direccion = $request->get('direccion', 'desc');
        $query->orderBy($ordenarPor, $direccion);

        // Paginación (10 por defecto)
        $porPagina = $request->get('por_pagina', 10);
        $clientes = $query->paginate($porPagina);

        return response()->json([
            'success' => true,
            'data' => $clientes->items(),
            'meta' => [
                'total' => $clientes->total(),
                'por_pagina' => $clientes->perPage(),
                'pagina_actual' => $clientes->currentPage(),
                'ultima_pagina' => $clientes->lastPage(),
            ],
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
            'nit' => 'required|string|max:100|unique:clientes,nit',
            'email' => 'nullable|email|max:150',
            'telefono' => 'nullable|string|max:30',
            'direccion' => 'nullable|string|max:255',
            'estado' => 'nullable|string|in:activo,inactivo',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nit.required' => 'El NIT es obligatorio',
            'nit.unique' => 'Ya existe un cliente con ese NIT',
            'email.email' => 'El email no es válido',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $cliente = Cliente::create([
            'nombre' => $request->nombre,
            'nit' => $request->nit,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'estado' => $request->estado ?? 'activo',
            'fecha_creacion' => now(),
            'fecha_actualizacion' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cliente creado correctamente',
            'data' => $cliente,
        ], 201);
    }

    public function show(string $id)
    {
        $cliente = Cliente::with(['regionales', 'areas'])->find($id);

        if (!$cliente) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $cliente,
        ], 200);
    }

 
    public function update(Request $request, string $id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente no encontrado',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:150',
            'nit' => 'sometimes|required|string|max:100|unique:clientes,nit,' . $id . ',id_cliente',
            'email' => 'nullable|email|max:150',
            'telefono' => 'nullable|string|max:30',
            'direccion' => 'nullable|string|max:255',
            'estado' => 'sometimes|string|in:activo,inactivo',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $cliente->update(array_merge(
            $request->only(['nombre', 'nit', 'email', 'telefono', 'direccion', 'estado']),
            ['fecha_actualizacion' => now()]
        ));

        return response()->json([
            'success' => true,
            'message' => 'Cliente actualizado correctamente',
            'data' => $cliente,
        ], 200);
    }

   
    public function destroy(string $id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente no encontrado',
            ], 404);
        }

        $cliente->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cliente eliminado correctamente',
        ], 200);
    }
}