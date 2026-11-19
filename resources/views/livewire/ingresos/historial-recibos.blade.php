<div class="mx-auto w-full max-w-7xl space-y-6 pb-10">

    {{-- ENCABEZADO --}}
    <div class="rounded-2xl bg-gradient-to-r from-emerald-950 to-emerald-800 p-6 text-white shadow-lg">

        <h1 class="text-2xl font-bold">
            Historial de Recibos
        </h1>

        <p class="mt-2 text-sm text-emerald-100">
            Consulta de pagos por hojas de ruta de la Cooperativa 12 de Mayo R.L.
        </p>

    </div>

    {{-- FILTROS --}}
    <form
        wire:submit="filtrar"
        class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"
    >

        <h2 class="mb-4 text-lg font-semibold">
            Buscar comprobantes
        </h2>

        <div class="grid gap-4 md:grid-cols-4">

            <div>
                <label class="mb-2 block text-sm font-medium">
                    Recibo, afiliado o CI
                </label>

                <input
                    type="text"
                    wire:model="buscar"
                    placeholder="Buscar..."
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-3 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">
                    Desde
                </label>

                <input
                    type="date"
                    wire:model="desde"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-3 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">
                    Hasta
                </label>

                <input
                    type="date"
                    wire:model="hasta"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-3 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">
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
            </div>

        </div>

        @error('buscar')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror

        @error('desde')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror

        @error('hasta')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror

        @error('estado')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror

        <div class="mt-5 flex flex-wrap gap-3">

            <button
                type="submit"
                class="rounded-lg bg-emerald-700 px-5 py-3 font-semibold text-white hover:bg-emerald-800"
            >
                Buscar recibos
            </button>

            <button
                type="button"
                wire:click="limpiarFiltros"
                class="rounded-lg border border-zinc-300 px-5 py-3 font-semibold text-zinc-700 dark:text-zinc-200"
            >
                Limpiar filtros
            </button>

        </div>

    </form>

    {{-- TABLA DE RECIBOS --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

        <div class="border-b border-zinc-200 p-5 dark:border-zinc-700">
            <h2 class="font-semibold">
                Recibos registrados
            </h2>
        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-left text-sm">

                <thead class="bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                    <tr>
                        <th class="px-4 py-3">N.º recibo</th>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Afiliado</th>
                        <th class="px-4 py-3">Mensualidades</th>
                        <th class="px-4 py-3">Cajero</th>
                        <th class="px-4 py-3 text-right">Total Bs</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">

                    @forelse ($recibos as $recibo)

                        <tr wire:key="recibo-{{ $recibo->id }}">

                            <td class="px-4 py-4 font-bold">
                                {{ str_pad((string) $recibo->numero_recibo, 6, '0', STR_PAD_LEFT) }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4">
                                {{ $recibo->fecha_cobro->timezone('America/La_Paz')->format('d/m/Y H:i') }}
                            </td>

                            <td class="px-4 py-4">
                                <div class="font-semibold">
                                    {{ $recibo->afiliado?->nombre_completo ?? '—' }}
                                </div>
                                <div class="text-xs text-zinc-500">
                                    Socio: {{ $recibo->afiliado?->numero_socio ?? '—' }}
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                {{ $recibo->detalles_count }}
                            </td>

                            <td class="px-4 py-4">
                                {{ $recibo->registradoPor?->name ?? '—' }}
                            </td>

                            <td class="px-4 py-4 text-right font-bold">
                                {{ number_format((float) $recibo->total, 2, ',', '.') }}
                            </td>

                            <td class="px-4 py-4">
                                @if($recibo->estado === 'EMITIDO')
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                                        Emitido
                                    </span>
                                @else
                                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">
                                        Anulado
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-4 text-right">
                                <a
                                    href="{{ route('recibos-hoja-ruta.imprimir', ['recibo' => $recibo->id]) }}"
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
                                No se encontraron recibos con los filtros seleccionados.
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

</div>