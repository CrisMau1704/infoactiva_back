<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubicaciones', function (Blueprint $table) {
            $table->id('id_ubicacion');
            $table->unsignedBigInteger('id_almacen');
            $table->unsignedInteger('rack');
            $table->unsignedInteger('nivel');
            $table->unsignedInteger('fila');
            $table->unsignedInteger('lado');
            $table->string('codigo', 30);
            $table->unsignedInteger('capacidad');
            $table->string('estado', 20)->default('activo');
            $table->dateTime('fecha_creacion');
            $table->dateTime('fecha_actualizacion');

            $table->foreign('id_almacen')
                  ->references('id_almacen')->on('almacenes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubicaciones');
    }
};