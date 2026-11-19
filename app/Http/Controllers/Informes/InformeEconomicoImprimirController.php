<?php

namespace App\Http\Controllers\Informes;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use UnexpectedValueException;

class InformeEconomicoImprimirController extends Controller
{
    public function imprimir(
        string $current_team,
        string $anio,
        string $mes
    ): View {

        Gate::authorize('ver-informe-economico');

        $usuario = Auth::user();

        abort_unless(
            $usuario instanceof User
            && $usuario->current_team_id !== null,
            403
        );

        abort_unless(
            ctype_digit($anio)
            && ctype_digit($mes)
            && (int) $anio >= 2020
            && (int) $anio <= 2100
            && (int) $mes >= 1
            && (int) $mes <= 12,
            422,
            'Período económico inválido.'
        );

        $teamId = (int) $usuario->current_team_id;

        // Verificar que la URL corresponda a la
        // cooperativa activa del usuario.
        Team::query()
            ->whereKey($teamId)
            ->where('slug', $current_team)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | PERÍODO DEL INFORME
        |--------------------------------------------------------------------------
        */

        $inicioLocal = CarbonImmutable::create(
            (int) $anio,
            (int) $mes,
            1,
            0,
            0,
            0,
            'America/La_Paz'
        );

        $desde = $inicioLocal->utc();
        $hasta = $inicioLocal->addMonth()->utc();

        /*
        |--------------------------------------------------------------------------
        | HOJAS DE RUTA Y MULTAS
        |--------------------------------------------------------------------------
        */

        $hojas = DB::table('recibos_hoja_ruta')
            ->where('team_id', $teamId)
            ->where('estado', 'EMITIDO')
            ->where('fecha_cobro', '>=', $desde)
            ->where('fecha_cobro', '<', $hasta)
            ->selectRaw("
                COUNT(*) AS cantidad,
                COALESCE(SUM(subtotal), 0) AS subtotal,
                COALESCE(SUM(multa_unica), 0) AS multas,
                COALESCE(SUM(total), 0) AS total
            ")
            ->first();

        /*
        |--------------------------------------------------------------------------
        | OTROS INGRESOS
        |--------------------------------------------------------------------------
        */

        $ingresos = DB::table('recibos_otros_ingresos')
            ->where('team_id', $teamId)
            ->where('estado', 'EMITIDO')
            ->where('fecha_cobro', '>=', $desde)
            ->where('fecha_cobro', '<', $hasta)
            ->selectRaw("
                concepto_codigo,
                concepto_nombre,
                naturaleza,
                COUNT(*) AS cantidad,
                COALESCE(SUM(importe), 0) AS total
            ")
            ->groupBy(
                'concepto_codigo',
                'concepto_nombre',
                'naturaleza'
            )
            ->orderBy('naturaleza')
            ->orderBy('concepto_nombre')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | EGRESOS PAGADOS
        |--------------------------------------------------------------------------
        */

        $egresos = DB::table('egresos')
            ->where('team_id', $teamId)
            ->where('estado', 'PAGADO')
            ->where('fecha_pago', '>=', $desde)
            ->where('fecha_pago', '<', $hasta)
            ->selectRaw("
                concepto_codigo,
                concepto_nombre,
                grupo_reporte,
                COUNT(*) AS cantidad,
                COALESCE(SUM(importe), 0) AS total
            ")
            ->groupBy(
                'concepto_codigo',
                'concepto_nombre',
                'grupo_reporte'
            )
            ->orderBy('grupo_reporte')
            ->orderBy('concepto_nombre')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | CONSOLIDACIÓN EN CENTAVOS
        |--------------------------------------------------------------------------
        */

        $totales = [
            'hojas_ruta' => $this->aCentavos($hojas->subtotal),
            'multas' => $this->aCentavos($hojas->multas),
            'total_hojas_ruta' => $this->aCentavos($hojas->total),

            'otros_operativos' => 0,
            'extraordinarios' => 0,
            'patrimoniales' => 0,

            'egresos_ordinarios' => 0,
            'otros_egresos' => 0,

            'total_ingresos' => 0,
            'total_egresos' => 0,
            'resultado' => 0,
        ];

        $clasificacionesValidas = true;

        foreach ($ingresos as $ingreso) {

            $ingreso->centavos = $this->aCentavos(
                $ingreso->total
            );

            switch ($ingreso->naturaleza) {
                case 'OPERATIVO':
                    $totales['otros_operativos'] += $ingreso->centavos;
                    break;

                case 'EXTRAORDINARIO':
                    $totales['extraordinarios'] += $ingreso->centavos;
                    break;

                case 'PATRIMONIAL':
                    $totales['patrimoniales'] += $ingreso->centavos;
                    break;

                default:
                    $clasificacionesValidas = false;
            }
        }

        foreach ($egresos as $egreso) {

            $egreso->centavos = $this->aCentavos(
                $egreso->total
            );

            switch ($egreso->grupo_reporte) {
                case 'ORDINARIO':
                    $totales['egresos_ordinarios'] += $egreso->centavos;
                    break;

                case 'OTROS':
                    $totales['otros_egresos'] += $egreso->centavos;
                    break;

                default:
                    $clasificacionesValidas = false;
            }
        }

        $totales['total_ingresos'] =
            $totales['hojas_ruta']
            + $totales['multas']
            + $totales['otros_operativos']
            + $totales['extraordinarios'];

        $totales['total_egresos'] =
            $totales['egresos_ordinarios']
            + $totales['otros_egresos'];

        $totales['resultado'] =
            $totales['total_ingresos']
            - $totales['total_egresos'];

        /*
         * Verificar que no existan diferencias
         * entre mensualidades, multas y total cobrado.
         */
        $conciliado =
            $clasificacionesValidas
            && (
                $totales['hojas_ruta'] + $totales['multas']
                === $totales['total_hojas_ruta']
            );

        /*
         * Formato monetario sin cálculos con float.
         */
        $formato = static function (int $centavos): string {

            $signo = $centavos < 0 ? '-' : '';

            $absoluto = abs($centavos);

            return $signo . 'Bs '
                . number_format(
                    intdiv($absoluto, 100),
                    0,
                    ',',
                    '.'
                )
                . ','
                . str_pad(
                    (string) ($absoluto % 100),
                    2,
                    '0',
                    STR_PAD_LEFT
                );
        };

        return view(
            'informes.economico.imprimir',
            [
                'nombreMes' => $inicioLocal
                    ->locale('es')
                    ->translatedFormat('F Y'),

                'fechaGeneracion' => now('America/La_Paz')
                    ->format('d/m/Y H:i'),

                'hojas' => $hojas,
                'ingresos' => $ingresos,
                'egresos' => $egresos,
                'totales' => $totales,
                'conciliado' => $conciliado,
                'formato' => $formato,
            ]
        );
    }

    private function aCentavos(
        string|int|null $importe
    ): int {

        $importe = (string) ($importe ?? '0');

        if (! preg_match(
            '/^\d+(?:\.\d{1,2})?$/D',
            $importe
        )) {
            throw new UnexpectedValueException(
                'Se encontró un importe monetario inválido.'
            );
        }

        $partes = explode('.', $importe, 2);

        return ((int) $partes[0] * 100)
            + (int) str_pad(
                $partes[1] ?? '',
                2,
                '0'
            );
    }
}