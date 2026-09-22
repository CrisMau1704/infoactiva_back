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
        Schema::create('guias_ingreso', function (Blueprint $table) {
            $table->id('id_guia_ingreso');
            $table->unsignedBigInteger('id_cliente');
            $table->unsignedBigInteger('id_regional');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_guia_salida');
            $table->dateTime('fecha_generacion');
            $table->dateTime('fecha_ingreso');
            $table->integer('cantidad_esperada');
            $table->integer('cantidad_recibida');   
            $table->string('estado', 30);
            $table->string('ruta_pdf' , 255)->nullable();
            $table->text('observaciones')->nullable();
            $table->dateTime('fecha_creacion');
            $table->dateTime('fecha_actualizacion');

              $table->foreign('id_cliente')
                  ->references('id_cliente')->on('clientes');

              $table->foreign('id_regional')
                  ->references('id_regional')->on('regionales');

              $table->foreign('id_usuario')
                  ->references('id_usuario')->on('users');

              $table->foreign('id_guia_salida')
                  ->references('id_guia_salida')->on('guias_salida');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guias_ingreso');
    }
};
