<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Regional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegionalController extends Controller
{
 
    public function index(Request $request)
    {
        $query = Regional::query();

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%");
                  
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Ordenamiento
        $ordenarPor = $request->get('ordenar_por', 'id_regional');
        $query->orderBy($ordenarPor);

        $porPagina = $request->get('por_pagina', 10);
        $regionales = $query->paginate($porPagina);

        return response()->json([
            'success' => true,
            'data' => $regionales->items(),
            'meta' => [
                'total' => $regionales->total(),
                'por_pagina' => $regionales->perPage(),
                'pagina_actual' => $regionales->currentPage(),
                'ultima_pagina' => $regionales->lastPage(),
            ],
        ], 200);
    }

   
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
            'ciudad' => 'required|string|max:100|unique:regionales,ciudad',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            
            
            'estado' => 'nullable|string|in:activo,inactivo',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'ciudad.required' => 'La ciudad es obligatoria',
            'ciudad.unique' => 'Ya existe una regional con esa ciudad',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $regional = Regional::create([
            'nombre' => $request->nombre,
            'ciudad' => $request->ciudad,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'estado' => $request->estado ?? 'activo',
            'fecha_creacion' => now(),
            'fecha_actualizacion' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Regional creada correctamente',
            'data' => $regional,
        ], 201);
    }

 
    public function show(string $id)
    {
        $regional = Regional::find($id);

        if (!$regional) {
            return response()->json([
                'success' => false,
                'message' => 'Regional no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $regional,
        ], 200);
    }


    public function update(Request $request, string $id)
    {
        $regional = Regional::find($id);

        if (!$regional) {
            return response()->json([
                'success' => false,
                'message' => 'Regional no encontrada',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:150',
            'ciudad' => 'sometimes|required|string|max:100|unique:regionales,ciudad,' . $id . ',id_regional',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            
            'estado' => 'sometimes|string|in:activo,inactivo',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $regional->update(array_merge(
            $request->only(['nombre', 'ciudad', 'direccion', 'telefono', 'email', 'estado']),
            ['fecha_actualizacion' => now()]
        ));

        return response()->json([
            'success' => true,
            'message' => 'Regional actualizada correctamente',
            'data' => $regional,
        ], 200);
    }

   
    public function destroy(string $id)
    {
        $regional = Regional::find($id);

        if (!$regional) {
            return response()->json([
                'success' => false,
                'message' => 'Regional no encontrada',
            ], 404);
        }

        $regional->delete();

        return response()->json([
            'success' => true,
            'message' => 'Regional eliminada correctamente',
        ], 200);
    }
}