<?php

namespace App\Livewire\Informes;

use App\Models\Egreso;
use App\Models\ReciboOtroIngreso;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class InformeEconomicoMensual extends Component
{
    public string $mes = '';
    public string $anio = '';

    public function mount(): void
    {
        $hoy = now('America/La_Paz');

        $this->mes = $hoy->format('m');
        $this->anio = $hoy->format('Y');
    }

    private function teamId(): int
    {
        $usuario = Auth::user();

        abort_unless(
            $usuario instanceof User
            && $usuario->current_team_id !== null,
            403
        );

        return (int) $usuario->current_team_id;
    }

    private function convertirACentavos(
        string|int|null $importe
    ): int {

        $importe = (string) ($importe ?? '0');

        if (! preg_match(
            '/^\d+(?:\.\d{1,2})?$/D',
            $importe
        )) {
            throw new \UnexpectedValueException(
                'Se encontró un importe monetario inválido.'
            );
        }

        $partes = explode('.', $importe, 2);

        $unidades = (int) $partes[0];

        $decimales = (int) str_pad(
            $partes[1] ?? '',
            2,
            '0'
        );

        return ($unidades * 100) + $decimales;
    }
    public function render()
    {
        Gate::authorize('ver-informe-economico');

        $mes = (int) $this->mes;
        $anio = (int) $this->anio;

        abort_unless(
            ctype_digit($this->mes)
            && ctype_digit($this->anio)
            && $mes >= 1
            && $mes <= 12
            && $anio >= 2020
            && $anio <= 2100,
            422,
            'Período económico inválido.'
        );

        $teamId = $this->teamId();

        /*
         * Fechas del período en horario boliviano.
         *
         * Utilizamos inicio inclusivo y fin exclusivo
         * para no perder operaciones del último día.
         */
        $inicioLocal = CarbonImmutable::create(
            $anio,
            $mes,
            1,
            0,
            0,
            0,
            'America/La_Paz'
        );

        $desde = $inicioLocal->utc();

        $hasta = $inicioLocal
            ->addMonth()
            ->utc();

        


        /*
        |--------------------------------------------------------------------------
        | RECAUDACIÓN DE HOJAS DE RUTA Y MULTAS
        |--------------------------------------------------------------------------
        |
        | Se utiliza la fecha real de cobro.
        | Solo se consideran recibos EMITIDOS.
        | No se incluyen los recibos ANULADOS.
        |
        | subtotal    = cobro de mensualidades
        | multa_unica = multas cobradas
        | total       = importe total del recibo
        |
        */

        $resumenHojasRuta = DB::table('recibos_hoja_ruta')
            ->where('team_id', $teamId)
            ->where('estado', 'EMITIDO')
            ->where('fecha_cobro', '>=', $desde)
            ->where('fecha_cobro', '<', $hasta)
            ->selectRaw("
                COUNT(*) AS cantidad,

                COALESCE(
                    SUM(subtotal),
                    0
                ) AS subtotal,

                COALESCE(
                    SUM(multa_unica),
                    0
                ) AS multas,

                COALESCE(
                    SUM(total),
                    0
                ) AS total
            ")
            ->first();    
        /*
         * OTROS INGRESOS
         *
         * Solo recibos emitidos.
         * Conservamos la clasificación histórica
         * registrada al momento del cobro.
         */
        $baseIngresos = ReciboOtroIngreso::query()
            ->where('team_id', $teamId)
            ->where('estado', 'EMITIDO')
            ->where('fecha_cobro', '>=', $desde)
            ->where('fecha_cobro', '<', $hasta);

        $ingresos = (clone $baseIngresos)
            ->selectRaw('
                concepto_codigo,
                concepto_nombre,
                naturaleza,
                COUNT(*) AS cantidad,
                COALESCE(SUM(importe), 0) AS total
            ')
            ->groupBy(
                'concepto_codigo',
                'concepto_nombre',
                'naturaleza'
            )
            ->orderBy('naturaleza')
            ->orderBy('concepto_nombre')
            ->get();

        $resumenIngresos = (clone $baseIngresos)
            ->selectRaw("
                COUNT(*) AS cantidad,

                COALESCE(
                    SUM(
                        CASE
                            WHEN naturaleza = 'OPERATIVO'
                            THEN importe ELSE 0
                        END
                    ), 0
                ) AS operativos,

                COALESCE(
                    SUM(
                        CASE
                            WHEN naturaleza = 'EXTRAORDINARIO'
                            THEN importe ELSE 0
                        END
                    ), 0
                ) AS extraordinarios,

                COALESCE(
                    SUM(
                        CASE
                            WHEN naturaleza = 'PATRIMONIAL'
                            THEN importe ELSE 0
                        END
                    ), 0
                ) AS patrimoniales
            ")
            ->first();

        /*
         * EGRESOS
         *
         * Excluimos los comprobantes ANULADOS.
         */
        $baseEgresos = Egreso::query()
            ->where('team_id', $teamId)
            ->where('estado', 'PAGADO')
            ->where('fecha_pago', '>=', $desde)
            ->where('fecha_pago', '<', $hasta);

        $egresos = (clone $baseEgresos)
            ->selectRaw('
                concepto_codigo,
                concepto_nombre,
                grupo_reporte,
                COUNT(*) AS cantidad,
                COALESCE(SUM(importe), 0) AS total
            ')
            ->groupBy(
                'concepto_codigo',
                'concepto_nombre',
                'grupo_reporte'
            )
            ->orderBy('grupo_reporte')
            ->orderBy('concepto_nombre')
            ->get();

        $resumenEgresos = (clone $baseEgresos)
            ->selectRaw("
                COUNT(*) AS cantidad,

                COALESCE(
                    SUM(
                        CASE
                            WHEN grupo_reporte = 'ORDINARIO'
                            THEN importe ELSE 0
                        END
                    ), 0
                ) AS ordinarios,

                COALESCE(
                    SUM(
                        CASE
                            WHEN grupo_reporte = 'OTROS'
                            THEN importe ELSE 0
                        END
                    ), 0
                ) AS otros,

                COALESCE(SUM(importe), 0) AS total
            ")
            ->first();
            /*
            |--------------------------------------------------------------------------
            | CONSOLIDACIÓN ECONÓMICA MENSUAL
            |--------------------------------------------------------------------------
            |
            | Las cantidades se convierten a centavos.
            | Se excluyen los movimientos patrimoniales.
            | No se duplican los importes de los recibos.
            |
            */

            // HOJAS DE RUTA

            $hojasRutaCentavos = $this->convertirACentavos(
                $resumenHojasRuta->subtotal
            );

            $multasCentavos = $this->convertirACentavos(
                $resumenHojasRuta->multas
            );

            // OTROS INGRESOS

            $otrosOperativosCentavos = $this->convertirACentavos(
                $resumenIngresos->operativos
            );

            $extraordinariosCentavos = $this->convertirACentavos(
                $resumenIngresos->extraordinarios
            );

            $patrimonialesCentavos = $this->convertirACentavos(
                $resumenIngresos->patrimoniales
            );

            // EGRESOS

            $egresosOrdinariosCentavos = $this->convertirACentavos(
                $resumenEgresos->ordinarios
            );

            $otrosEgresosCentavos = $this->convertirACentavos(
                $resumenEgresos->otros
            );

            // TOTAL DE INGRESOS

            $totalIngresosCentavos =
                $hojasRutaCentavos
                + $multasCentavos
                + $otrosOperativosCentavos
                + $extraordinariosCentavos;

            // TOTAL DE EGRESOS

            $totalEgresosCentavos =
                $egresosOrdinariosCentavos
                + $otrosEgresosCentavos;

            // RESULTADO ECONÓMICO

            $resultadoCentavos =
                $totalIngresosCentavos
                - $totalEgresosCentavos;

            /*
            * Control de consistencia:
            * el total de Hojas de Ruta debe coincidir
            * con mensualidades + multas.
            */

            $hojasRutaConsistentes = (
                $hojasRutaCentavos + $multasCentavos
            ) === $this->convertirACentavos(
                $resumenHojasRuta->total
            );

            /*
            * Control de consistencia de egresos.
            */

            $egresosConsistentes = (
                $egresosOrdinariosCentavos
                + $otrosEgresosCentavos
            ) === $this->convertirACentavos(
                $resumenEgresos->total
            );

            // DATOS PARA EL INFORME

            $consolidado = [
                'hojas_ruta' => $hojasRutaCentavos,
                'multas' => $multasCentavos,
                'otros_operativos' => $otrosOperativosCentavos,
                'extraordinarios' => $extraordinariosCentavos,
                'patrimoniales' => $patrimonialesCentavos,
                'egresos_ordinarios' => $egresosOrdinariosCentavos,
                'otros_egresos' => $otrosEgresosCentavos,
                'total_ingresos' => $totalIngresosCentavos,
                'total_egresos' => $totalEgresosCentavos,
                'resultado' => $resultadoCentavos,
                'conciliacion_correcta' =>
                    $hojasRutaConsistentes && $egresosConsistentes,
            ];

        return view(
            'livewire.informes.informe-economico-mensual',
            [
                'ingresos' => $ingresos,
                'egresos' => $egresos,

                'resumenIngresos' => $resumenIngresos,
                'resumenEgresos' => $resumenEgresos,

                'nombreMes' => $inicioLocal
                    ->locale('es')
                    ->translatedFormat('F Y'),
                'resumenHojasRuta' => $resumenHojasRuta,
                'consolidado' => $consolidado,
            ]
        );
    }
}