<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'id_rol',
        'id_regional',
        'id_almacen',
        'estado',
        'fecha_creacion',
        'fecha_actualizacion',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function regional()
    {
        return $this->belongsTo(Regional::class, 'id_regional', 'id_regional');
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'id_almacen', 'id_almacen');
    }

    public function tienePermiso(string $codigo): bool
    {
        if (!$this->rol) return false;

        return $this->rol->permisos()
            ->where('codigo', $codigo)
            ->exists();
    }

 
    public function permisosCodigos(): array
    {
        if (!$this->rol) return [];

        return $this->rol->permisos()->pluck('codigo')->toArray();
    }
}