<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conceptos_ingreso', function (Blueprint $table) {

            $table->id();

            $table->foreignId('team_id')
                ->constrained('teams')
                ->restrictOnDelete();

            $table->string('codigo', 40);
            $table->string('nombre', 120);

            // FIJO o VARIABLE.
            // NULL significa que aún debe configurarse.
            $table->string('forma_importe', 20)->nullable();

            // UNICO, MENSUAL, ANUAL o POR_EVENTO.
            $table->string('periodicidad', 20)->nullable();

            // OPERATIVO, EXTRAORDINARIO o PATRIMONIAL.
            $table->string('naturaleza', 20)->nullable();

            // Se definirá para cada concepto.
            $table->boolean('requiere_afiliado')->nullable();

            // Inicialmente ningún concepto puede cobrarse.
            $table->boolean('activo')->default(false);

            $table->text('descripcion')->nullable();

            $table->timestamps();

            $table->unique(
                ['team_id', 'codigo'],
                'conceptos_ingreso_equipo_codigo_unico'
            );

            $table->index(['team_id', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conceptos_ingreso');
    }
};