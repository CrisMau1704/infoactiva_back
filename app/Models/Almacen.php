<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    protected $table = 'almacenes';
    protected $primaryKey = 'id_almacen';

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = ['id_regional', 'nombre', 'direccion', 'estado'];

    public function regional()
    {
        return $this->belongsTo(Regional::class, 'id_regional', 'id_regional');
    }
}