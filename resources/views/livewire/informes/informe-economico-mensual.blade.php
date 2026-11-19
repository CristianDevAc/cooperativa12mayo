<div class="mx-auto w-full max-w-7xl space-y-6 pb-10">

    {{-- ENCABEZADO --}}
    <div class="rounded-2xl bg-gradient-to-r from-emerald-950 to-emerald-800 p-6 text-white shadow-lg">

        <h1 class="text-2xl font-bold">
            Informe Económico Mensual
        </h1>

        <p class="mt-2 text-sm text-emerald-100">
            Cooperativa de Transportes 12 de Mayo R.L.
        </p>

        <p class="mt-2 text-sm text-emerald-100">
            Resumen administrativo de ingresos y egresos.
        </p>

    </div>

    {{-- FILTROS --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

        <h2 class="mb-4 text-lg font-bold">
            Período del informe
        </h2>

        <div class="grid gap-4 md:grid-cols-2">

            <div>
                <label class="mb-2 block text-sm font-semibold">
                    Mes
                </label>

                <select
                    wire:model.live="mes"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
                    @foreach(range(1, 12) as $numeroMes)
                        <option value="{{ $numeroMes }}">
                            {{ \Carbon\CarbonImmutable::create(2026, $numeroMes, 1)->locale('es')->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold">
                    Año
                </label>

                <select
                    wire:model.live="anio"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
                    @for($a = now('America/La_Paz')->year + 1; $a >= 2020; $a--)
                        <option value="{{ $a }}">
                            {{ $a }}
                        </option>
                    @endfor
                </select>
            </div>

        </div>

    </div>

    <div class="rounded-xl border border-blue-300 bg-blue-50 p-4 text-sm text-blue-900">

        <strong>Resumen económico de movimientos registrados:</strong>

        se incluyen Hojas de Ruta, multas, Otros Ingresos
        y egresos pagados.

        Los movimientos patrimoniales se presentan
        separadamente y no forman parte del resultado.

        El saldo real de caja y bancos deberá conciliarse
        con el saldo inicial del período.

    </div>

    <div class="flex flex-wrap items-center justify-between gap-4">

        <h2 class="text-xl font-bold capitalize">
            {{ $nombreMes }}
        </h2>

        <a
            href="{{ route('informes.economico.imprimir', [
                'current_team' => request()->route('current_team'),
                'anio' => $anio,
                'mes' => $mes,
            ]) }}"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center gap-2 rounded-lg
                bg-emerald-700 px-5 py-3
                text-sm font-bold text-white
                hover:bg-emerald-800"
        >
            Imprimir informe
        </a>

    </div>
    {{-- RECAUDACIÓN DE HOJAS DE RUTA --}}

    <h2 class="text-xl font-bold">
        1. Recaudación de Hojas de Ruta y Multas
    </h2>

    <div class="grid gap-4 md:grid-cols-4">

        {{-- COMPROBANTES --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

            <p class="text-sm text-zinc-500">
                Recibos emitidos
            </p>

            <p class="mt-2 text-3xl font-bold">
                {{ number_format(
                    (int) $resumenHojasRuta->cantidad,
                    0,
                    ',',
                    '.'
                ) }}
            </p>

        </div>

        {{-- HOJAS DE RUTA --}}
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-5">

            <p class="text-sm font-semibold text-blue-800">
                Hojas de Ruta
            </p>

            <p class="mt-2 text-2xl font-bold text-blue-900">
                Bs {{ number_format(
                    (float) $resumenHojasRuta->subtotal,
                    2,
                    ',',
                    '.'
                ) }}
            </p>

            <p class="mt-1 text-xs text-blue-700">
                Recaudación por mensualidades
            </p>

        </div>

        {{-- MULTAS --}}
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">

            <p class="text-sm font-semibold text-amber-800">
                Multas
            </p>

            <p class="mt-2 text-2xl font-bold text-amber-900">
                Bs {{ number_format(
                    (float) $resumenHojasRuta->multas,
                    2,
                    ',',
                    '.'
                ) }}
            </p>

            <p class="mt-1 text-xs text-amber-700">
                Multas efectivamente cobradas
            </p>

        </div>

        {{-- TOTAL --}}
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5">

            <p class="text-sm font-semibold text-emerald-800">
                Total recaudado
            </p>

            <p class="mt-2 text-2xl font-bold text-emerald-900">
                Bs {{ number_format(
                    (float) $resumenHojasRuta->total,
                    2,
                    ',',
                    '.'
                ) }}
            </p>

            <p class="mt-1 text-xs text-emerald-700">
                Hojas de Ruta + multas
            </p>

        </div>

    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-5 text-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

        <p class="font-semibold">
            Control de recaudación
        </p>

        <p class="mt-2 text-zinc-600 dark:text-zinc-400">
            El total corresponde únicamente a los recibos emitidos
            durante el período seleccionado. Los recibos anulados
            quedan excluidos del informe.
        </p>

    </div>

    {{-- RESUMEN DE INGRESOS --}}
    <h2 class="text-xl font-bold">
        2. Otros Ingresos
    </h2>

    <div class="grid gap-4 md:grid-cols-3">

        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5">
            <p class="text-sm font-semibold text-emerald-800">
                Ingresos operativos
            </p>

            <p class="mt-2 text-2xl font-bold text-emerald-900">
                Bs {{ number_format((float) $resumenIngresos->operativos, 2, ',', '.') }}
            </p>
        </div>

        <div class="rounded-xl border border-blue-200 bg-blue-50 p-5">
            <p class="text-sm font-semibold text-blue-800">
                Ingresos extraordinarios
            </p>

            <p class="mt-2 text-2xl font-bold text-blue-900">
                Bs {{ number_format((float) $resumenIngresos->extraordinarios, 2, ',', '.') }}
            </p>
        </div>

        <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">
            <p class="text-sm font-semibold text-amber-800">
                Movimientos patrimoniales
            </p>

            <p class="mt-2 text-2xl font-bold text-amber-900">
                Bs {{ number_format((float) $resumenIngresos->patrimoniales, 2, ',', '.') }}
            </p>

            <p class="mt-1 text-xs text-amber-800">
                No se incluyen en los ingresos operativos.
            </p>
        </div>

    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

        <div class="border-b border-zinc-200 p-5 dark:border-zinc-700">
            <h3 class="font-bold">
                Detalle de Otros Ingresos
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">

                <thead class="bg-zinc-100 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3">Concepto</th>
                        <th class="px-4 py-3">Naturaleza</th>
                        <th class="px-4 py-3 text-center">Recibos</th>
                        <th class="px-4 py-3 text-right">Importe (Bs)</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">

                    @forelse($ingresos as $ingreso)
                        <tr wire:key="ingreso-{{ $ingreso->concepto_codigo }}-{{ $ingreso->naturaleza }}">

                            <td class="px-4 py-3">
                                {{ $ingreso->concepto_nombre }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $ingreso->naturaleza }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                {{ $ingreso->cantidad }}
                            </td>

                            <td class="px-4 py-3 text-right font-semibold">
                                {{ number_format((float) $ingreso->total, 2, ',', '.') }}
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-zinc-500">
                                No existen otros ingresos registrados en este período.
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>
        </div>
    </div>

    {{-- RESUMEN DE EGRESOS --}}
    <h2 class="text-xl font-bold">
        3. Egresos
    </h2>

    <div class="grid gap-4 md:grid-cols-3">

        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500">
                Egresos ordinarios
            </p>

            <p class="mt-2 text-2xl font-bold">
                Bs {{ number_format((float) $resumenEgresos->ordinarios, 2, ',', '.') }}
            </p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500">
                Otros egresos
            </p>

            <p class="mt-2 text-2xl font-bold">
                Bs {{ number_format((float) $resumenEgresos->otros, 2, ',', '.') }}
            </p>
        </div>

        <div class="rounded-xl border border-red-200 bg-red-50 p-5">
            <p class="text-sm font-semibold text-red-800">
                Total egresos pagados
            </p>

            <p class="mt-2 text-2xl font-bold text-red-900">
                Bs {{ number_format((float) $resumenEgresos->total, 2, ',', '.') }}
            </p>
        </div>

    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

        <div class="border-b border-zinc-200 p-5 dark:border-zinc-700">
            <h3 class="font-bold">
                Detalle de Egresos
            </h3>
        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-left text-sm">

                <thead class="bg-zinc-100 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3">Concepto</th>
                        <th class="px-4 py-3">Clasificación</th>
                        <th class="px-4 py-3 text-center">Comprobantes</th>
                        <th class="px-4 py-3 text-right">Importe (Bs)</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">

                    @forelse($egresos as $egreso)
                        <tr wire:key="egreso-resumen-{{ $egreso->concepto_codigo }}-{{ $egreso->grupo_reporte }}">

                            <td class="px-4 py-3">
                                {{ $egreso->concepto_nombre }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $egreso->grupo_reporte }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                {{ $egreso->cantidad }}
                            </td>

                            <td class="px-4 py-3 text-right font-semibold">
                                {{ number_format((float) $egreso->total, 2, ',', '.') }}
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-zinc-500">
                                No existen egresos pagados en este período.
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>
    </div>

    
    {{-- RESUMEN ECONÓMICO CONSOLIDADO --}}

    <div class="rounded-2xl bg-gradient-to-r from-emerald-950 to-emerald-800 p-6 text-white shadow-lg">

        <h2 class="text-2xl font-bold">
            4. Resumen Económico Consolidado
        </h2>

        <p class="mt-2 text-sm text-emerald-100 capitalize">
            {{ $nombreMes }}
        </p>

    </div>

    @if(! $consolidado['conciliacion_correcta'])

        <div class="rounded-xl border border-red-300 bg-red-50 p-4 text-sm font-semibold text-red-800">

            Se detectó una diferencia entre los subtotales
            y los importes registrados.

            Revise la recaudación de Hojas de Ruta
            y la clasificación de los egresos antes
            de utilizar este resultado.

        </div>

    @endif

    <div class="grid gap-6 lg:grid-cols-2">

        {{-- INGRESOS --}}
        <div class="overflow-hidden rounded-xl border border-emerald-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

            <div class="bg-emerald-700 px-5 py-4 text-lg font-bold text-white">
                INGRESOS
            </div>

            <div class="space-y-4 p-5 text-sm">

                @foreach([
                    ['Hojas de Ruta', 'hojas_ruta'],
                    ['Multas', 'multas'],
                    ['Otros Ingresos operativos', 'otros_operativos'],
                    ['Ingresos extraordinarios', 'extraordinarios'],
                ] as [$nombre, $clave])

                    <div class="flex justify-between gap-4">
                        <span>{{ $nombre }}</span>

                        <strong>
                            Bs {{ number_format(
                                $consolidado[$clave] / 100,
                                2,
                                ',',
                                '.'
                            ) }}
                        </strong>
                    </div>

                @endforeach

                <div class="flex justify-between gap-4 border-t-2 border-emerald-600 pt-4 text-lg font-bold text-emerald-800 dark:text-emerald-300">

                    <span>TOTAL INGRESOS</span>

                    <span>
                        Bs {{ number_format(
                            $consolidado['total_ingresos'] / 100,
                            2,
                            ',',
                            '.'
                        ) }}
                    </span>

                </div>

            </div>

        </div>

        {{-- EGRESOS --}}
        <div class="overflow-hidden rounded-xl border border-red-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

            <div class="bg-red-700 px-5 py-4 text-lg font-bold text-white">
                EGRESOS
            </div>

            <div class="space-y-4 p-5 text-sm">

                <div class="flex justify-between gap-4">

                    <span>Egresos ordinarios</span>

                    <strong>
                        Bs {{ number_format(
                            $consolidado['egresos_ordinarios'] / 100,
                            2,
                            ',',
                            '.'
                        ) }}
                    </strong>

                </div>

                <div class="flex justify-between gap-4">

                    <span>Otros egresos</span>

                    <strong>
                        Bs {{ number_format(
                            $consolidado['otros_egresos'] / 100,
                            2,
                            ',',
                            '.'
                        ) }}
                    </strong>

                </div>

                <div class="flex justify-between gap-4 border-t-2 border-red-600 pt-4 text-lg font-bold text-red-800 dark:text-red-300">

                    <span>TOTAL EGRESOS</span>

                    <span>
                        Bs {{ number_format(
                            $consolidado['total_egresos'] / 100,
                            2,
                            ',',
                            '.'
                        ) }}
                    </span>

                </div>

            </div>

        </div>

    </div>

    {{-- RESULTADO DEL PERÍODO --}}

    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

        <p class="text-sm font-semibold text-zinc-500">
            RESULTADO NETO DEL PERÍODO
        </p>

        @if($consolidado['conciliacion_correcta'])

            <p class="mt-3 text-4xl font-bold
                {{ $consolidado['resultado'] >= 0
                    ? 'text-emerald-700 dark:text-emerald-400'
                    : 'text-red-700 dark:text-red-400' }}">

                {{ $consolidado['resultado'] < 0 ? '-' : '' }}
                Bs {{ number_format(
                    abs($consolidado['resultado']) / 100,
                    2,
                    ',',
                    '.'
                ) }}

            </p>

            <p class="mt-3 text-sm text-zinc-500">

                {{ $consolidado['resultado'] >= 0
                    ? 'Los ingresos registrados son iguales o superiores a los egresos pagados.'
                    : 'Los egresos pagados superan a los ingresos registrados en este período.' }}

            </p>

        @else

            <p class="mt-3 font-semibold text-red-700">
                Resultado pendiente de conciliación.
            </p>

        @endif

    </div>

    {{-- MOVIMIENTOS PATRIMONIALES --}}

    <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">

        <h3 class="font-bold text-amber-900">
            Movimientos patrimoniales registrados
        </h3>

        <p class="mt-2 text-2xl font-bold text-amber-900">

            Bs {{ number_format(
                $consolidado['patrimoniales'] / 100,
                2,
                ',',
                '.'
            ) }}

        </p>

        <p class="mt-2 text-xs text-amber-800">
            Este importe se informa por separado y no se suma
            al resultado neto del período. Fondo Social
            permanece pendiente de definición y configuración.
        </p>

    </div>






    <p class="text-xs text-zinc-500">
        Los ingresos se agrupan según su fecha de cobro.
        Los egresos se agrupan según su fecha de pago.
        Los comprobantes de egreso anulados no forman parte
        de los totales pagados.
    </p>

</div>