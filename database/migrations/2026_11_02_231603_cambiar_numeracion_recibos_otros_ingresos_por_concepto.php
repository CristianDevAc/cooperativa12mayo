<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Un número puede repetirse en distintos conceptos,
        // pero nunca dentro del mismo concepto y cooperativa.
        Schema::table('recibos_otros_ingresos', function (Blueprint $table) {
            $table->unique(
                [
                    'team_id',
                    'concepto_ingreso_id',
                    'numero_recibo',
                ],
                'otros_ingresos_concepto_numero_unico'
            );
        });

        // Retirar la antigua restricción global.
        Schema::table('recibos_otros_ingresos', function (Blueprint $table) {
            $table->dropUnique('otros_ingresos_numero_unico');
        });

        // Inicializar los numeradores de cada concepto
        // a partir de los recibos existentes.
        DB::statement("
            INSERT INTO numeradores_recibo
                (team_id, tipo, ultimo_numero, created_at, updated_at)
            SELECT
                team_id,
                'CONCEPTO_' || concepto_ingreso_id::text,
                MAX(numero_recibo),
                NOW(),
                NOW()
            FROM recibos_otros_ingresos
            GROUP BY team_id, concepto_ingreso_id
            ON CONFLICT (team_id, tipo)
            DO UPDATE SET
                ultimo_numero = GREATEST(
                    numeradores_recibo.ultimo_numero,
                    EXCLUDED.ultimo_numero
                ),
                updated_at = NOW()
        ");
    }

    public function down(): void
    {
        // No restaurar la restricción anterior si ya existen
        // números repetidos entre conceptos diferentes.
        $duplicados = DB::table('recibos_otros_ingresos')
            ->select('team_id', 'numero_recibo')
            ->groupBy('team_id', 'numero_recibo')
            ->havingRaw('COUNT(*) > 1')
            ->first();

        if ($duplicados !== null) {
            throw new RuntimeException(
                'No se puede revertir la numeración por concepto: '
                . 'existen recibos con números iguales en conceptos distintos.'
            );
        }

        Schema::table('recibos_otros_ingresos', function (Blueprint $table) {
            $table->unique(
                ['team_id', 'numero_recibo'],
                'otros_ingresos_numero_unico'
            );
        });

        Schema::table('recibos_otros_ingresos', function (Blueprint $table) {
            $table->dropUnique(
                'otros_ingresos_concepto_numero_unico'
            );
        });
    }
};