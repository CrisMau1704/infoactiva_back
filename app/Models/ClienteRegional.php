<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteRegional extends Model
{
    protected $table = 'cliente_regional';
    protected $primaryKey = 'id_cliente_regional';

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'id_cliente',
        'id_regional',
        'estado'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function regional()
    {
        return $this->belongsTo(Regional::class, 'id_regional', 'id_regional');
    }
}