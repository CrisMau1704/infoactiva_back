<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('detalle_guias_ingreso', function (Blueprint $table) {
            $table->id('id_detalle_guia_ingreso');
            $table->unsignedBigInteger('id_guia_ingreso');
            $table->unsignedBigInteger('id_caja');
            $table->unsignedBigInteger('id_area');
            $table->string('estado', 30);
            $table->dateTime('fecha_creacion');
            $table->dateTime('fecha_actualizacion');

            $table->foreign('id_guia_ingreso')
                ->references('id_guia_ingreso')->on('guias_ingreso');

            $table->foreign('id_caja')
                ->references('id_caja')->on('cajas');
                
            $table->foreign('id_area')
                ->references('id_area')->on('area_cliente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_guias_ingreso');
    }
};
