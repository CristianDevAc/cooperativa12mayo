<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recibos_otros_ingresos', function (Blueprint $table) {

            $table->foreignId('historial_vehiculo_id')
                ->nullable()
                ->constrained('historial_vehiculos')
                ->restrictOnDelete();

            // Un cambio de vehículo solo puede
            // tener un recibo asociado.
            $table->unique(
                'historial_vehiculo_id',
                'recibos_otros_cambio_vehiculo_unico'
            );
        });
    }

    public function down(): void
    {
        Schema::table('recibos_otros_ingresos', function (Blueprint $table) {

            $table->dropUnique(
                'recibos_otros_cambio_vehiculo_unico'
            );

            $table->dropConstrainedForeignId(
                'historial_vehiculo_id'
            );
        });
    }
};