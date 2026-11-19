<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Recibo N.º {{ $recibo->numero_recibo }}
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
            background: #f1f5f9;
            font-family: Arial, sans-serif;
            color: #111827;
            font-size: 12px;
        }

        .barra {
            max-width: 600px;
            margin: 0 auto 15px;
            text-align: right;
        }

        .boton {
            padding: 10px 18px;
            background: #047857;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        .recibo {
            max-width: 600px;
            margin: auto;
            padding: 24px;
            background: white;
            border: 1px solid #d1d5db;
        }

        .encabezado {
            text-align: center;
            border-bottom: 2px solid #111827;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }

        .encabezado h1 {
            margin: 0 0 7px;
            font-size: 18px;
            text-transform: uppercase;
        }

        .titulo {
            font-size: 14px;
            font-weight: bold;
        }

        .numero {
            margin-top: 8px;
            font-size: 15px;
            font-weight: bold;
        }

        .datos {
            line-height: 1.9;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px 5px;
            border-bottom: 1px solid #d1d5db;
        }

        th {
            background: #f3f4f6;
            text-align: left;
        }

        .derecha {
            text-align: right;
        }

        .totales {
            margin-top: 15px;
            margin-left: auto;
            width: 65%;
        }

        .totales td {
            border-bottom: none;
        }

        .total {
            font-size: 16px;
            font-weight: bold;
            border-top: 2px solid #111827;
        }

        .pie {
            margin-top: 45px;
            text-align: center;
        }

        .firma {
            width: 200px;
            margin: 0 auto;
            border-top: 1px solid #111827;
            padding-top: 7px;
        }

        .anulado {
            padding: 8px;
            margin-bottom: 12px;
            border: 2px solid #dc2626;
            color: #dc2626;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
        }

        @media print {
            body {
                padding: 0;
                background: white;
            }

            .barra {
                display: none;
            }

            .recibo {
                max-width: none;
                padding: 0;
                border: none;
            }
        }
    </style>
</head>

<body>

    @php
        $formatoBs = fn ($importe) =>
            number_format((float) $importe, 2, ',', '.');
    @endphp

    <div class="barra">
        <button class="boton" onclick="window.print()">
            Imprimir / Guardar PDF
        </button>
    </div>

    <main class="recibo">

        <div class="encabezado">

            <h1>
                {{ $recibo->team?->name ?? 'Cooperativa 12 de Mayo' }}
            </h1>

            <div class="titulo">
                COMPROBANTE DE PAGO
            </div>

            <div>
                HOJAS DE RUTA
            </div>

            <div class="numero">
                RECIBO N.º
                {{ str_pad((string) $recibo->numero_recibo, 6, '0', STR_PAD_LEFT) }}
            </div>

        </div>

        @if($recibo->estado !== 'EMITIDO')
            <div class="anulado">
                {{ $recibo->estado }} — SIN VALIDEZ
            </div>
        @endif

        <div class="datos">

            <div>
                <strong>Fecha:</strong>
                {{ $recibo->fecha_cobro->timezone('America/La_Paz')->format('d/m/Y H:i') }}
            </div>

            <div>
                <strong>Afiliado:</strong>
                {{ $recibo->afiliado?->nombre_completo }}
            </div>

            <div>
                <strong>N.º de socio:</strong>
                {{ $recibo->afiliado?->numero_socio ?? '—' }}
            </div>

            <div>
                <strong>CI:</strong>
                {{ $recibo->afiliado?->ci ?? '—' }}
            </div>

        </div>

        <table>

            <thead>
                <tr>
                    <th>Período</th>
                    <th>Placa</th>
                    <th class="derecha">Importe (Bs)</th>
                </tr>
            </thead>

            <tbody>

                @foreach($recibo->detalles as $detalle)
                    <tr>
                        <td>
                            {{ $detalle->periodo->format('m/Y') }}
                        </td>

                        <td>
                            {{ $detalle->placa }}
                        </td>

                        <td class="derecha">
                            {{ $formatoBs($detalle->importe) }}
                        </td>
                    </tr>
                @endforeach

            </tbody>

        </table>

        <table class="totales">

            <tr>
                <td>Subtotal</td>
                <td class="derecha">
                    Bs {{ $formatoBs($recibo->subtotal) }}
                </td>
            </tr>

            <tr>
                <td>Multa única</td>
                <td class="derecha">
                    Bs {{ $formatoBs($recibo->multa_unica) }}
                </td>
            </tr>

            <tr class="total">
                <td>TOTAL</td>
                <td class="derecha">
                    Bs {{ $formatoBs($recibo->total) }}
                </td>
            </tr>

        </table>

        <div class="datos" style="margin-top: 20px;">

            <strong>Cobrado por:</strong>
            {{ $recibo->registradoPor?->name ?? '—' }}

        </div>

        <div class="pie">

            <div class="firma">
                Firma y sello del cajero
            </div>

            <p style="margin-top: 25px; font-size: 10px;">
                Comprobante interno de pago de hojas de ruta.
            </p>

        </div>

    </main>

</body>
</html>