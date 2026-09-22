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
        Schema::create('rol_permiso', function (Blueprint $table) {
            $table->id('id_rol_permiso');
            $table->unsignedBigInteger('id_rol');
            $table->unsignedBigInteger('id_permiso');
            $table->dateTime('fecha_creacion');
            $table->dateTime('fecha_actualizacion');

            $table->foreign('id_rol')
                  ->references('id_rol')->on('roles');

            $table->foreign('id_permiso')
                  ->references('id_permiso')->on('permisos');

            // Evita duplicados: un rol no puede tener el mismo permiso dos veces
            $table->unique(['id_rol', 'id_permiso']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rol_permiso');
    }
};
