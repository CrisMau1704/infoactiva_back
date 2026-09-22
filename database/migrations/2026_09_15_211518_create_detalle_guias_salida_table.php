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
        Schema::create('detalle_guias_salida', function (Blueprint $table) {
            $table->id('id_detalle_guia_salida');
            $table->unsignedBigInteger('id_guia_salida');
            $table->unsignedBigInteger('id_caja');
            $table->dateTime('fecha_creacion');
            $table->dateTime('fecha_actualizacion');


                $table->foreign('id_guia_salida')
                    ->references('id_guia_salida')->on('guias_salida');
    
                $table->foreign('id_caja')
                    ->references('id_caja')->on('cajas');       
         
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_guias_salida');
    }
};
