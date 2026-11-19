<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_reincorporacion', function (Blueprint $table) {
            $table->id();

            $table->foreignId('team_id')
                ->constrained('teams')
                ->restrictOnDelete();

            $table->foreignId('afiliado_id')
                ->constrained('afiliados')
                ->restrictOnDelete();

            $table->timestampTz('fecha_solicitud')
                ->useCurrent();

            // PENDIENTE, COBRADA, FINALIZADA o CANCELADA.
            $table->string('estado', 20)
                ->default('PENDIENTE');

            $table->string('observaciones', 500)
                ->nullable();

            $table->foreignId('recibo_otro_ingreso_id')
                ->nullable()
                ->unique()
                ->constrained('recibos_otros_ingresos')
                ->restrictOnDelete();

            $table->foreignId('registrado_por')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestampTz('cerrada_en')->nullable();

            $table->timestamps();

            $table->index(
                ['team_id', 'afiliado_id', 'estado'],
                'sol_reinc_afiliado_estado_idx'
            );
        });

        // Impedir que un afiliado tenga dos solicitudes
        // pendientes o cobradas al mismo tiempo.
        DB::statement("
            CREATE UNIQUE INDEX sol_reinc_una_abierta_por_afiliado
            ON solicitudes_reincorporacion (team_id, afiliado_id)
            WHERE estado IN ('PENDIENTE', 'COBRADA')
        ");

        DB::statement("
            ALTER TABLE solicitudes_reincorporacion
            ADD CONSTRAINT sol_reinc_estado_valido
            CHECK (
                estado IN (
                    'PENDIENTE',
                    'COBRADA',
                    'FINALIZADA',
                    'CANCELADA'
                )
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_reincorporacion');
    }
};