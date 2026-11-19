<?php

namespace App\Services\Ingresos;

use App\Models\Afiliado;
use App\Models\ConceptoIngreso;
use App\Models\NumeradorRecibo;
use App\Models\ReciboOtroIngreso;
use App\Models\TarifaConceptoIngreso;
use App\Models\User;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use App\Models\SolicitudReincorporacion;
use App\Models\HistorialVehiculo;

class CobrarOtroIngresoService
{
    public function cobrar(
        int $conceptoId,
        ?int $afiliadoId,
        string $detalle,
        string $importeEsperado,
        string $operacionUuid,
        ?string $importeVariable = null,
        ?int $historialVehiculoId = null
    ): ReciboOtroIngreso {

        $usuario = Auth::user();

        abort_unless(
            $usuario instanceof User
            && $usuario->current_team_id !== null,
            403
        );

        Gate::authorize('cobrar-otros-ingresos');

        if (! config('otros_ingresos.cobros_habilitados', false)) {
            throw new DomainException(
                'Los cobros de otros ingresos no están habilitados.'
            );
        }

        $teamId = (int) $usuario->current_team_id;

        $detalle = trim($detalle);
        $detalleIngresado = $detalle;

        if (mb_strlen($detalle) < 5 || mb_strlen($detalle) > 500) {
            throw new DomainException(
                'El detalle debe tener entre 5 y 500 caracteres.'
            );
        }
        if (! Str::isUuid($operacionUuid, version: 4)) {
            throw new DomainException(
                'La operación no tiene un identificador válido. '
                . 'Vuelva a calcular el importe.'
            );
        }

        $importeEsperado = $this->normalizarImporte(
            $importeEsperado
        );

        $fechaCobro = CarbonImmutable::now('America/La_Paz');

        return DB::transaction(function () use (
            $teamId,
            $usuario,
            $conceptoId,
            $afiliadoId,
            $detalle,
            $importeEsperado,
            $importeVariable,
            $operacionUuid,
            $detalleIngresado,
            $fechaCobro,
            $historialVehiculoId
        ) {

            /*
             * Bloqueamos el concepto durante el registro.
             * Esto serializa cobros simultáneos de este concepto
             * y permite verificar pagos únicos antes de insertar.
             */
            $concepto = ConceptoIngreso::query()
                ->where('team_id', $teamId)
                ->lockForUpdate()
                ->findOrFail($conceptoId);

            if (
                $concepto->codigo === 'OTROS_INGRESOS'
                && $detalleIngresado === ''
            ) {
                throw new DomainException(
                    'Debe indicar en Observaciones qué ingreso se está cobrando.'
                );
            }
            /*
            * Por ahora únicamente permitimos cobros
            * de Certificaciones.
            */
            if (! in_array(
                $concepto->codigo,
                [
                    'CERTIFICACION',
                    'CUOTA_INGRESO',
                    'LOGOTIPO',
                    'OTROS_INGRESOS',
                    'REINCORPORACION',
                    'CAMBIO_HERRAMIENTA',
                ],
                true
            )) {
                throw new DomainException(
                    'Este concepto todavía no está autorizado para cobrar.'
                );
            }

            /*
            * Comprobar si esta misma operación
            * ya generó un recibo.
            *
            * El bloqueo del concepto serializa las solicitudes
            * concurrentes para este tipo de ingreso.
            */
            $existente = ReciboOtroIngreso::query()
                ->where('team_id', $teamId)
                ->where('operacion_uuid', $operacionUuid)
                ->first();

            if ($existente !== null) {

                /*
                * El identificador no puede reutilizarse
                * para modificar los datos de otro cobro.
                */
                $coincide =
                    (int) $existente->concepto_ingreso_id === $conceptoId
                    && (int) ($existente->afiliado_id ?? 0)
                        === (int) ($afiliadoId ?? 0)
                    && (int) $existente->registrado_por === (int) $usuario->id
                    && $existente->detalle === $detalle
                    && (string) $existente->importe === $importeEsperado
                    && $existente->estado === 'EMITIDO'&& (int) ($existente->historial_vehiculo_id ?? 0)
                    === (int) ($historialVehiculoId ?? 0);

                if (! $coincide) {
                    throw new DomainException(
                        'El identificador de esta operación ya fue utilizado '
                        . 'con otros datos. Inicie una nueva cotización.'
                    );
                }

                /*
                * La operación ya se registró:
                * devolver el recibo original, sin cobrar nuevamente.
                */
                return $existente;
            }

            /*
            |--------------------------------------------------------------------------
            | Validación especial para Reincorporaciones
            |--------------------------------------------------------------------------
            |
            | El cobro solo se permite cuando existe una
            | solicitud PENDIENTE para ese afiliado.
            |
            | La solicitud queda bloqueada durante la transacción.
            |
            */

            $solicitudReincorporacion = null;

            if ($concepto->codigo === 'REINCORPORACION') {

                if ($afiliadoId === null) {
                    throw new DomainException(
                        'Debe seleccionar el afiliado que solicita reincorporación.'
                    );
                }

                $solicitudReincorporacion = SolicitudReincorporacion::query()
                    ->where('team_id', $teamId)
                    ->where('afiliado_id', $afiliadoId)
                    ->where('estado', 'PENDIENTE')
                    ->whereNull('recibo_otro_ingreso_id')
                    ->lockForUpdate()
                    ->first();

                if ($solicitudReincorporacion === null) {
                    throw new DomainException(
                        'Este afiliado no tiene una solicitud de '
                        . 'reincorporación pendiente de cobro.'
                    );
                }

                $afiliadoReincorporacion = Afiliado::query()
                    ->where('team_id', $teamId)
                    ->findOrFail($afiliadoId);

                if ($afiliadoReincorporacion->estado !== 'INACTIVO') {
                    throw new DomainException(
                        'El afiliado ya no se encuentra INACTIVO. '
                        . 'Revise su situación antes de realizar el cobro.'
                    );
                }
            }

            /*
        |--------------------------------------------------------------------------
        | Validación de Cambio de Herramienta
        |--------------------------------------------------------------------------
        */

        $movimientoCambio = null;

        if ($concepto->codigo === 'CAMBIO_HERRAMIENTA') {

            if ($afiliadoId === null || $historialVehiculoId === null) {
                throw new DomainException(
                    'Debe seleccionar un cambio de vehículo registrado.'
                );
            }

            $movimientoCambio = HistorialVehiculo::query()
                ->where('team_id', $teamId)
                ->where('afiliado_id', $afiliadoId)
                ->where('motivo', 'CAMBIO_HERRAMIENTA')
                ->where('id', $historialVehiculoId)
                ->lockForUpdate()
                ->first();

            if ($movimientoCambio === null) {
                throw new DomainException(
                    'No existe un cambio de herramienta válido '
                    . 'para el afiliado seleccionado.'
                );
            }

            $yaCobrado = ReciboOtroIngreso::query()
                ->where('team_id', $teamId)
                ->where('historial_vehiculo_id', $movimientoCambio->id)
                ->exists();

            if ($yaCobrado) {
                throw new DomainException(
                    'Este cambio de herramienta ya tiene un recibo registrado.'
                );
            }

        } elseif ($historialVehiculoId !== null) {

            throw new DomainException(
                'El movimiento de vehículo no corresponde al concepto seleccionado.'
            );
        }

            if (! $concepto->activo) {
                throw new DomainException(
                    'Este concepto de ingreso no está habilitado.'
                );
            }

            if (
                ! in_array(
                    $concepto->forma_importe,
                    ['FIJO', 'VARIABLE'],
                    true
                )
                || ! in_array(
                    $concepto->periodicidad,
                    ['UNICO', 'POR_EVENTO'],
                    true
                )
                || ! in_array(
                    $concepto->naturaleza,
                    ['OPERATIVO', 'EXTRAORDINARIO', 'PATRIMONIAL'],
                    true
                )
                || $concepto->requiere_afiliado === null
            ) {
                throw new DomainException(
                    'El concepto tiene una configuración incompleta '
                    . 'o una periodicidad todavía no implementada.'
                );
            }

            /*
             * Los movimientos patrimoniales requieren reglas
             * financieras propias. No se habilitan todavía.
             */
            if ($concepto->naturaleza === 'PATRIMONIAL') {
                throw new DomainException(
                    'Los conceptos patrimoniales todavía no están habilitados.'
                );
            }

            if ($concepto->codigo === 'FONDO_SOCIAL') {
                throw new DomainException(
                    'Fondo Social requiere una configuración especial.'
                );
            }

            /*
             * Validación del afiliado.
             */
            if ($concepto->requiere_afiliado && $afiliadoId === null) {
                throw new DomainException(
                    'Debe seleccionar un afiliado para este cobro.'
                );
            }

            if ($afiliadoId !== null) {
                Afiliado::query()
                    ->where('team_id', $teamId)
                    ->findOrFail($afiliadoId);
            }

            /*
             * Los conceptos UNICO solamente pueden cobrarse
             * una vez por afiliado, mientras el recibo esté emitido.
             */
            if ($concepto->periodicidad === 'UNICO') {

                if ($afiliadoId === null) {
                    throw new DomainException(
                        'Un concepto de cobro único requiere identificar un afiliado.'
                    );
                }

                $yaPagado = ReciboOtroIngreso::query()
                    ->where('team_id', $teamId)
                    ->where('concepto_ingreso_id', $concepto->id)
                    ->where('afiliado_id', $afiliadoId)
                    ->where('estado', 'EMITIDO')
                    ->exists();

                if ($yaPagado) {
                    throw new DomainException(
                        'Este afiliado ya tiene registrado el pago único '
                        . 'de este concepto.'
                    );
                }
            }

            /*
             * Determinar el importe según la configuración.
             */
            $tarifa = null;

            if ($concepto->forma_importe === 'FIJO') {

                $tarifa = TarifaConceptoIngreso::query()
                    ->where('team_id', $teamId)
                    ->where('concepto_ingreso_id', $concepto->id)
                    ->whereDate(
                        'vigente_desde',
                        '<=',
                        $fechaCobro->toDateString()
                    )
                    ->orderByDesc('vigente_desde')
                    ->first();

                if (! $tarifa) {
                    throw new DomainException(
                        'No existe una tarifa vigente para este concepto.'
                    );
                }

                if (
                    $importeVariable !== null
                    && trim($importeVariable) !== ''
                ) {
                    throw new DomainException(
                        'Este concepto utiliza una tarifa fija.'
                    );
                }

                $importe = (string) $tarifa->importe;

            } else {

                if ($importeVariable === null) {
                    throw new DomainException(
                        'Debe indicar el importe de este ingreso.'
                    );
                }

                $importe = $this->normalizarImporte(
                    $importeVariable
                );
            }

            /*
             * Comparar con el importe que el cajero confirmó.
             * Si cambió la tarifa, no registrar el recibo.
             */
            $esperado = $this->normalizarImporte(
                $importeEsperado
            );

            if ($importe !== $esperado) {
                throw new DomainException(
                    'El importe cambió. Actualice la cotización '
                    . 'antes de registrar el cobro.'
                );
            }

            /*
             * Numeración independiente de Hojas de Ruta.
             */
            $tipoNumerador = 'CONCEPTO_' . $concepto->id;

            DB::table('numeradores_recibo')->insertOrIgnore([
                'team_id' => $teamId,
                'tipo' => $tipoNumerador,
                'ultimo_numero' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $numerador = NumeradorRecibo::query()
                ->where('team_id', $teamId)
                ->where('tipo', $tipoNumerador)
                ->lockForUpdate()
                ->firstOrFail();

            $nuevoNumero = (int) $numerador->ultimo_numero + 1;

            /*
             * Guardar el recibo.
             */
            $recibo = ReciboOtroIngreso::create([
                'team_id' => $teamId,
                'concepto_ingreso_id' => $concepto->id,
                'afiliado_id' => $afiliadoId,
                'tarifa_concepto_ingreso_id' => $tarifa?->id,
                'numero_recibo' => $nuevoNumero,
                'fecha_cobro' => $fechaCobro,
                'periodo' => null,
                'concepto_codigo' => $concepto->codigo,
                'concepto_nombre' => $concepto->nombre,
                'forma_importe' => $concepto->forma_importe,
                'periodicidad' => $concepto->periodicidad,
                'naturaleza' => $concepto->naturaleza,
                'detalle' => $detalle,
                'importe' => $importe,
                'estado' => 'EMITIDO',
                'operacion_uuid' => $operacionUuid,
                'registrado_por' => $usuario->id,
                'historial_vehiculo_id' => $movimientoCambio?->id,
                
            ]);
            /*
            * Vincular el recibo con la solicitud.
            * Ambos cambios pertenecen a la misma transacción.
            */
            if ($solicitudReincorporacion !== null) {

                $solicitudReincorporacion->estado = 'COBRADA';

                $solicitudReincorporacion->recibo_otro_ingreso_id =
                    $recibo->id;

                $solicitudReincorporacion->save();
            }

            $numerador->ultimo_numero = $nuevoNumero;
            $numerador->save();

            return $recibo;

        }, 3);
    }

    /*
     * Validar montos en bolivianos con máximo dos decimales,
     * sin emplear aritmética de punto flotante.
     */
    private function normalizarImporte(string $valor): string
    {
        $valor = trim($valor);

        if (! preg_match(
            '/^\d{1,10}(?:\.\d{1,2})?$/',
            $valor
        )) {
            throw new DomainException(
                'El importe debe ser positivo y tener máximo dos decimales. '
                . 'Utilice punto decimal, por ejemplo: 15.50.'
            );
        }

        $partes = explode('.', $valor, 2);

        $entero = ltrim($partes[0], '0');
        $entero = $entero === '' ? '0' : $entero;

        $decimales = str_pad(
            $partes[1] ?? '',
            2,
            '0'
        );

        if ($entero === '0' && $decimales === '00') {
            throw new DomainException(
                'El importe debe ser mayor que cero.'
            );
        }

        return $entero . '.' . $decimales;
    }
}