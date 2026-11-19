<?php

namespace App\Services\Ingresos;

use App\Models\Afiliado;
use App\Models\ReciboHojaRutaDetalle;
use App\Models\TarifaHojaRuta;
use Carbon\CarbonImmutable;
use DomainException;
use InvalidArgumentException;

class CotizarHojaRutaService
{
    /**
     * Calcula las mensualidades pendientes.
     *
     * No registra pagos ni modifica la base de datos.
     */
    public function cotizar(
        int $teamId,
        int $afiliadoId,
        string $inicioControl,
        string $periodoObjetivo,
        ?CarbonImmutable $fechaCobro = null
    ): array {

        $fechaCobro ??= CarbonImmutable::now('America/La_Paz');

        $fechaCobro = $fechaCobro->setTimezone('America/La_Paz');

        $desde = $this->convertirMes($inicioControl);
        $hasta = $this->convertirMes($periodoObjetivo);

        if ($desde->greaterThan($hasta)) {
            throw new DomainException(
                'El período inicial no puede ser posterior al período objetivo.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Verificar apertura de venta
        |--------------------------------------------------------------------------
        */

        $apertura = ReglasHojaRuta::fechaApertura(
            (int) $hasta->year,
            (int) $hasta->month
        );

        if ($fechaCobro->lessThan($apertura)) {
            throw new DomainException(
                'Todavía no está habilitada la venta para '
                . $hasta->format('m/Y')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Consultar afiliado y vehículo vigente
        |--------------------------------------------------------------------------
        */

        $afiliado = Afiliado::query()
            ->with('asignacionVigente.vehiculo')
            ->where('team_id', $teamId)
            ->whereIn('estado', [
                'ACTIVO',
                'BLOQUEADO',
                'INACTIVO',
            ])
            ->findOrFail($afiliadoId);

        /*
        |--------------------------------------------------------------------------
        | Obtener vehículo asociado al cobro
        |--------------------------------------------------------------------------
        |
        | Primero buscamos el vehículo vigente.
        | Si no existe, utilizamos el último vehículo del historial.
        |
        | Esto permite regularizar deudas anteriores cuando
        | el afiliado se encuentra inactivo.
        |
        */

        $vehiculo = $afiliado->asignacionVigente?->vehiculo;

        // Verificar que el vehículo pertenezca al equipo.
        if (
            $vehiculo !== null
            && (int) $vehiculo->team_id !== $teamId
        ) {
            $vehiculo = null;
        }

        if ($vehiculo === null) {

            $ultimaAsignacion = $afiliado->historialVehiculos()
                ->with('vehiculo')
                ->where('team_id', $teamId)
                ->whereHas(
                    'vehiculo',
                    fn ($query) => $query
                        ->where('team_id', $teamId)
                )
                ->orderByDesc('fecha_inicio')
                ->first();

            $vehiculo = $ultimaAsignacion?->vehiculo;
        }

        if ($vehiculo === null) {

            throw new DomainException(
                'El afiliado no tiene ningún vehículo registrado. '
                . 'No es posible registrar una hoja de ruta '
                . 'sin identificar el vehículo asociado.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | No cobrar períodos anteriores al ingreso del afiliado
        |--------------------------------------------------------------------------
        */

        if ($afiliado->fecha_ingreso) {

            $mesIngreso = $this->convertirMes(
                $afiliado->fecha_ingreso->format('Y-m')
            );

            if ($mesIngreso->greaterThan($desde)) {
                $desde = $mesIngreso;
            }
        }

        if ($desde->greaterThan($hasta)) {
            throw new DomainException(
                'El afiliado todavía no tenía obligación de pago en ese período.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Consultar períodos de inactividad del afiliado
        |--------------------------------------------------------------------------
        |
        | periodo_desde: primer mes suspendido (incluido).
        | periodo_hasta: primer mes reactivado (excluido).
        |
        | Estos períodos no generan nuevas mensualidades.
        |
        */

        $periodosInactividad = $afiliado->periodosInactividad()
            ->where('team_id', $teamId)
            ->whereDate(
                'periodo_desde',
                '<=',
                $hasta->toDateString()
            )
            ->where(function ($query) use ($desde) {
                $query->whereNull('periodo_hasta')
                    ->orWhereDate(
                        'periodo_hasta',
                        '>',
                        $desde->toDateString()
                    );
            })
            ->get([
                'periodo_desde',
                'periodo_hasta',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Consultar mensualidades que ya están pagadas
        |--------------------------------------------------------------------------
        */

        $pagados = ReciboHojaRutaDetalle::query()
            ->where('team_id', $teamId)
            ->where('afiliado_id', $afiliadoId)
            ->where('vigente', true)
            ->whereBetween('periodo', [
                $desde->toDateString(),
                $hasta->toDateString(),
            ])
            ->get(['periodo'])
            ->mapWithKeys(
                fn ($detalle) => [
                    $detalle->periodo->format('Y-m') => true
                ]
            )
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Calcular períodos pendientes
        |--------------------------------------------------------------------------
        */

        $detalles = [];

        $subtotalCentavos = 0;

        for (
            $mes = $desde;
            $mes->lessThanOrEqualTo($hasta);
            $mes = $mes->addMonthNoOverflow()
        ) {

            $periodo = $mes->format('Y-m');

            /*
            |--------------------------------------------------------------------------
            | Verificar si la mensualidad estaba suspendida
            |--------------------------------------------------------------------------
            */

            $estaInactivo = $periodosInactividad->contains(
                function ($inactividad) use ($periodo) {

                    $desdeInactividad = $inactividad
                        ->periodo_desde
                        ->format('Y-m');

                    $hastaInactividad = $inactividad
                        ->periodo_hasta
                        ?->format('Y-m');

                    return $desdeInactividad <= $periodo
                        && (
                            $hastaInactividad === null
                            || $periodo < $hastaInactividad
                        );
                }
            );

            // Los meses completamente inactivos no generan deuda.
            if ($estaInactivo) {
                continue;
            }

            // Si el período ya fue pagado, no se cobra otra vez.
            if (isset($pagados[$periodo])) {
                continue;
            }

            /*
            | Recuperar el precio histórico del período.
            */

            $tarifa = TarifaHojaRuta::query()
                ->where('team_id', $teamId)
                ->whereDate(
                    'vigente_desde',
                    '<=',
                    $mes->toDateString()
                )
                ->orderByDesc('vigente_desde')
                ->first();

            if (! $tarifa) {
                throw new DomainException(
                    "No existe una tarifa configurada para {$periodo}."
                );
            }

            $importeCentavos = $this->aCentavos(
                (string) $tarifa->importe_base
            );

            $subtotalCentavos += $importeCentavos;

            $detalles[] = [
                'periodo' => $periodo,
                'tarifa_id' => $tarifa->id,
                'vehiculo_id' => $vehiculo->id,
                'placa' => $vehiculo->placa,
                'importe_centavos' => $importeCentavos,
                'importe' => $this->aBolivianos($importeCentavos),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Calcular multa única
        |--------------------------------------------------------------------------
        */

        $periodosPendientes = array_column(
            $detalles,
            'periodo'
        );

        $aplicaMulta = ReglasHojaRuta::aplicaMulta(
            $periodosPendientes,
            $fechaCobro
        );

        $multaCentavos = 0;

        if ($aplicaMulta) {

            // Multa vigente en la fecha del cobro.
            $tarifaMulta = TarifaHojaRuta::query()
                ->where('team_id', $teamId)
                ->whereDate(
                    'vigente_desde',
                    '<=',
                    $fechaCobro->startOfMonth()->toDateString()
                )
                ->orderByDesc('vigente_desde')
                ->first();

            if (! $tarifaMulta) {
                throw new DomainException(
                    'No existe una multa configurada para la fecha de cobro.'
                );
            }

            // Una sola multa, aunque existan varios meses atrasados.
            $multaCentavos = $this->aCentavos(
                (string) $tarifaMulta->importe_multa
            );
        }

        $totalCentavos = $subtotalCentavos + $multaCentavos;

        return [
            'afiliado_id' => $afiliado->id,
            'afiliado' => $afiliado->nombre_completo,
            'ci' => $afiliado->ci,
            'vehiculo_id' => $vehiculo->id,
            'placa' => $vehiculo->placa,
            'periodo_objetivo' => $periodoObjetivo,

            'detalles' => $detalles,

            'cantidad_meses' => count($detalles),

            'subtotal_centavos' => $subtotalCentavos,
            'multa_centavos' => $multaCentavos,
            'total_centavos' => $totalCentavos,

            'subtotal' => $this->aBolivianos($subtotalCentavos),
            'multa_unica' => $this->aBolivianos($multaCentavos),
            'total' => $this->aBolivianos($totalCentavos),

            'tiene_mora' => $aplicaMulta,
        ];
    }

    /**
     * Convertir YYYY-MM al primer día del mes.
     */
    private function convertirMes(string $periodo): CarbonImmutable
    {
        if (
            ! preg_match(
                '/^\d{4}-(0[1-9]|1[0-2])$/',
                $periodo
            )
        ) {
            throw new InvalidArgumentException(
                'El período debe tener formato YYYY-MM.'
            );
        }

        [$anio, $mes] = array_map(
            'intval',
            explode('-', $periodo)
        );

        return CarbonImmutable::create(
            $anio,
            $mes,
            1,
            0,
            0,
            0,
            'America/La_Paz'
        );
    }

    /**
     * Convertir importe decimal a centavos.
     * Evitamos sumar importes monetarios con float.
     */
    private function aCentavos(string $importe): int
    {
        if (
            ! preg_match(
                '/^\d{1,10}(\.\d{1,2})?$/',
                $importe
            )
        ) {
            throw new InvalidArgumentException(
                'Importe monetario inválido.'
            );
        }

        $partes = explode('.', $importe, 2);

        $enteros = (int) $partes[0];

        $decimales = (int) str_pad(
            $partes[1] ?? '0',
            2,
            '0',
            STR_PAD_RIGHT
        );

        return ($enteros * 100) + $decimales;
    }

    /**
     * Convertir centavos a un importe con dos decimales.
     */
    private function aBolivianos(int $centavos): string
    {
        return intdiv($centavos, 100)
            . '.'
            . str_pad(
                (string) ($centavos % 100),
                2,
                '0',
                STR_PAD_LEFT
            );
    }
}