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
        Schema::create('almacenes', function (Blueprint $table) {
            $table->id('id_almacen');
            $table->unsignedBigInteger('id_regional');
            $table->string('nombre', 100);
            $table->string('direccion', 255);
            $table->string('estado', 20)->default('activo');
            $table->dateTime('fecha_creacion');
            $table->dateTime('fecha_actualizacion');

            $table->foreign('id_regional')
                  ->references('id_regional')
                  ->on('regionales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('almacenes');
    }
};
