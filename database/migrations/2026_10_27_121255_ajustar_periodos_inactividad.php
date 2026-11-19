<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            'ALTER TABLE periodos_inactividad_afiliados
             DROP CONSTRAINT chk_periodo_inactividad'
        );

        DB::statement(
            'ALTER TABLE periodos_inactividad_afiliados
             ADD CONSTRAINT chk_periodo_inactividad
             CHECK (
                periodo_hasta IS NULL
                OR periodo_hasta >= periodo_desde
             )'
        );
    }

    public function down(): void
    {
        DB::statement(
            'ALTER TABLE periodos_inactividad_afiliados
             DROP CONSTRAINT chk_periodo_inactividad'
        );

        DB::statement(
            'ALTER TABLE periodos_inactividad_afiliados
             ADD CONSTRAINT chk_periodo_inactividad
             CHECK (
                periodo_hasta IS NULL
                OR periodo_hasta > periodo_desde
             )'
        );
    }
};