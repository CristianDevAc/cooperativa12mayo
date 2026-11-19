<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Numeración de recibos
        |--------------------------------------------------------------------------
        */

        Schema::create('numeradores_recibo', function (Blueprint $table) {

            $table->id();

            $table->foreignId('team_id')
                ->constrained('teams')
                ->restrictOnDelete();

            $table->string('tipo', 30);

            $table->unsignedBigInteger('ultimo_numero')
                ->default(0);

            $table->timestamps();

            $table->unique(
                ['team_id', 'tipo'],
                'numeradores_recibo_tipo_unico'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Cabecera de recibos
        |--------------------------------------------------------------------------
        */

        Schema::create('recibos_hoja_ruta', function (Blueprint $table) {

            $table->id();

            $table->foreignId('team_id')
                ->constrained('teams')
                ->restrictOnDelete();

            $table->foreignId('afiliado_id')
                ->constrained('afiliados')
                ->restrictOnDelete();

            $table->unsignedBigInteger('numero_recibo');

            $table->timestampTz('fecha_cobro');

            // Suma de las mensualidades
            $table->decimal('subtotal', 12, 2);

            // Se cobra una sola vez por operación
            $table->decimal('multa_unica', 12, 2)
                ->default(0);

            $table->decimal('total', 12, 2);

            // EMITIDO o ANULADO
            $table->string('estado', 15)
                ->default('EMITIDO');

            $table->foreignId('registrado_por')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestampTz('anulado_en')
                ->nullable();

            $table->foreignId('anulado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('motivo_anulacion')
                ->nullable();

            $table->timestamps();

            $table->unique(
                ['team_id', 'numero_recibo'],
                'recibos_hr_numero_unico'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Detalle de mensualidades pagadas
        |--------------------------------------------------------------------------
        */

        Schema::create('recibo_hoja_ruta_detalles', function (Blueprint $table) {

            $table->id();

            $table->foreignId('team_id')
                ->constrained('teams')
                ->restrictOnDelete();

            $table->foreignId('recibo_hoja_ruta_id')
                ->constrained('recibos_hoja_ruta')
                ->restrictOnDelete();

            $table->foreignId('afiliado_id')
                ->constrained('afiliados')
                ->restrictOnDelete();

            $table->foreignId('vehiculo_id')
                ->constrained('vehiculos')
                ->restrictOnDelete();

            $table->foreignId('tarifa_hoja_ruta_id')
                ->constrained('tarifas_hoja_ruta')
                ->restrictOnDelete();

            // Primer día del mes pagado
            $table->date('periodo');

            // Conserva la placa al momento del cobro
            $table->string('placa', 20);

            // Precio histórico aplicado al período
            $table->decimal('importe', 12, 2);

            // Permite diferenciar pagos válidos de anulados
            $table->boolean('vigente')->default(true);

            $table->timestamps();

            $table->index(
                ['team_id', 'afiliado_id', 'periodo'],
                'detalles_hr_busqueda_periodo'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Evitar duplicación de mensualidades
        |--------------------------------------------------------------------------
        |
        | Un afiliado no puede tener dos pagos vigentes
        | correspondientes al mismo período.
        |
        | Si se anula un recibo, posteriormente podremos
        | registrar nuevamente el período.
        |
        */

        DB::statement(
            'CREATE UNIQUE INDEX detalles_hr_mes_vigente_unico
             ON recibo_hoja_ruta_detalles
             (team_id, afiliado_id, periodo)
             WHERE vigente = TRUE'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('recibo_hoja_ruta_detalles');

        Schema::dropIfExists('recibos_hoja_ruta');

        Schema::dropIfExists('numeradores_recibo');
    }
};