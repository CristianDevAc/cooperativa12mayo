<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Recibo de ingreso N.º {{ $recibo->numero_recibo }}
    </title>

    <style>
        @page {
            size: A5 portrait;
            margin: 12mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 20px;
            background: #f3f4f6;
            font-family: Arial, sans-serif;
            color: #18181b;
            font-size: 12px;
        }

        .herramientas {
            max-width: 600px;
            margin: 0 auto 15px;
            text-align: right;
        }

        .boton {
            background: #047857;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 12px 20px;
            font-weight: bold;
            cursor: pointer;
        }

        .recibo {
            max-width: 600px;
            margin: auto;
            padding: 25px;
            background: white;
            border: 1px solid #d4d4d8;
        }

        .encabezado {
            text-align: center;
            border-bottom: 3px solid #166534;
            padding-bottom: 15px;
        }

        .cooperativa {
            font-size: 17px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .subtitulo {
            margin-top: 6px;
            font-size: 13px;
            font-weight: bold;
        }

        .numero {
            margin-top: 12px;
            font-size: 17px;
            color: #991b1b;
            font-weight: bold;
        }

        .datos {
            margin-top: 20px;
        }

        .fila {
            display: flex;
            gap: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #e4e4e7;
        }

        .etiqueta {
            width: 115px;
            flex-shrink: 0;
            font-weight: bold;
        }

        .valor {
            flex: 1;
            overflow-wrap: anywhere;
        }

        .concepto {
            margin-top: 20px;
            padding: 15px;
            background: #f4f4f5;
            border-left: 4px solid #166534;
        }

        .concepto h3 {
            margin: 0 0 8px;
            font-size: 13px;
        }

        .concepto p {
            margin: 0;
            line-height: 1.6;
        }

        .total {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 20px;
            padding: 15px;
            border: 2px solid #166534;
            font-size: 17px;
            font-weight: bold;
        }

        .total span:last-child {
            color: #166534;
        }

        .pie {
            margin-top: 60px;
            text-align: center;
        }

        .firma {
            width: 210px;
            margin: auto;
            padding-top: 8px;
            border-top: 1px solid #18181b;
        }

        .nota {
            margin-top: 25px;
            font-size: 10px;
            color: #52525b;
            text-align: center;
        }

        .anulado {
            margin-top: 15px;
            padding: 10px;
            border: 2px solid #dc2626;
            color: #dc2626;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .herramientas {
                display: none;
            }

            .recibo {
                max-width: none;
                border: none;
                padding: 0;
            }
        }
    </style>
</head>

<body>

    <div class="herramientas">

        <button
            type="button"
            class="boton"
            onclick="window.print()"
        >
            Imprimir / Guardar PDF
        </button>

    </div>

    <main class="recibo">

        <header class="encabezado">

            <div class="cooperativa">
                {{ $recibo->team?->name ?? 'Cooperativa de Transportes 12 de Mayo R.L.' }}
            </div>

            <div class="subtitulo">
                COMPROBANTE DE OTROS INGRESOS
            </div>

            <div class="numero">
                RECIBO N.º
                {{ $recibo->concepto_codigo }}-{{ str_pad((string) $recibo->numero_recibo, 6, '0', STR_PAD_LEFT) }}
            </div>

        </header>

        @if($recibo->estado !== 'EMITIDO')
            <div class="anulado">
                {{ $recibo->estado }} — SIN VALIDEZ
            </div>
        @endif

        <section class="datos">

            <div class="fila">
                <span class="etiqueta">Fecha:</span>
                <span class="valor">
                    {{ $recibo->fecha_cobro->timezone('America/La_Paz')->format('d/m/Y H:i') }}
                </span>
            </div>

            <div class="fila">
                <span class="etiqueta">Afiliado:</span>
                <span class="valor">
                    {{ $recibo->afiliado?->nombre_completo ?? 'No aplica' }}
                </span>
            </div>

            <div class="fila">
                <span class="etiqueta">N.º socio:</span>
                <span class="valor">
                    {{ $recibo->afiliado?->numero_socio ?? '—' }}
                </span>
            </div>

            <div class="fila">
                <span class="etiqueta">CI:</span>
                <span class="valor">
                    {{ $recibo->afiliado?->ci ?? '—' }}
                </span>
            </div>
            @if($recibo->historial_vehiculo_id)

                <div class="fila">
                    <span class="etiqueta">Cambio de herramienta:</span>

                    <span class="valor">
                        Movimiento N.º {{ str_pad(
                            (string) $recibo->historial_vehiculo_id,
                            6,
                            '0',
                            STR_PAD_LEFT
                        ) }}
                    </span>
                </div>

            @endif

        </section>

        <section class="concepto">

            <h3>CONCEPTO DE INGRESO</h3>

            <p>
                <strong>{{ $recibo->concepto_nombre }}</strong>
            </p>

            <p style="margin-top: 8px;">
                {{ $recibo->detalle }}
            </p>

        </section>

        <div class="total">

            <span>TOTAL PAGADO</span>

            <span>
                Bs {{ number_format((float) $recibo->importe, 2, ',', '.') }}
            </span>

        </div>

        <div class="datos">

            <div class="fila">
                <span class="etiqueta">Registrado por:</span>

                <span class="valor">
                    {{ $recibo->registradoPor?->name ?? '—' }}
                </span>
            </div>

        </div>

        <footer class="pie">

            <div class="firma">
                Firma y sello del cajero
            </div>

            <p class="nota">
                Comprobante interno de pago.
                No constituye factura tributaria.
            </p>

        </footer>

    </main>

</body>

</html>