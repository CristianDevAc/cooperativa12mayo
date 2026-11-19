<div class="mx-auto w-full max-w-7xl space-y-6 pb-10">

    {{-- ENCABEZADO --}}
    <div class="rounded-2xl bg-gradient-to-r from-emerald-950 to-emerald-800 p-6 text-white shadow-lg">

        <h1 class="text-2xl font-bold">
            Historial de Egresos
        </h1>

        <p class="mt-2 text-sm text-emerald-100">
            Cooperativa de Transportes 12 de Mayo R.L.
        </p>

        <p class="mt-2 text-sm text-emerald-100">
            Consulta de pagos, comprobantes y gastos registrados.
        </p>

    </div>
    {{-- MENSAJES DE ANULACIÓN --}}
    @if($mensajeAnulacion)
        <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-4 font-semibold text-emerald-800">
            {{ $mensajeAnulacion }}
        </div>
    @endif

    @if($errorAnulacion)
        <div class="rounded-xl border border-red-300 bg-red-50 p-4 font-semibold text-red-800">
            {{ $errorAnulacion }}
        </div>
    @endif

    {{-- FORMULARIO DE ANULACIÓN --}}
    @if($egresoAnularId !== null)

        <div class="rounded-xl border border-red-300 bg-red-50 p-6 text-zinc-900">

            <h2 class="text-lg font-bold text-red-800">
                Anulación de comprobante
            </h2>

            <p class="mt-3 text-sm">
                Comprobante seleccionado:
                <strong>{{ $referenciaAnular }}</strong>
            </p>

            <p class="mt-3 text-sm text-red-800">
                Esta operación conservará el comprobante y registrará
                su anulación. Solo debe utilizarse para un registro
                erróneo o un pago efectivamente revertido.
            </p>

            <div class="mt-5">

                <label class="mb-2 block text-sm font-semibold">
                    Motivo de anulación
                </label>

                <textarea
                    wire:model="motivoAnulacion"
                    rows="3"
                    minlength="10"
                    maxlength="500"
                    placeholder="Explique detalladamente por qué se anula este comprobante."
                    class="w-full rounded-lg border border-red-200 bg-white p-3 text-zinc-900"
                ></textarea>

                @error('motivoAnulacion')
                    <p class="mt-2 text-sm font-semibold text-red-700">
                        {{ $message }}
                    </p>
                @enderror

            </div>

            <div class="mt-5 flex flex-wrap justify-end gap-3">

                <button
                    type="button"
                    wire:click="cancelarAnulacion"
                    class="rounded-lg border border-zinc-300 bg-white px-5 py-3 font-semibold text-zinc-800"
                >
                    Cancelar
                </button>

                <button
                    type="button"
                    wire:click="confirmarAnulacion"
                    wire:confirm="¿Está seguro de anular este comprobante? Su estado cambiará a ANULADO y quedará registrada la operación."
                    wire:loading.attr="disabled"
                    wire:target="confirmarAnulacion"
                    class="rounded-lg bg-red-700 px-5 py-3 font-bold text-white hover:bg-red-800 disabled:opacity-50"
                >
                    Confirmar anulación
                </button>

            </div>

        </div>

    @endif
    {{-- FILTROS --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

        <h2 class="mb-5 text-lg font-bold">
            Filtros de búsqueda
        </h2>

        <div class="grid gap-4 md:grid-cols-3">

            <div>
                <label class="mb-2 block text-sm font-semibold">
                    Beneficiario o detalle
                </label>

                <input
                    type="text"
                    wire:model.live.debounce.400ms="busqueda"
                    maxlength="150"
                    placeholder="Buscar..."
                    class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold">
                    N.º de comprobante
                </label>

                <input
                    type="text"
                    wire:model.live.debounce.400ms="numeroComprobante"
                    placeholder="EGR-000001"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold">
                    Concepto
                </label>

                <select
                    wire:model.live="conceptoEgresoId"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
                    <option value="">Todos los conceptos</option>

                    @foreach($conceptos as $concepto)
                        <option value="{{ $concepto->id }}">
                            {{ $concepto->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold">
                    Desde
                </label>

                <input
                    type="date"
                    wire:model.live="fechaDesde"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold">
                    Hasta
                </label>

                <input
                    type="date"
                    wire:model.live="fechaHasta"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold">
                    Estado
                </label>

                <select
                    wire:model.live="estado"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
                    <option value="">Todos los estados</option>
                    <option value="PAGADO">Pagados</option>
                    <option value="ANULADO">Anulados</option>
                </select>
            </div>

        </div>

        @if($errorFechas)
            <p class="mt-4 text-sm font-semibold text-red-600">
                {{ $errorFechas }}
            </p>
        @endif

        <div class="mt-5 flex justify-end">

            <button
                type="button"
                wire:click="limpiarFiltros"
                class="rounded-lg border border-zinc-300 px-5 py-3 text-sm font-semibold hover:bg-zinc-100 dark:border-zinc-600 dark:hover:bg-zinc-800"
            >
                Limpiar filtros
            </button>

        </div>

    </div>

    {{-- RESUMEN --}}
    <div class="grid gap-4 md:grid-cols-2">

        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

            <p class="text-sm text-zinc-500">
                Comprobantes encontrados
            </p>

            <p class="mt-2 text-3xl font-bold">
                {{ number_format($cantidad, 0, ',', '.') }}
            </p>

        </div>

        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm dark:border-emerald-900 dark:bg-emerald-950">

            <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-200">
                Total pagado
            </p>

            <p class="mt-2 text-3xl font-bold text-emerald-800 dark:text-emerald-200">
                Bs {{ number_format((float) $totalPagado, 2, ',', '.') }}
            </p>

            <p class="mt-1 text-xs text-emerald-700 dark:text-emerald-300">
                Suma de comprobantes PAGADOS según los filtros.
            </p>

        </div>

    </div>

    {{-- TABLA --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

        <div class="border-b border-zinc-200 p-5 dark:border-zinc-700">
            <h2 class="text-lg font-bold">
                Comprobantes registrados
            </h2>
        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-left text-sm">

                <thead class="bg-zinc-100 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3">Comprobante</th>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Concepto</th>
                        <th class="px-4 py-3">Beneficiario</th>
                        <th class="px-4 py-3 text-right">Importe</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Acción</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">

                    @forelse($egresos as $egreso)

                        <tr wire:key="egreso-historial-{{ $egreso->id }}">

                            <td class="px-4 py-4 font-bold">
                                EGR-{{ str_pad(
                                    (string) $egreso->numero_comprobante,
                                    6,
                                    '0',
                                    STR_PAD_LEFT
                                ) }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4">
                                {{ $egreso->fecha_pago->copy()
                                    ->timezone('America/La_Paz')
                                    ->format('d/m/Y H:i') }}
                            </td>

                            <td class="px-4 py-4">

                                <p class="font-semibold">
                                    {{ $egreso->concepto_nombre }}
                                </p>

                                <p class="mt-1 text-xs text-zinc-500">
                                    {{ $egreso->grupo_reporte }}
                                </p>

                            </td>

                            <td class="px-4 py-4">

                                <p class="font-semibold">
                                    {{ $egreso->beneficiario }}
                                </p>

                                <p class="mt-1 max-w-xs text-xs text-zinc-500">
                                    {{ $egreso->detalle }}
                                </p>

                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-right font-bold">
                                Bs {{ number_format(
                                    (float) $egreso->importe,
                                    2,
                                    ',',
                                    '.'
                                ) }}
                            </td>

                            <td class="px-4 py-4">

                                @if($egreso->estado === 'PAGADO')
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                                        PAGADO
                                    </span>
                                @else
                                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">
                                        ANULADO
                                    </span>
                                @endif

                            </td>

                            <td class="px-4 py-4">

                                <a
                                    href="{{ route('egresos.imprimir', [
                                        'current_team' => request()->route('current_team'),
                                        'egreso' => $egreso->id,
                                    ]) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex whitespace-nowrap rounded-lg bg-emerald-700 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-800"
                                >
                                    Imprimir
                                </a>
                                @can('anular-egresos')

                                    @if($egreso->estado === 'PAGADO')

                                        <button
                                            type="button"
                                            wire:click="solicitarAnulacion({{ $egreso->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="solicitarAnulacion"
                                            class="mt-2 inline-flex rounded-lg
                                                bg-red-700 px-4 py-2
                                                text-xs font-semibold text-white
                                                hover:bg-red-800 disabled:opacity-50"
                                        >
                                            Anular
                                            @if($egreso->motivo_anulacion)

                                                <p class="mt-2 max-w-xs text-xs text-red-700 dark:text-red-400">
                                                    Motivo: {{ $egreso->motivo_anulacion }}
                                                </p>

                                            @endif
                                        </button>

                                    @endif

                                @endcan

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-zinc-500">
                                No existen egresos que coincidan con los filtros.
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>

        </div>

        @if($egresos->hasPages())
            <div class="border-t border-zinc-200 p-5 dark:border-zinc-700">
                {{ $egresos->links() }}
            </div>
        @endif

    </div>

    <p class="text-xs text-zinc-500">
        Los totales incluyen todos los resultados filtrados,
        aunque existan varias páginas de comprobantes.
    </p>

</div>