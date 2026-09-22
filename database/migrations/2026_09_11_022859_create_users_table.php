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
        Schema::create('users', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('nombre', 150);
            $table->string('email', 150)->unique();
            $table->string('password', 255);
            $table->unsignedBigInteger('id_rol');
            $table->unsignedBigInteger('id_regional')->nullable();
            $table->unsignedBigInteger('id_almacen')->nullable();
            $table->string('estado', 20)->default('activo');
            $table->dateTime('fecha_creacion');
            $table->dateTime('fecha_actualizacion');

            $table->foreign('id_rol')
                  ->references('id_rol')->on('roles');

            $table->foreign('id_regional')
                  ->references('id_regional')->on('regionales');

            $table->foreign('id_almacen')
                  ->references('id_almacen')->on('almacenes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
