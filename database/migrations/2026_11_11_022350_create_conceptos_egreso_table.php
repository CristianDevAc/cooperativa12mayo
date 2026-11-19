<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conceptos_egreso', function (Blueprint $table) {

            $table->id();

            $table->foreignId('team_id')
                ->constrained('teams')
                ->restrictOnDelete();

            $table->string('codigo', 50);

            $table->string('nombre', 150);

            // ORDINARIO u OTROS, según el informe económico.
            $table->string('grupo_reporte', 20);

            // No habilitar cobros/pagos automáticamente.
            $table->boolean('activo')->default(false);

            $table->text('descripcion')->nullable();

            $table->timestamps();

            $table->unique(
                ['team_id', 'codigo'],
                'conceptos_egreso_equipo_codigo_unico'
            );

            $table->index(
                ['team_id', 'grupo_reporte'],
                'conceptos_egreso_grupo_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conceptos_egreso');
    }
};