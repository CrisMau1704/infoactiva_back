<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AreaCliente;
use App\Models\ClienteRegional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AreaClienteController extends Controller
{
    // ========================================
    // LISTAR ÁREAS
    // ========================================
    public function index(Request $request)
    {
        $query = AreaCliente::with([
            'cliente',
            'clienteRegional.regional'
        ]);

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;

            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhereHas('cliente', function ($c) use ($buscar) {
                      $c->where('nombre', 'like', "%{$buscar}%");
                  });
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('id_cliente')) {
            $query->where('id_cliente', $request->id_cliente);
        }

        if ($request->filled('id_regional')) {
            $query->whereHas('clienteRegional', function ($q) use ($request) {
                $q->where('id_regional', $request->id_regional);
            });
        }

        $porPagina = min(
            max((int) $request->get('por_pagina', 10), 1),
            100
        );

        $areas = $query
            ->orderByDesc('id_area')
            ->paginate($porPagina);

        return response()->json([
            'success' => true,
            'data' => $areas->items(),
            'meta' => [
                'total' => $areas->total(),
                'por_pagina' => $areas->perPage(),
                'pagina_actual' => $areas->currentPage(),
                'ultima_pagina' => $areas->lastPage(),
            ]
        ], 200);
    }

    // ========================================
    // CREAR ÁREA
    // ========================================
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_cliente' => [
                'required',
                'integer',
                'exists:clientes,id_cliente'
            ],
            'id_cliente_regional' => [
                'required',
                'integer',
                'exists:cliente_regional,id_cliente_regional'
            ],
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'estado' => 'nullable|in:activo,inactivo',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que la regional pertenezca al cliente
        // y que su habilitación esté activa
        $clienteRegional = ClienteRegional::where(
            'id_cliente_regional',
            $request->id_cliente_regional
        )
        ->where('id_cliente', $request->id_cliente)
        ->where('estado', 'activo')
        ->first();

        if (!$clienteRegional) {
            return response()->json([
                'success' => false,
                'message' => 'El cliente no está habilitado en la regional seleccionada'
            ], 422);
        }

        // Evitar áreas duplicadas dentro de una misma
        // habilitación cliente-regional
        $existe = AreaCliente::where(
            'id_cliente_regional',
            $clienteRegional->id_cliente_regional
        )
        ->where('nombre', $request->nombre)
        ->exists();

        if ($existe) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'nombre' => [
                        'Ya existe un área con ese nombre para este cliente y regional'
                    ]
                ]
            ], 422);
        }

        $area = AreaCliente::create([
            'id_cliente' => $request->id_cliente,
            'id_cliente_regional' => $clienteRegional->id_cliente_regional,
            'nombre' => trim($request->nombre),
            'descripcion' => $request->descripcion,
            'estado' => $request->estado ?? 'activo',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Área creada correctamente',
            'data' => $area->load([
                'cliente',
                'clienteRegional.regional'
            ])
        ], 201);
    }

    // ========================================
    // MOSTRAR ÁREA
    // ========================================
    public function show(string $id)
    {
        $area = AreaCliente::with([
            'cliente',
            'clienteRegional.regional'
        ])->find($id);

        if (!$area) {
            return response()->json([
                'success' => false,
                'message' => 'Área no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $area
        ], 200);
    }

    // ========================================
    // ACTUALIZAR ÁREA
    // ========================================
    public function update(Request $request, string $id)
    {
        $area = AreaCliente::find($id);

        if (!$area) {
            return response()->json([
                'success' => false,
                'message' => 'Área no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'id_cliente' => 'sometimes|required|integer|exists:clientes,id_cliente',
            'id_cliente_regional' => 'sometimes|required|integer|exists:cliente_regional,id_cliente_regional',
            'nombre' => 'sometimes|required|string|max:150',
            'descripcion' => 'nullable|string',
            'estado' => 'sometimes|in:activo,inactivo',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $idCliente = $request->input(
            'id_cliente',
            $area->id_cliente
        );

        $idClienteRegional = $request->input(
            'id_cliente_regional',
            $area->id_cliente_regional
        );

        $clienteRegional = ClienteRegional::where(
            'id_cliente_regional',
            $idClienteRegional
        )
        ->where('id_cliente', $idCliente)
        ->where('estado', 'activo')
        ->first();

        if (!$clienteRegional) {
            return response()->json([
                'success' => false,
                'message' => 'La regional no corresponde al cliente o está inactiva'
            ], 422);
        }

        $nombre = trim(
            $request->input('nombre', $area->nombre)
        );

        $existe = AreaCliente::where(
            'id_cliente_regional',
            $idClienteRegional
        )
        ->where('nombre', $nombre)
        ->where('id_area', '!=', $area->id_area)
        ->exists();

        if ($existe) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'nombre' => [
                        'Ya existe un área con ese nombre para este cliente y regional'
                    ]
                ]
            ], 422);
        }

        $area->update([
            'id_cliente' => $idCliente,
            'id_cliente_regional' => $idClienteRegional,
            'nombre' => $nombre,
            'descripcion' => $request->input(
                'descripcion',
                $area->descripcion
            ),
            'estado' => $request->input(
                'estado',
                $area->estado
            )
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Área actualizada correctamente',
            'data' => $area->load([
                'cliente',
                'clienteRegional.regional'
            ])
        ], 200);
    }

    // ========================================
    // ELIMINAR ÁREA
    // ========================================
    public function destroy(string $id)
    {
        $area = AreaCliente::find($id);

        if (!$area) {
            return response()->json([
                'success' => false,
                'message' => 'Área no encontrada'
            ], 404);
        }

        // No eliminar áreas que ya tengan cajas asociadas
        if ($area->cajas()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el área porque tiene cajas asociadas'
            ], 409);
        }

        $area->delete();

        return response()->json([
            'success' => true,
            'message' => 'Área eliminada correctamente'
        ], 200);
    }
}

