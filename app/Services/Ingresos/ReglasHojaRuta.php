<?php

namespace App\Services\Ingresos;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

class ReglasHojaRuta
{
    private const ZONA_HORARIA = 'America/La_Paz';

    private const DIA_APERTURA = 25;

    private const DIA_LIMITE = 5;

    /**
     * Fecha de apertura de venta.
     *
     * Ejemplo:
     * Noviembre 2026 -> 25 de octubre de 2026
     */
    public static function fechaApertura(
        int $anio,
        int $mes
    ): CarbonImmutable {

        $periodo = CarbonImmutable::create(
            $anio,
            $mes,
            1,
            0,
            0,
            0,
            self::ZONA_HORARIA
        );

        return $periodo
            ->subMonth()
            ->setDay(self::DIA_APERTURA);
    }

    /**
     * Última fecha para pagar sin multa.
     *
     * Si el día 5 es sábado o domingo,
     * se traslada al siguiente lunes.
     */
    public static function fechaLimiteSinMulta(
        int $anio,
        int $mes
    ): CarbonImmutable {

        $fecha = CarbonImmutable::create(
            $anio,
            $mes,
            self::DIA_LIMITE,
            0,
            0,
            0,
            self::ZONA_HORARIA
        );

        while ($fecha->isWeekend()) {
            $fecha = $fecha->addDay();
        }

        return $fecha;
    }

    /**
     * Determina si existe mora.
     *
     * Recibe una lista de meses pendientes:
     * ['2026-07', '2026-08', '2026-09']
     *
     * Devuelve TRUE si al menos uno está vencido.
     * La multa se cobrará una sola vez.
     */
    public static function aplicaMulta(
        array $periodosPendientes,
        ?CarbonImmutable $fechaPago = null
    ): bool {

        $fechaPago ??= CarbonImmutable::now(
            self::ZONA_HORARIA
        );

        foreach ($periodosPendientes as $periodo) {

            if (
                ! is_string($periodo)
                || ! preg_match(
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

            $limite = self::fechaLimiteSinMulta(
                $anio,
                $mes
            );

            if (
                $fechaPago->startOfDay()
                    ->greaterThan($limite->startOfDay())
            ) {
                return true;
            }
        }

        return false;
    }
}