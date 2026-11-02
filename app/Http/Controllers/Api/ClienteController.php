<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ClienteRegional;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    
 
public function index(Request $request)
{
    // Cargar clientes con sus regionales activas
    $query = Cliente::with([
        'clienteRegionales' => function ($q) {
            $q->where('estado', 'activo')
              ->with('regional');
        }
    ]);

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

    // Ordenamiento permitido
    $columnasPermitidas = [
        'id_cliente',
        'nombre',
        'nit',
        'email',
        'telefono',
        'direccion',
        'estado'
    ];

    $ordenarPor = $request->get('ordenar_por', 'id_cliente');

    if (!in_array($ordenarPor, $columnasPermitidas, true)) {
        $ordenarPor = 'id_cliente';
    }

    $direccion = strtolower(
        $request->get('direccion', 'desc')
    );

    if (!in_array($direccion, ['asc', 'desc'], true)) {
        $direccion = 'desc';
    }

    $query->orderBy($ordenarPor, $direccion);

    // Paginación
    $porPagina = min(
        max((int) $request->get('por_pagina', 10), 1),
        100
    );

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

        // Regionales seleccionadas
        'regionales' => 'required|array|min:1',
        'regionales.*' => 'required|integer|distinct|exists:regionales,id_regional',
    ], [
        'nombre.required' => 'El nombre es obligatorio',
        'nit.required' => 'El NIT es obligatorio',
        'nit.unique' => 'Ya existe un cliente con ese NIT',
        'email.email' => 'El email no es válido',
        'regionales.required' => 'Debe seleccionar al menos una regional',
        'regionales.min' => 'Debe seleccionar al menos una regional',
        'regionales.*.exists' => 'La regional seleccionada no existe',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

    $cliente = DB::transaction(function () use ($request) {

        // Registrar cliente
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

        // Registrar regionales habilitadas
        foreach ($request->regionales as $idRegional) {
            ClienteRegional::create([
                'id_cliente' => $cliente->id_cliente,
                'id_regional' => $idRegional,
                'estado' => 'activo',
            ]);
        }

        return $cliente;
    });

    return response()->json([
        'success' => true,
        'message' => 'Cliente creado correctamente',
        'data' => $cliente->load('clienteRegionales'),
    ], 201);
}


        public function show(string $id)
        {
           $cliente = Cliente::with([
    'regionales',
    'areas',
    'clienteRegionales.regional'
])->find($id);

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

        'regionales' => 'sometimes|required|array|min:1',
        'regionales.*' => 'required|integer|distinct|exists:regionales,id_regional',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

    DB::transaction(function () use ($request, $cliente) {

        // Actualizar información general
        $cliente->update(array_merge(
            $request->only([
                'nombre',
                'nit',
                'email',
                'telefono',
                'direccion',
                'estado'
            ]),
            ['fecha_actualizacion' => now()]
        ));

        // Actualizar regionales si fueron enviadas
        if ($request->exists('regionales')) {

            $regionalesSeleccionadas = $request->regionales;

            // Desactivar regionales que fueron desmarcadas
            ClienteRegional::where('id_cliente', $cliente->id_cliente)
                ->whereNotIn('id_regional', $regionalesSeleccionadas)
                ->update([
                    'estado' => 'inactivo',
                    'fecha_actualizacion' => now(),
                ]);

            // Crear o activar regionales seleccionadas
            foreach ($regionalesSeleccionadas as $idRegional) {
                ClienteRegional::updateOrCreate(
                    [
                        'id_cliente' => $cliente->id_cliente,
                        'id_regional' => $idRegional,
                    ],
                    [
                        'estado' => 'activo',
                    ]
                );
            }
        }
    });

    return response()->json([
        'success' => true,
        'message' => 'Cliente actualizado correctamente',
        'data' => $cliente->fresh()->load('clienteRegionales'),
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