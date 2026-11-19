<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarifas_hoja_ruta', function (Blueprint $table) {

            $table->id();

            $table->foreignId('team_id')
                ->constrained('teams')
                ->cascadeOnDelete();

            // Primer día del mes desde el que rige la tarifa
            $table->date('vigente_desde');

            // Precio de la hoja de ruta
            $table->decimal('importe_base', 12, 2);

            $table->text('observaciones')->nullable();

            // Usuario que registró la tarifa
            $table->foreignId('creado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // Una tarifa por fecha de inicio y cooperativa
            $table->unique(
                ['team_id', 'vigente_desde'],
                'tarifas_hr_vigencia_unica'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarifas_hoja_ruta');
    }
};