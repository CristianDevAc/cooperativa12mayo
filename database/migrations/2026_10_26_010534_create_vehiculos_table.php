<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('team_id')
                ->constrained('teams')
                ->restrictOnDelete();

            $table->string('placa', 20);
            $table->string('marca', 80)->nullable();
            $table->string('modelo', 80)->nullable();
            $table->string('tipo', 50)->nullable();
            $table->unsignedSmallInteger('anio')->nullable();
            $table->string('color', 50)->nullable();

            $table->boolean('activo')->default(true);

            $table->timestamps();

            $table->unique(['team_id', 'placa']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};