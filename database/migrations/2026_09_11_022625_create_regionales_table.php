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
        Schema::create('regionales', function (Blueprint $table) {
            $table->id('id_regional');
            $table->string('nombre', 100);
            $table->string('ciudad', 100);
            $table->string('direccion', 255);
            $table->string('telefono', 30);
            $table->string('email', 150);
            $table->string('estado', 20)->default('activo');
            $table->dateTime('fecha_creacion');
            $table->dateTime('fecha_actualizacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regionales');
    }
};
