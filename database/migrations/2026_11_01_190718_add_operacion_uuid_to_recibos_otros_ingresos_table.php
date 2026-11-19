<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recibos_otros_ingresos', function (Blueprint $table) {
            $table->uuid('operacion_uuid')->nullable();

            $table->unique(
                ['team_id', 'operacion_uuid'],
                'recibos_otros_operacion_unica'
            );
        });
    }

    public function down(): void
    {
        Schema::table('recibos_otros_ingresos', function (Blueprint $table) {
            $table->dropUnique('recibos_otros_operacion_unica');
            $table->dropColumn('operacion_uuid');
        });
    }
};