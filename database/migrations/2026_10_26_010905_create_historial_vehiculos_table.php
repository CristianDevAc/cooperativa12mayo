<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_vehiculos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('team_id')
                ->constrained('teams')
                ->restrictOnDelete();

            $table->foreignId('afiliado_id')
                ->constrained('afiliados')
                ->restrictOnDelete();

            $table->foreignId('vehiculo_id')
                ->constrained('vehiculos')
                ->restrictOnDelete();

            $table->timestamp('fecha_inicio')->useCurrent();
            $table->timestamp('fecha_fin')->nullable();

            $table->string('motivo', 40)->default('ALTA');
            $table->text('observaciones')->nullable();

            $table->timestamps();

            $table->index(['team_id', 'afiliado_id']);
            $table->index(['team_id', 'vehiculo_id']);
            $table->index(['team_id', 'fecha_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_vehiculos');
    }
};