<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        EGR-{{ str_pad((string) $registro->numero_comprobante, 6, '0', STR_PAD_LEFT) }}
    </title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px;
            background: #edf0f3;
            font-family: Arial, Helvetica, sans-serif;
            color: #1f2937;
            font-size: 12px;
        }

        .acciones {
            max-width: 820px;
            margin: 0 auto 20px;
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 11px 22px;
            background: #17644d;
            color: #fff;
            text-decoration: none;
            border: 0;
            border-radius: 6px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-secundario {
            background: #475569;
        }

        .hoja {
            max-width: 820px;
            margin: auto;
            padding: 38px 42px;
            background: #fff;
            border: 1px solid #d1d5db;
            box-shadow: 0 4px 18px #00000012;
        }

        .encabezado {
            text-align: center;
            border-bottom: 3px solid #17644d;
            padding-bottom: 18px;
        }

        .empresa {
            margin: 0;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .nombre {
            margin: 5px 0;
            font-size: 24px;
            color: #17644d;
        }

        .subtitulo {
            margin-top: 12px;
            font-size: 15px;
            font-weight: bold;
        }

        .numero {
            margin-top: 8px;
            font-size: 17px;
            font-weight: bold;
        }

        .anulado {
            margin: 12px 0;
            padding: 10px;
            background: #fee2e2;
            color: #991b1b;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }

        .seccion {
            margin-top: 23px;
        }

        .titulo-seccion {
            padding: 9px 12px;
            background: #eaf5ef;
            border-left: 4px solid #17644d;
            color: #14533e;
            font-size: 12px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        td {
            padding: 9px 8px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
            overflow-wrap: anywhere;
        }

        td.etiqueta {
            width: 34%;
            color: #64748b;
        }

        td.valor {
            font-weight: 600;
        }

        .detalle {
            margin-top: 10px;
            padding: 14px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            line-height: 1.7;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .total {
            margin-top: 24px;
            padding: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            background: #eaf5ef;
            border: 1px solid #c2e3d1;
            font-size: 17px;
            font-weight: bold;
        }

        .total-importe {
            font-size: 23px;
            white-space: nowrap;
            color: #14533e;
        }

        .firmas {
            display: flex;
            justify-content: space-between;
            gap: 45px;
            margin-top: 85px;
        }

        .firma {
            flex: 1;
            border-top: 1px solid #475569;
            padding-top: 10px;
            text-align: center;
            line-height: 1.6;
        }

        .pie {
            margin-top: 36px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #64748b;
        }

        @page {
            size: letter portrait;
            margin: 15mm;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                background: white;
                font-size: 11px;
            }

            .acciones {
                display: none;
            }

            .hoja {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 0;
                border: 0;
                box-shadow: none;
            }

            .seccion,
            .total,
            .firmas {
                break-inside: avoid;
            }

            .titulo-seccion,
            .total {
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
            Imprimir comprobante
        </button>

        <button
            type="button"
            class="btn btn-secundario"
            onclick="window.close()"
        >
            Cerrar
        </button>

    </div>

    <main class="hoja">

        <header class="encabezado">

            <p class="empresa">
                COOPERATIVA DE TRANSPORTES
            </p>

            <h1 class="nombre">
                12 DE MAYO R.L.
            </h1>

            <div class="subtitulo">
                COMPROBANTE DE EGRESO
            </div>

            <div class="numero">
                EGR-{{ str_pad(
                    (string) $registro->numero_comprobante,
                    6,
                    '0',
                    STR_PAD_LEFT
                ) }}
            </div>

        </header>

        @if($registro->estado === 'ANULADO')
            <div class="anulado">
                COMPROBANTE ANULADO
            </div>

            <div class="seccion">

                <div class="titulo-seccion">
                    INFORMACIÓN DE ANULACIÓN
                </div>

                <table>

                    <tr>
                        <td class="etiqueta">Fecha de anulación</td>
                        <td class="valor">
                            {{ $registro->anulado_en?->copy()
                                ->timezone('America/La_Paz')
                                ->format('d/m/Y H:i') ?? 'No disponible' }}
                        </td>
                    </tr>

                    <tr>
                        <td class="etiqueta">Anulado por</td>
                        <td class="valor">
                            {{ $registro->anuladoPor?->name ?? 'No disponible' }}
                        </td>
                    </tr>

                    <tr>
                        <td class="etiqueta">Motivo</td>
                        <td class="valor">
                            {{ $registro->motivo_anulacion }}
                        </td>
                    </tr>

                </table>

            </div>

        @endif

        <section class="seccion">

            <div class="titulo-seccion">
                INFORMACIÓN DEL COMPROBANTE
            </div>

            <table>
                <tr>
                    <td class="etiqueta">Fecha y hora del pago</td>
                    <td class="valor">
                        {{ $registro->fecha_pago
                            ? $registro->fecha_pago->copy()
                                ->timezone('America/La_Paz')
                                ->format('d/m/Y H:i')
                            : 'Sin fecha' }}
                    </td>
                </tr>

                <tr>
                    <td class="etiqueta">Número de comprobante</td>
                    <td class="valor">
                        EGR-{{ str_pad(
                            (string) $registro->numero_comprobante,
                            6,
                            '0',
                            STR_PAD_LEFT
                        ) }}
                    </td>
                </tr>

                <tr>
                    <td class="etiqueta">Estado</td>
                    <td class="valor">
                        {{ $registro->estado }}
                    </td>
                </tr>

                <tr>
                    <td class="etiqueta">Registrado por</td>
                    <td class="valor">
                        {{ $registro->registradoPor?->name ?? 'No disponible' }}
                    </td>
                </tr>
            </table>

        </section>

        <section class="seccion">

            <div class="titulo-seccion">
                DATOS DEL EGRESO
            </div>

            <table>
                <tr>
                    <td class="etiqueta">Beneficiario</td>
                    <td class="valor">
                        {{ $registro->beneficiario }}
                    </td>
                </tr>

                <tr>
                    <td class="etiqueta">Concepto</td>
                    <td class="valor">
                        {{ $registro->concepto_nombre }}
                    </td>
                </tr>

                <tr>
                    <td class="etiqueta">Clasificación</td>
                    <td class="valor">
                        {{ $registro->grupo_reporte }}
                    </td>
                </tr>

                <tr>
                    <td class="etiqueta">Medio de pago</td>
                    <td class="valor">
                        {{ $registro->medio_pago }}
                    </td>
                </tr>

                @if($registro->referencia_pago)
                    <tr>
                        <td class="etiqueta">Referencia de pago</td>
                        <td class="valor">
                            {{ $registro->referencia_pago }}
                        </td>
                    </tr>
                @endif

                @if($registro->tipo_documento)
                    <tr>
                        <td class="etiqueta">Documento de respaldo</td>
                        <td class="valor">
                            {{ $registro->tipo_documento }}
                            {{ $registro->numero_documento }}
                        </td>
                    </tr>
                @endif

            </table>

        </section>

        <section class="seccion">

            <div class="titulo-seccion">
                DETALLE DEL GASTO
            </div>

            <div class="detalle">{{ $registro->detalle }}</div>

            @if($registro->observaciones)
                <p>
                    <strong>Observaciones:</strong>
                    {{ $registro->observaciones }}
                </p>
            @endif

        </section>

        <div class="total">

            <span>
                {{ $registro->estado === 'ANULADO'
                    ? 'IMPORTE DEL COMPROBANTE ANULADO'
                    : 'TOTAL PAGADO' }}
            </span>

            <span class="total-importe">
                Bs {{ number_format(
                    (float) $registro->importe,
                    2,
                    ',',
                    '.'
                ) }}
            </span>

        </div>

        <div class="firmas">

            <div class="firma">
                <strong>ENTREGUÉ CONFORME</strong>
                <div>Firma del responsable</div>
            </div>

            <div class="firma">
                <strong>RECIBÍ CONFORME</strong>
                <div>Firma del beneficiario</div>
            </div>

        </div>

        <footer class="pie">
            Comprobante interno de egreso.
            Cooperativa de Transportes 12 de Mayo R.L.
        </footer>

    </main>

</body>
</html>