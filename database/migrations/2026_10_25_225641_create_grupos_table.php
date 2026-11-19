<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupos', function (Blueprint $table) {
            $table->id();

            // Organización a la que pertenece el grupo
            $table->foreignId('team_id')
                ->constrained('teams')
                ->restrictOnDelete();

            // Datos del grupo
            $table->string('codigo', 20);
            $table->string('nombre', 100);
            $table->boolean('activo')->default(true);

            $table->timestamps();

            // No repetir códigos dentro de la misma organización
            $table->unique(['team_id', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};