
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('area_cliente', function (Blueprint $table) {
            if (!Schema::hasColumn('area_cliente', 'id_cliente_regional')) {
                $table->unsignedBigInteger('id_cliente_regional')
                    ->nullable()
                    ->after('id_cliente');
            }
        });
    }

    public function down(): void
    {
        Schema::table('area_cliente', function (Blueprint $table) {
            if (Schema::hasColumn('area_cliente', 'id_cliente_regional')) {
                $table->dropColumn('id_cliente_regional');
            }
        });
    }
};
