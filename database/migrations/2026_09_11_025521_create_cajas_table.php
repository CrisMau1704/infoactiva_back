<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cajas', function (Blueprint $table) {
            $table->id('id_caja');
            $table->string('codigo', 50)->unique();
            $table->unsignedBigInteger('id_cliente')->nullable();
            $table->unsignedBigInteger('id_area')->nullable();
            $table->unsignedBigInteger('id_ubicacion')->nullable();
            $table->unsignedBigInteger('id_regional');
            $table->string('tipo', 30);
            $table->string('estado', 30)->default('activo');
            $table->string('origen', 30);
            $table->dateTime('fecha_creacion');
            $table->dateTime('fecha_actualizacion');

            $table->foreign('id_cliente')
                  ->references('id_cliente')->on('clientes');

            $table->foreign('id_area')
                  ->references('id_area')->on('area_cliente');

            $table->foreign('id_ubicacion')
                  ->references('id_ubicacion')->on('ubicaciones');

            $table->foreign('id_regional')
                  ->references('id_regional')->on('regionales');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};