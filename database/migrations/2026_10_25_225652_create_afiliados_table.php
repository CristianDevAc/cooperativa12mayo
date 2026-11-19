<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('afiliados', function (Blueprint $table) {
            $table->id();

            // Organización
            $table->foreignId('team_id')
                ->constrained('teams')
                ->restrictOnDelete();

            // Grupo al que pertenece el afiliado
            $table->foreignId('grupo_id')
                ->nullable()
                ->constrained('grupos')
                ->restrictOnDelete();

            // Identificación del afiliado
            $table->string('numero_socio', 30)->nullable();
            $table->string('nombre_completo', 180);
            $table->string('ci', 30)->nullable();

            // Contacto
            $table->string('telefono', 30)->nullable();
            $table->text('direccion')->nullable();

            // Datos administrativos
            $table->date('fecha_ingreso')->nullable();
            $table->string('estado', 20)->default('ACTIVO');
            $table->text('observaciones')->nullable();

            $table->timestamps();

            // Evitar números de socio duplicados
            $table->unique(['team_id', 'numero_socio']);

            // Evitar CI duplicados en la misma organización
            $table->unique(['team_id', 'ci']);

            // Facilitar búsquedas por grupo y estado
            $table->index(['team_id', 'grupo_id']);
            $table->index(['team_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('afiliados');
    }
};