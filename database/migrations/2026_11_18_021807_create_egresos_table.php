<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('egresos', function (Blueprint $table) {

            $table->id();

            $table->foreignId('team_id')
                ->constrained('teams')
                ->restrictOnDelete();

            $table->foreignId('concepto_egreso_id')
                ->constrained('conceptos_egreso')
                ->restrictOnDelete();

            // Numeración propia de comprobantes de egreso.
            $table->unsignedBigInteger('numero_comprobante');

            // Identificador para impedir registrar dos veces
            // la misma operación.
            $table->uuid('operacion_uuid');

            // Fecha efectiva de salida del dinero.
            $table->timestampTz('fecha_pago');

            // Copia histórica de los datos del concepto.
            $table->string('concepto_codigo', 50);
            $table->string('concepto_nombre', 150);
            $table->string('grupo_reporte', 20);

            // Información del pago.
            $table->string('beneficiario', 200);
            $table->string('detalle', 500);
            $table->decimal('importe', 12, 2);

            // EFECTIVO, TRANSFERENCIA, DEPOSITO u OTRO.
            $table->string('medio_pago', 20);

            $table->string('referencia_pago', 150)
                ->nullable();

            // FACTURA, RECIBO u OTRO.
            // El respaldo puede registrarse posteriormente.
            $table->string('tipo_documento', 20)
                ->nullable();

            $table->string('numero_documento', 100)
                ->nullable();

            $table->string('observaciones', 500)
                ->nullable();

            $table->string('estado', 20)
                ->default('PAGADO');

            $table->foreignId('registrado_por')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();

            $table->unique(
                ['team_id', 'numero_comprobante'],
                'egresos_numero_unico'
            );

            $table->unique(
                ['team_id', 'operacion_uuid'],
                'egresos_operacion_unica'
            );

            $table->index(
                ['team_id', 'fecha_pago'],
                'egresos_fecha_idx'
            );

            $table->index(
                ['team_id', 'concepto_egreso_id'],
                'egresos_concepto_idx'
            );

            $table->index(
                ['team_id', 'registrado_por'],
                'egresos_usuario_idx'
            );
        });

        // PostgreSQL valida también estas reglas.
        DB::statement("
            ALTER TABLE egresos
            ADD CONSTRAINT egresos_importe_positivo
            CHECK (importe > 0)
        ");

        DB::statement("
            ALTER TABLE egresos
            ADD CONSTRAINT egresos_estado_valido
            CHECK (estado IN ('PAGADO', 'ANULADO'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('egresos');
    }
};