<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Informe Económico - {{ $nombreMes }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px;
            background: #edf0f3;
            font-family: Arial, Helvetica, sans-serif;
            color: #1f2937;
            font-size: 11px;
        }

        .acciones {
            max-width: 850px;
            margin: 0 auto 15px;
            display: flex;
            gap: 10px;
        }

        .btn {
            border: none;
            border-radius: 6px;
            background: #17644d;
            color: white;
            padding: 11px 20px;
            font-weight: bold;
            cursor: pointer;
        }

        .hoja {
            max-width: 850px;
            margin: auto;
            background: white;
            padding: 30px;
            border: 1px solid #d1d5db;
            box-shadow: 0 3px 14px #00000012;
        }

        .encabezado {
            text-align: center;
            border-bottom: 3px solid #17644d;
            padding-bottom: 15px;
        }

        .cooperativa {
            font-size: 12px;
            font-weight: bold;
        }

        h1 {
            font-size: 23px;
            margin: 5px 0;
            color: #17644d;
        }

        .subtitulo {
            font-size: 15px;
            font-weight: bold;
            margin-top: 12px;
        }

        .periodo {
            font-size: 14px;
            margin-top: 6px;
            text-transform: capitalize;
        }

        .metadatos {
            margin-top: 14px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            color: #64748b;
        }

        .seccion {
            margin-top: 23px;
        }

        h2 {
            font-size: 13px;
            color: #17644d;
            border-bottom: 2px solid #17644d;
            padding-bottom: 7px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background: #eaf5ef;
            padding: 9px 8px;
            text-align: left;
            border-bottom: 1px solid #cbd5e1;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .cantidad {
            text-align: center;
            width: 14%;
        }

        .importe {
            text-align: right;
            white-space: nowrap;
            width: 27%;
        }

        .total-fila {
            background: #f1f5f9;
            font-weight: bold;
        }

        .resumen {
            margin-top: 20px;
            border: 1px solid #cbd5e1;
            padding: 12px 15px;
        }

        .fila-resumen {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            padding: 7px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .fila-resumen:last-child {
            border-bottom: none;
        }

        .resultado {
            margin-top: 15px;
            background: #eaf5ef;
            border: 1px solid #c2e3d1;
            padding: 17px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            font-size: 16px;
            font-weight: bold;
        }

        .negativo {
            background: #fee2e2;
            border-color: #fecaca;
            color: #991b1b;
        }

        .advertencia {
            margin-top: 15px;
            padding: 11px;
            background: #fff7ed;
            border: 1px solid #fdba74;
            color: #9a3412;
        }

        .patrimonio {
            margin-top: 15px;
            background: #f8fafc;
            padding: 12px;
            border: 1px solid #cbd5e1;
        }

        .firmas {
            display: flex;
            gap: 22px;
            margin-top: 70px;
        }

        .firma {
            flex: 1;
            border-top: 1px solid #475569;
            padding-top: 8px;
            text-align: center;
            font-size: 10px;
            line-height: 1.6;
        }

        .pie {
            margin-top: 25px;
            padding-top: 12px;
            border-top: 1px solid #cbd5e1;
            text-align: center;
            color: #64748b;
            font-size: 9px;
            line-height: 1.6;
        }

        @page {
            size: letter portrait;
            margin: 13mm;
        }

        @media print {
            body {
                background: white;
                margin: 0;
                padding: 0;
            }

            .acciones {
                display: none;
            }

            .hoja {
                max-width: none;
                padding: 0;
                border: none;
                box-shadow: none;
            }

            thead {
                display: table-header-group;
            }

            tr,
            .resultado,
            .firmas,
            .fila-resumen {
                break-inside: avoid;
            }

            th,
            .total-fila,
            .resultado {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    <div class="acciones">
        <button
            type="button"
            class="btn"
            onclick="window.print()"
        >
            Imprimir informe
        </button>
    </div>

    <main class="hoja">

        <header class="encabezado">

            <div class="cooperativa">
                COOPERATIVA DE TRANSPORTES
            </div>

            <h1>12 DE MAYO R.L.</h1>

            <div class="subtitulo">
                INFORME ECONÓMICO MENSUAL
            </div>

            <div class="periodo">
                {{ $nombreMes }}
            </div>

        </header>

        <div class="metadatos">
            <span>Moneda: Bolivianos (Bs)</span>
            <span>Generado: {{ $fechaGeneracion }}</span>
        </div>

        {{-- HOJAS DE RUTA --}}

        <section class="seccion">

            <h2>1. RECAUDACIÓN DE HOJAS DE RUTA</h2>

            <table>
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th class="cantidad">Recibos</th>
                        <th class="importe">Importe</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>Hojas de Ruta - Mensualidades</td>
                        <td class="cantidad">{{ $hojas->cantidad }}</td>
                        <td class="importe">
                            {{ $formato($totales['hojas_ruta']) }}
                        </td>
                    </tr>

                    <tr>
                        <td>Multas por mora</td>
                        <td class="cantidad">—</td>
                        <td class="importe">
                            {{ $formato($totales['multas']) }}
                        </td>
                    </tr>

                    <tr class="total-fila">
                        <td colspan="2">TOTAL HOJAS DE RUTA Y MULTAS</td>
                        <td class="importe">
                            {{ $formato($totales['total_hojas_ruta']) }}
                        </td>
                    </tr>
                </tbody>
            </table>

        </section>

        {{-- OTROS INGRESOS --}}

        <section class="seccion">

            <h2>2. OTROS INGRESOS</h2>

            <table>
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th>Naturaleza</th>
                        <th class="cantidad">Recibos</th>
                        <th class="importe">Importe</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($ingresos as $ingreso)
                        <tr>
                            <td>{{ $ingreso->concepto_nombre }}</td>
                            <td>{{ $ingreso->naturaleza }}</td>
                            <td class="cantidad">{{ $ingreso->cantidad }}</td>
                            <td class="importe">
                                {{ $formato($ingreso->centavos) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                Sin ingresos registrados en el período.
                            </td>
                        </tr>
                    @endforelse

                    <tr class="total-fila">
                        <td colspan="3">
                            TOTAL OTROS INGRESOS NO PATRIMONIALES
                        </td>
                        <td class="importe">
                            {{ $formato(
                                $totales['otros_operativos']
                                + $totales['extraordinarios']
                            ) }}
                        </td>
                    </tr>

                </tbody>
            </table>

        </section>

        {{-- EGRESOS --}}

        <section class="seccion">

            <h2>3. EGRESOS</h2>

            <table>
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th>Clasificación</th>
                        <th class="cantidad">Comprobantes</th>
                        <th class="importe">Importe</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($egresos as $egreso)
                        <tr>
                            <td>{{ $egreso->concepto_nombre }}</td>
                            <td>{{ $egreso->grupo_reporte }}</td>
                            <td class="cantidad">{{ $egreso->cantidad }}</td>
                            <td class="importe">
                                {{ $formato($egreso->centavos) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                Sin egresos pagados en el período.
                            </td>
                        </tr>
                    @endforelse

                    <tr class="total-fila">
                        <td colspan="3">TOTAL EGRESOS</td>
                        <td class="importe">
                            {{ $formato($totales['total_egresos']) }}
                        </td>
                    </tr>

                </tbody>
            </table>

        </section>

        {{-- CONSOLIDADO --}}

        <section class="seccion">

            <h2>4. RESUMEN ECONÓMICO CONSOLIDADO</h2>

            <div class="resumen">

                @foreach([
                    ['Hojas de Ruta', 'hojas_ruta'],
                    ['Multas', 'multas'],
                    ['Otros Ingresos operativos', 'otros_operativos'],
                    ['Ingresos extraordinarios', 'extraordinarios'],
                ] as [$etiqueta, $clave])

                    <div class="fila-resumen">
                        <span>{{ $etiqueta }}</span>
                        <strong>{{ $formato($totales[$clave]) }}</strong>
                    </div>

                @endforeach

                <div class="fila-resumen">
                    <strong>TOTAL INGRESOS</strong>
                    <strong>
                        {{ $formato($totales['total_ingresos']) }}
                    </strong>
                </div>

                <div class="fila-resumen">
                    <span>Egresos ordinarios</span>
                    <strong>
                        {{ $formato($totales['egresos_ordinarios']) }}
                    </strong>
                </div>

                <div class="fila-resumen">
                    <span>Otros egresos</span>
                    <strong>
                        {{ $formato($totales['otros_egresos']) }}
                    </strong>
                </div>

                <div class="fila-resumen">
                    <strong>TOTAL EGRESOS</strong>
                    <strong>
                        {{ $formato($totales['total_egresos']) }}
                    </strong>
                </div>

            </div>

            @if($conciliado)

                <div class="resultado {{ $totales['resultado'] < 0 ? 'negativo' : '' }}">
                    <span>RESULTADO DEL PERÍODO</span>
                    <span>
                        {{ $formato($totales['resultado']) }}
                    </span>
                </div>

            @else

                <div class="advertencia">
                    Se detectaron diferencias en los importes
                    o clasificaciones. Resultado pendiente de revisión.
                </div>

            @endif

        </section>

        {{-- PATRIMONIO --}}

        <div class="patrimonio">

            <strong>MOVIMIENTOS PATRIMONIALES</strong>

            <p>
                Total registrado:
                <strong>
                    {{ $formato($totales['patrimoniales']) }}
                </strong>
            </p>

            <small>
                Este importe se presenta por separado y no forma
                parte del resultado económico. Fondo Social
                permanece pendiente de definición.
            </small>

        </div>

        {{-- FIRMAS --}}

        <div class="firmas">

            <div class="firma">
                <strong>ELABORADO POR</strong>
                <div>Nombre y firma</div>
            </div>

            <div class="firma">
                <strong>REVISADO POR</strong>
                <div>Nombre y firma</div>
            </div>

            <div class="firma">
                <strong>APROBADO POR</strong>
                <div>Nombre y firma</div>
            </div>

        </div>

        <footer class="pie">
            Informe administrativo de movimientos registrados
            en el sistema de la Cooperativa 12 de Mayo R.L.
            No constituye por sí solo una conciliación de caja,
            bancos ni estados financieros contables.
        </footer>

    </main>

</body>
</html>