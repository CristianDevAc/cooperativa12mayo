<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarifas_conceptos_ingreso', function (Blueprint $table) {
            $table->id();

            $table->foreignId('team_id')
                ->constrained('teams')
                ->restrictOnDelete();

            $table->foreignId('concepto_ingreso_id')
                ->constrained('conceptos_ingreso')
                ->restrictOnDelete();

            // Primer día del mes en que entra en vigencia.
            $table->date('vigente_desde');

            $table->decimal('importe', 12, 2);

            $table->text('observaciones')->nullable();

            $table->foreignId('creado_por')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();

            // No se permite repetir la tarifa
            // de un concepto para el mismo mes.
            $table->unique(
                ['team_id', 'concepto_ingreso_id', 'vigente_desde'],
                'tarifas_ci_equipo_concepto_fecha_unica'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarifas_conceptos_ingreso');
    }
};