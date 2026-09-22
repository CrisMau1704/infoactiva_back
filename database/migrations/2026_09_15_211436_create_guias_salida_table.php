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
        Schema::create('guias_salida', function (Blueprint $table) {
            $table->id('id_guia_salida');
            $table->unsignedBigInteger('id_cliente')->nullable();
            $table->unsignedBigInteger('id_regional')->nullable();
            $table->unsignedBigInteger('id_usuario')->nullable(); 
            $table->dateTime('fecha_generacion');
            $table->dateTime('fecha_entrega');
            $table->unsignedInteger('cantidad_cajas'); 
            $table->unsignedInteger('rango_desde');
            $table->unsignedInteger('rango_hasta');
            $table->string('estado', 30);
            $table->string('ruta_pdf', 255)->nullable(); 
            $table->text('observaciones')->nullable();
            $table->dateTime('fecha_creacion');
            $table->dateTime('fecha_actualizacion');

          
            $table->foreign('id_cliente')
                  ->references('id_cliente')->on('clientes');

            $table->foreign('id_regional')
                  ->references('id_regional')->on('regionales');

        
            $table->foreign('id_usuario')
                  ->references('id_usuario')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guias_salida');
    }
};