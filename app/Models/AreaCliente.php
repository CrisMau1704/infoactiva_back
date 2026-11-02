<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AreaCliente extends Model
{
    protected $table = 'area_cliente';

    protected $primaryKey = 'id_area';

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'id_cliente',
        'id_cliente_regional',
        'nombre',
        'descripcion',
        'estado'
    ];

    // Relación con el cliente
    public function cliente()
    {
        return $this->belongsTo(
            Cliente::class,
            'id_cliente',
            'id_cliente'
        );
    }

    // Relación con la habilitación del cliente en una regional
    public function clienteRegional()
    {
        return $this->belongsTo(
            ClienteRegional::class,
            'id_cliente_regional',
            'id_cliente_regional'
        );
    }

    public function cajas()
{
    return $this->hasMany(
        Caja::class,
        'id_area',
        'id_area'
    );
}
}
