<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodos_inactividad_afiliados', function (Blueprint $table) {
            $table->id();

            $table->foreignId('team_id')
                ->constrained('teams')
                ->restrictOnDelete();

            $table->foreignId('afiliado_id')
                ->constrained('afiliados')
                ->restrictOnDelete();

            // Primer mes suspendido: incluido.
            $table->date('periodo_desde');

            // Primer mes reactivado: NO incluido en la suspensión.
            // NULL significa que sigue inactivo.
            $table->date('periodo_hasta')->nullable();

            $table->text('motivo')->nullable();

            $table->foreignId('registrado_por')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('reactivado_por')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();

            $table->index([
                'team_id',
                'afiliado_id',
                'periodo_desde',
            ], 'idx_inactividad_afiliado');
        });

        // Un afiliado no puede tener dos suspensiones abiertas.
        DB::statement(
            'CREATE UNIQUE INDEX ux_inactividad_abierta
             ON periodos_inactividad_afiliados (team_id, afiliado_id)
             WHERE periodo_hasta IS NULL'
        );

        // La fecha de reactivación debe ser posterior al inicio.
        DB::statement(
            'ALTER TABLE periodos_inactividad_afiliados
             ADD CONSTRAINT chk_periodo_inactividad
             CHECK (
                periodo_hasta IS NULL
                OR periodo_hasta > periodo_desde
             )'
        );

        // Solo permitimos fechas correspondientes al primer día del mes.
        DB::statement(
            'ALTER TABLE periodos_inactividad_afiliados
             ADD CONSTRAINT chk_inactividad_mes_completo
             CHECK (
                EXTRACT(DAY FROM periodo_desde) = 1
                AND (
                    periodo_hasta IS NULL
                    OR EXTRACT(DAY FROM periodo_hasta) = 1
                )
             )'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('periodos_inactividad_afiliados');
    }
};