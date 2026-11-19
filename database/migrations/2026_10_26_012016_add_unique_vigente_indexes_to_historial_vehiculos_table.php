<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Un afiliado solamente puede tener una asignación vigente.
        DB::statement('
            CREATE UNIQUE INDEX historial_unico_afiliado_vigente
            ON historial_vehiculos (team_id, afiliado_id)
            WHERE fecha_fin IS NULL
        ');

        // Un vehículo no puede estar vigente para dos afiliados.
        DB::statement('
            CREATE UNIQUE INDEX historial_unico_vehiculo_vigente
            ON historial_vehiculos (team_id, vehiculo_id)
            WHERE fecha_fin IS NULL
        ');
    }

    public function down(): void
    {
        DB::statement('
            DROP INDEX IF EXISTS historial_unico_vehiculo_vigente
        ');

        DB::statement('
            DROP INDEX IF EXISTS historial_unico_afiliado_vigente
        ');
    }
};