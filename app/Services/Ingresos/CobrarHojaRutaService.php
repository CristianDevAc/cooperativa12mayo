<?php

namespace App\Services\Ingresos;

use App\Models\Afiliado;
use App\Models\NumeradorRecibo;
use App\Models\ReciboHojaRuta;
use App\Models\User;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CobrarHojaRutaService
{
    public function __construct(
        private CotizarHojaRutaService $cotizador
    ) {
    }

    /**
     * Registrar un cobro completo.
     *
     * El servicio no permite registrar pagos
     * mientras no estén habilitados.
     */
    public function cobrar(
        int $afiliadoId,
        string $periodoObjetivo,
        ?string $totalEsperado = null
    ): ReciboHojaRuta {

        /*
        |--------------------------------------------------------------------------
        | Verificar habilitación
        |--------------------------------------------------------------------------
        */

        if (! config('hojas_ruta.cobros_habilitados', false)) {
            throw new DomainException(
                'Los cobros de hojas de ruta todavía no están habilitados.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Verificar usuario y permisos
        |--------------------------------------------------------------------------
        */

        $usuario = Auth::user();

        abort_unless(
            $usuario instanceof User
            && $usuario->current_team_id !== null,
            403
        );

        // El permiso se configurará antes de activar los cobros.
        Gate::authorize('cobrar-hojas-ruta');

        $teamId = (int) $usuario->current_team_id;

        /*
        |--------------------------------------------------------------------------
        | Consultar fecha inicial obligatoria
        |--------------------------------------------------------------------------
        */

        $inicioControl = config('hojas_ruta.inicio_control');

        if (
            ! is_string($inicioControl)
            || ! preg_match(
                '/^\d{4}-(0[1-9]|1[0-2])$/',
                $inicioControl
            )
        ) {
            throw new DomainException(
                'Debe configurar el mes inicial de control de hojas de ruta.'
            );
        }

        $fechaCobro = CarbonImmutable::now('America/La_Paz');

        /*
        |--------------------------------------------------------------------------
        | Transacción del cobro
        |--------------------------------------------------------------------------
        */

        return DB::transaction(function () use (
            $teamId,
            $afiliadoId,
            $periodoObjetivo,
            $inicioControl,
            $fechaCobro,
            $usuario,
            $totalEsperado
        ) {

            /*
            | Bloquear el afiliado durante el cobro.
            |
            | Evita que dos cajas registren simultáneamente
            | mensualidades del mismo afiliado.
            */

            Afiliado::query()
                ->where('team_id', $teamId)
                ->lockForUpdate()
                ->findOrFail($afiliadoId);

            /*
            | Volver a calcular las mensualidades pendientes.
            |
            | Nunca confiamos solamente en una cotización
            | mostrada previamente en pantalla.
            */

            $cotizacion = $this->cotizador->cotizar(
                $teamId,
                $afiliadoId,
                $inicioControl,
                $periodoObjetivo,
                $fechaCobro
            );

            if ($cotizacion['cantidad_meses'] === 0) {
                throw new DomainException(
                    'El afiliado no tiene mensualidades pendientes para cobrar.'
                );
            }
            if (
                $totalEsperado !== null
                && $cotizacion['total'] !== $totalEsperado
            ) {
                throw new DomainException(
                    'El importe de la cotización cambió. '
                    . 'Vuelva a calcular antes de registrar el cobro.'
                );
            }
            /*
            |--------------------------------------------------------------------------
            | Preparar numerador
            |--------------------------------------------------------------------------
            */

            DB::table('numeradores_recibo')->insertOrIgnore([
                'team_id' => $teamId,
                'tipo' => 'HOJA_RUTA',
                'ultimo_numero' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /*
            | Bloquear el numerador mientras se genera
            | el siguiente número de recibo.
            */

            $numerador = NumeradorRecibo::query()
                ->where('team_id', $teamId)
                ->where('tipo', 'HOJA_RUTA')
                ->lockForUpdate()
                ->firstOrFail();

            $nuevoNumero = $numerador->ultimo_numero + 1;

            /*
            |--------------------------------------------------------------------------
            | Registrar recibo
            |--------------------------------------------------------------------------
            */

            $recibo = ReciboHojaRuta::create([
                'team_id' => $teamId,
                'afiliado_id' => $afiliadoId,
                'numero_recibo' => $nuevoNumero,
                'fecha_cobro' => $fechaCobro,
                'subtotal' => $cotizacion['subtotal'],
                'multa_unica' => $cotizacion['multa_unica'],
                'total' => $cotizacion['total'],
                'estado' => 'EMITIDO',
                'registrado_por' => $usuario->id,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Registrar mensualidades pagadas
            |--------------------------------------------------------------------------
            */

            foreach ($cotizacion['detalles'] as $detalle) {

                $recibo->detalles()->create([
                    'team_id' => $teamId,
                    'afiliado_id' => $afiliadoId,
                    'vehiculo_id' => $detalle['vehiculo_id'],
                    'tarifa_hoja_ruta_id' => $detalle['tarifa_id'],
                    'periodo' => $detalle['periodo'] . '-01',
                    'placa' => $detalle['placa'],
                    'importe' => $detalle['importe'],
                    'vigente' => true,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Actualizar numeración
            |--------------------------------------------------------------------------
            */

            $numerador->ultimo_numero = $nuevoNumero;

            $numerador->save();

            /*
            | Si todo terminó correctamente,
            | Laravel confirmará la transacción.
            */

            return $recibo->load('detalles');

        }, 3);
    }
}