<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recibos_otros_ingresos', function (Blueprint $table) {

            $table->id();

            $table->foreignId('team_id')
                ->constrained('teams')
                ->restrictOnDelete();

            $table->foreignId('concepto_ingreso_id')
                ->constrained('conceptos_ingreso')
                ->restrictOnDelete();

            // Puede ser nulo para ingresos que no
            // requieren identificar a un afiliado.
            $table->foreignId('afiliado_id')
                ->nullable()
                ->constrained('afiliados')
                ->restrictOnDelete();

            // Tarifa histórica aplicada, si corresponde.
            $table->foreignId('tarifa_concepto_ingreso_id')
                ->nullable()
                ->constrained('tarifas_conceptos_ingreso')
                ->restrictOnDelete();

            // Correlativo exclusivo de otros ingresos.
            $table->unsignedBigInteger('numero_recibo');

            $table->timestampTz('fecha_cobro');

            // Período aplicable a cobros mensuales o anuales.
            $table->date('periodo')->nullable();

            // Guardamos una copia de los datos del concepto.
            // Así el recibo conserva su información histórica.
            $table->string('concepto_codigo', 40);
            $table->string('concepto_nombre', 120);

            $table->string('forma_importe', 20);
            $table->string('periodicidad', 20);
            $table->string('naturaleza', 20);

            $table->string('detalle', 500);

            // Importe realmente cobrado.
            $table->decimal('importe', 12, 2);

            $table->string('estado', 15)
                ->default('EMITIDO');

            $table->foreignId('registrado_por')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();

            $table->unique(
                ['team_id', 'numero_recibo'],
                'otros_ingresos_numero_unico'
            );

            $table->index(
                ['team_id', 'fecha_cobro'],
                'otros_ingresos_fecha_idx'
            );

            $table->index(
                ['team_id', 'concepto_ingreso_id', 'periodo'],
                'otros_ingresos_periodo_idx'
            );

            $table->index(
                ['team_id', 'afiliado_id'],
                'otros_ingresos_afiliado_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recibos_otros_ingresos');
    }
};