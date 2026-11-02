<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'nit',
        'email',
        'telefono',
        'direccion',
        'estado',
        'fecha_creacion',
        'fecha_actualizacion',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    // Relaciones
     public function areas()
    {
        return $this->hasMany(
            AreaCliente::class,
            'id_cliente',
            'id_cliente'
        );
    }

    // Un cliente puede estar habilitado en varias regionales
    public function regionales()
    {
        return $this->belongsToMany(
            Regional::class,
            'cliente_regional',
            'id_cliente',
            'id_regional'
        )->withPivot(
            'id_cliente_regional',
            'estado',
            'fecha_creacion',
            'fecha_actualizacion'
        );
    }

    // Registros de habilitación por regional
    public function clienteRegionales()
    {
        return $this->hasMany(
            ClienteRegional::class,
            'id_cliente',
            'id_cliente'
        );
    }
}