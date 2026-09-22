<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cliente_regional', function (Blueprint $table) {
            $table->id('id_cliente_regional');
            $table->unsignedBigInteger('id_cliente');
            $table->unsignedBigInteger('id_regional');
            $table->string('estado', 20)->default('activo');
            $table->dateTime('fecha_creacion');
            $table->dateTime('fecha_actualizacion');

            $table->foreign('id_cliente')
                  ->references('id_cliente')->on('clientes');

            $table->foreign('id_regional')
                  ->references('id_regional')->on('regionales');

            
            $table->unique(['id_cliente', 'id_regional']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_regional');
    }
};