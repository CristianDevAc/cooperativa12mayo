<div class="mx-auto w-full max-w-7xl space-y-6 pb-10">

    @php
        $bs = fn ($valor) => number_format(
            (float) $valor, 2, ',', '.'
        );
    @endphp

    {{-- ENCABEZADO --}}
    <div class="rounded-2xl bg-gradient-to-r from-emerald-950 to-emerald-800 p-6 text-white shadow-lg">
        <h1 class="text-2xl font-bold">
            Historial de Otros Ingresos
        </h1>

        <p class="mt-2 text-sm text-emerald-100">
            Consulta de comprobantes y recaudaciones de la
            Cooperativa de Transportes 12 de Mayo R.L.
        </p>
    </div>

    {{-- FILTROS --}}
    <form
        wire:submit="filtrar"
        class="space-y-4 rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"
    >
        <h2 class="text-lg font-bold">
            Buscar comprobantes
        </h2>

        <div class="grid gap-4 md:grid-cols-3">

            <div>
                <label class="mb-2 block text-sm font-semibold">
                    Recibo, afiliado o detalle
                </label>

                <input
                    type="text"
                    wire:model="buscar"
                    maxlength="120"
                    placeholder="Ej. CERTIFICACION-000001"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-3 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
                @error('buscar')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold">
                    Concepto de ingreso
                </label>

                <select
                    wire:model="conceptoId"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-3 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
                    <option value="">Todos los conceptos</option>

                    @foreach($conceptos as $concepto)
                        <option value="{{ $concepto->id }}">
                            {{ $concepto->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('conceptoId')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold">
                    Estado
                </label>

                <select
                    wire:model="estado"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-3 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
                    <option value="">Todos</option>
                    <option value="EMITIDO">Emitidos</option>
                    <option value="ANULADO">Anulados</option>
                </select>
                @error('estado')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold">
                    Fecha desde
                </label>

                <input
                    type="date"
                    wire:model="desde"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-3 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
                @error('desde')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold">
                    Fecha hasta
                </label>

                <input
                    type="date"
                    wire:model="hasta"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-3 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
                @error('hasta')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

        </div>

        <div class="flex flex-wrap gap-3">
            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="filtrar"
                class="rounded-lg bg-emerald-700 px-5 py-3 font-semibold text-white hover:bg-emerald-800"
            >
                Buscar recibos
            </button>

            <button
                type="button"
                wire:click="limpiarFiltros"
                class="rounded-lg border border-zinc-300 px-5 py-3 font-semibold dark:border-zinc-600"
            >
                Limpiar filtros
            </button>
        </div>
    </form>

    {{-- RESUMEN --}}
    <div class="grid gap-4 md:grid-cols-2">

        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500">
                Recibos emitidos
            </p>

            <p class="mt-2 text-3xl font-bold">
                {{ $resumen->cantidad ?? 0 }}
            </p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500">
                Total recaudado
            </p>

            <p class="mt-2 text-3xl font-bold text-emerald-700 dark:text-emerald-400">
                Bs {{ $bs($resumen->total ?? 0) }}
            </p>
        </div>

    </div>

    {{-- TOTALES POR CONCEPTO --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="border-b border-zinc-200 p-5 dark:border-zinc-700">
            <h2 class="text-lg font-bold">
                Recaudación por concepto
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-100 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3">Concepto</th>
                        <th class="px-4 py-3 text-right">Recibos</th>
                        <th class="px-4 py-3 text-right">Total Bs</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($porConcepto as $concepto)
                        <tr>
                            <td class="px-4 py-4 font-semibold">
                                {{ $concepto->concepto_nombre }}
                            </td>

                            <td class="px-4 py-4 text-right">
                                {{ $concepto->cantidad }}
                            </td>

                            <td class="px-4 py-4 text-right font-bold">
                                {{ $bs($concepto->total) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-zinc-500">
                                No hay cobros emitidos con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- HISTORIAL --}}
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
                        <th class="px-4 py-3">Recibo</th>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Concepto</th>
                        <th class="px-4 py-3">Afiliado</th>
                        <th class="px-4 py-3">Cajero</th>
                        <th class="px-4 py-3 text-right">Bs</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Acción</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">

                    @forelse($recibos as $recibo)

                        <tr wire:key="otro-recibo-{{ $recibo->id }}">

                            <td class="whitespace-nowrap px-4 py-4 font-bold">
                                {{ $recibo->concepto_codigo }}-{{ str_pad((string) $recibo->numero_recibo, 6, '0', STR_PAD_LEFT) }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4">
                                {{ $recibo->fecha_cobro->timezone('America/La_Paz')->format('d/m/Y H:i') }}
                            </td>

                            <td class="px-4 py-4">
                                <div class="font-semibold">
                                    {{ $recibo->concepto_nombre }}
                                </div>

                                <div class="mt-1 max-w-xs text-xs text-zinc-500">
                                    {{ $recibo->detalle }}
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                {{ $recibo->afiliado?->nombre_completo ?? 'No aplica' }}
                            </td>

                            <td class="px-4 py-4">
                                {{ $recibo->registradoPor?->name ?? '—' }}
                            </td>

                            <td class="px-4 py-4 text-right font-bold">
                                {{ $bs($recibo->importe) }}
                            </td>

                            <td class="px-4 py-4">
                                @if($recibo->estado === 'EMITIDO')
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                                        Emitido
                                    </span>
                                @else
                                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">
                                        {{ $recibo->estado }}
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-4">
                                <a
                                    href="{{ route('recibos-otros-ingresos.imprimir', [
                                        'recibo' => $recibo->id
                                    ]) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="font-semibold text-blue-700 hover:underline dark:text-blue-400"
                                >
                                    Imprimir
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-zinc-500">
                                No se encontraron comprobantes.
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>

        </div>

        <div class="p-4">
            {{ $recibos->links() }}
        </div>

    </div>

    <p class="text-xs text-zinc-500">
        Los totales corresponden únicamente a recibos emitidos.
        Este reporte no incluye cobros de Hojas de Ruta.
    </p>

</div>