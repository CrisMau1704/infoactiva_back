<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\Regional;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AlmacenController extends Controller
{
    private const ORDENABLES = ['id_almacen', 'nombre', 'direccion', 'estado'];

    public function index(Request $request): JsonResponse
    {
        $porPagina = min(max((int) $request->input('por_pagina', 10), 1), 100);

        // Carga la regional de cada almacén para mostrar su nombre
        $query = Almacen::with('regional:id_regional,nombre');

        if ($buscar = $request->input('buscar')) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('direccion', 'like', "%{$buscar}%")
                  ->orWhereHas('regional', fn ($r) => $r->where('nombre', 'like', "%{$buscar}%"));
            });
        }

        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        }

        if ($idRegional = $request->input('id_regional')) {
            $query->where('id_regional', $idRegional);
        }

        $direccion = $request->input('direccion') === 'desc' ? 'desc' : 'asc';
        $campo = $request->input('ordenar_por');

        if ($campo === 'regional') {
            // Ordena por el nombre de la regional
            $query->orderBy(
                Regional::select('nombre')->whereColumn('regionales.id_regional', 'almacenes.id_regional'),
                $direccion
            );
        } else {
            $campo = in_array($campo, self::ORDENABLES, true) ? $campo : 'id_almacen';
            $query->orderBy($campo, $direccion);
        }

        $paginado = $query->paginate($porPagina);

        return response()->json([
            'success' => true,
            'data'    => $paginado->items(),
            'meta'    => [
                'total'      => $paginado->total(),
                'page'       => $paginado->currentPage(),
                'por_pagina' => $paginado->perPage(),
                'last_page'  => $paginado->lastPage(),
            ],
        ]);
    }

    public function show($id): JsonResponse
    {
        $almacen = Almacen::with('regional:id_regional,nombre')->findOrFail($id);

        return response()->json(['success' => true, 'data' => $almacen]);
    }

    public function store(Request $request): JsonResponse
    {
        $almacen = Almacen::create($this->validar($request));

        return response()->json([
            'success' => true,
            'message' => 'Almacén creado correctamente.',
            'data'    => $almacen->load('regional:id_regional,nombre'),
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $almacen = Almacen::findOrFail($id);
        $almacen->update($this->validar($request, $id));

        return response()->json([
            'success' => true,
            'message' => 'Almacén actualizado correctamente.',
            'data'    => $almacen->load('regional:id_regional,nombre'),
        ]);
    }

    /**
     * DELETE /almacenes/{id}: desactiva el almacén (no lo borra),
     * porque las cajas, ubicaciones y usuarios dependen de él.
     */
    public function destroy($id): JsonResponse
    {
        $almacen = Almacen::findOrFail($id);
        $almacen->update(['estado' => 'inactivo']);

        return response()->json([
            'success' => true,
            'message' => 'Almacén desactivado correctamente.',
        ]);
    }

    /**
     * Regionales para el select del formulario.
     */
    public function catalogos(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'regionales' => Regional::orderBy('nombre')->get(['id_regional', 'nombre', 'estado']),
            ],
        ]);
    }

    /**
     * Valida los datos del formulario (crear y editar).
     */
    private function validar(Request $request, $id = null): array
    {
        return $request->validate([
            'id_regional' => ['required', 'integer', 'exists:regionales,id_regional'],
            // El nombre no se puede repetir dentro de la misma regional
            'nombre'      => [
                'required', 'string', 'max:100',
                Rule::unique('almacenes', 'nombre')
                    ->where('id_regional', $request->input('id_regional'))
                    ->ignore($id, 'id_almacen'),
            ],
            'direccion'   => ['required', 'string', 'max:255'],
            'estado'      => ['required', Rule::in(['activo', 'inactivo'])],
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'unique'   => 'Ya existe un almacén con ese nombre en la regional seleccionada.',
            'max'      => 'El campo :attribute no puede superar :max caracteres.',
            'exists'   => 'La :attribute seleccionada no es válida.',
            'integer'  => 'El campo :attribute no es válido.',
            'in'       => 'El :attribute seleccionado no es válido.',
        ], [
            'id_regional' => 'regional',
            'direccion'   => 'dirección',
        ]);
    }
}