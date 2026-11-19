<div class="space-y-6">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                Hojas de Ruta
            </h1>

            <p class="text-sm text-zinc-500">
                Gestión de ingresos por hojas de ruta
            </p>
        </div>

        <span
            class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
            Módulo de Ingresos
        </span>

    </div>

    {{-- BÚSQUEDA --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

        <h2 class="mb-4 text-lg font-semibold">
            Buscar afiliado
        </h2>

        <input
            type="text"
            wire:model.live.debounce.300ms="buscar"
            placeholder="Buscar por nombre, CI o placa..."
            class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none focus:border-emerald-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
        >

        <p class="mt-2 text-xs text-zinc-500">
            Se muestran afiliados activos, bloqueados e inactivos.
            Los afiliados con deudas anteriores pueden consultar
            sus obligaciones, aunque ya no tengan un vehículo vigente.
        </p>

    </div>

    {{-- LISTADO --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

        <div class="border-b border-zinc-200 p-4 dark:border-zinc-700">

            <h2 class="font-semibold">
                Afiliados disponibles
            </h2>

        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-left text-sm">

                <thead class="bg-zinc-50 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">

                    <tr>
                        <th class="px-4 py-3">Afiliado</th>
                        <th class="px-4 py-3">CI</th>
                        <th class="px-4 py-3">Grupo</th>
                        <th class="px-4 py-3">Placa</th>
                        <th class="px-4 py-3">Acción</th>
                    </tr>

                </thead>

                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">

                    @forelse($afiliados as $afiliado)

                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">

                            <td class="px-4 py-3 font-medium">
                                {{ $afiliado->nombre_completo }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $afiliado->ci }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $afiliado->grupo?->nombre ?? 'Sin grupo' }}
                            </td>

                            <td class="px-4 py-3 font-semibold text-emerald-700 dark:text-emerald-400">
                                {{ $afiliado->asignacionVigente?->vehiculo?->placa ?? 'Sin placa' }}
                            </td>

                            <td class="px-4 py-3">

                                <button
                                    type="button"
                                    wire:click="seleccionar({{ $afiliado->id }})"
                                    class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700"
                                >
                                    Seleccionar
                                </button>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-zinc-500">
                                No se encontraron afiliados disponibles.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="p-4">
            {{ $afiliados->links() }}
        </div>

    </div>

    {{-- AFILIADO SELECCIONADO --}}
    @if($seleccionado)

        @php
            $vehiculo = $seleccionado->asignacionVigente?->vehiculo;
        @endphp

        <div class="rounded-xl border border-emerald-200 bg-white p-6 shadow-sm dark:border-emerald-900 dark:bg-zinc-900">

            <div class="mb-5 flex items-start justify-between gap-4">

                <div>
                    <h2 class="text-xl font-bold text-emerald-700 dark:text-emerald-400">
                        Emisión de Hoja de Ruta
                    </h2>

                    <p class="text-sm text-zinc-500">
                        Afiliado seleccionado
                    </p>
                </div>

                <button
                    type="button"
                    wire:click="cancelarSeleccion"
                    class="rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700"
                >
                    Cerrar
                </button>

            </div>

            <div class="grid gap-4 md:grid-cols-2">

                <div>
                    <p class="text-xs text-zinc-500">Afiliado</p>
                    <p class="font-semibold">
                        {{ $seleccionado->nombre_completo }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-zinc-500">Carnet de identidad</p>
                    <p class="font-semibold">
                        {{ $seleccionado->ci }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-zinc-500">Grupo</p>
                    <p class="font-semibold">
                        {{ $seleccionado->grupo?->nombre ?? 'Sin grupo' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-zinc-500">Placa vigente</p>
                    <p class="font-semibold text-emerald-700 dark:text-emerald-400">
                        {{ $vehiculo?->placa ?? 'Sin placa' }}
                    </p>
                </div>

            </div>

            @if($vehiculo?->foto_path)

                <div class="mt-5">

                    <p class="mb-2 text-xs text-zinc-500">
                        Fotografía del vehículo
                    </p>

                    <img
                        src="{{ route('vehiculos.foto', ['vehiculo' => $vehiculo->id]) }}"
                        alt="Vehículo {{ $vehiculo->placa }}"
                        class="h-40 w-64 rounded-lg border border-zinc-200 object-cover dark:border-zinc-700"
                    >

                </div>

            @endif

            {{-- PRÓXIMA ETAPA --}}
            {{-- COTIZACIÓN DE HOJAS DE RUTA --}}
            <div class="mt-6 rounded-xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-800">

                <h3 class="mb-2 text-lg font-semibold">
                    Cotización de Hojas de Ruta
                </h3>

                <p class="mb-5 text-sm text-zinc-500">
                    Consulte las mensualidades pendientes y el importe total.
                    Esta operación no registra pagos.
                </p>

                <p class="mb-4 text-sm">
                    Estado del afiliado:
                    <span class="font-semibold">
                        {{ $seleccionado->estado }}
                    </span>
                </p>

                @if(!config('hojas_ruta.inicio_control'))
                    <div class="mb-4 rounded-lg bg-amber-100 p-3 text-sm text-amber-800">
                        Falta configurar la fecha oficial de inicio de control.
                        Todavía no se pueden calcular las obligaciones.
                    </div>
                @endif

                <form wire:submit="previsualizar" class="space-y-4">

                    <div>
                        <label
                            for="periodoObjetivo"
                            class="mb-2 block text-sm font-medium"
                        >
                            Mes de hoja de ruta
                        </label>

                        <input
                            id="periodoObjetivo"
                            type="month"
                            wire:model.change.live="periodoObjetivo"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white"
                        >

                        @error('periodoObjetivo')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="rounded-lg bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-700"
                    >
                        Calcular mensualidades pendientes
                    </button>

                </form>
                @if($mensajeCobro)

                    <div
                        class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800"
                    >
                        {{ $mensajeCobro }}
                    </div>

                @endif
                @if($errorCotizacion)

                    <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                        {{ $errorCotizacion }}
                    </div>

                @endif
                @if($reciboGeneradoId !== null)

                    <a
                        href="{{ route('recibos-hoja-ruta.imprimir', [
                            'recibo' => $reciboGeneradoId
                        ]) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mt-4 inline-flex rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700"
                    >
                        Imprimir recibo
                    </a>

                @endif

                @if($cotizacion !== null)

                    <div class="mt-6 space-y-4">

                        <div class="rounded-lg bg-white p-4 dark:bg-zinc-900">

                            <div class="flex flex-wrap justify-between gap-3">

                                <div>
                                    <p class="text-xs text-zinc-500">
                                        Afiliado
                                    </p>

                                    <p class="font-semibold">
                                        {{ $cotizacion['afiliado'] }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-zinc-500">
                                        Meses pendientes
                                    </p>

                                    <p class="text-lg font-bold">
                                        {{ $cotizacion['cantidad_meses'] }}
                                    </p>
                                </div>

                            </div>

                        </div>

                        <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">

                            <table class="w-full text-sm">

                                <thead class="bg-zinc-100 dark:bg-zinc-900">

                                    <tr>
                                        <th class="px-4 py-3 text-left">
                                            Período
                                        </th>

                                        <th class="px-4 py-3 text-left">
                                            Placa
                                        </th>

                                        <th class="px-4 py-3 text-right">
                                            Importe (Bs)
                                        </th>
                                    </tr>

                                </thead>

                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">

                                    @forelse($cotizacion['detalles'] as $detalle)

                                        <tr>
                                            <td class="px-4 py-3">
                                                {{ $detalle['periodo'] }}
                                            </td>

                                            <td class="px-4 py-3">
                                                {{ $detalle['placa'] }}
                                            </td>

                                            <td class="px-4 py-3 text-right font-semibold">
                                                {{ $detalle['importe'] }}
                                            </td>
                                        </tr>

                                    @empty

                                        <tr>
                                            <td
                                                colspan="3"
                                                class="px-4 py-5 text-center text-zinc-500"
                                            >
                                                No existen mensualidades pendientes
                                                para el período consultado.
                                            </td>
                                        </tr>

                                    @endforelse

                                </tbody>

                            </table>

                        </div>

                        <div class="rounded-lg bg-white p-5 dark:bg-zinc-900">

                            <div class="flex justify-between py-2">
                                <span>Subtotal mensualidades</span>

                                <span class="font-semibold">
                                    Bs {{ $cotizacion['subtotal'] }}
                                </span>
                            </div>

                            <div class="flex justify-between py-2">
                                <span>Multa única</span>

                                <span class="font-semibold">
                                    Bs {{ $cotizacion['multa_unica'] }}
                                </span>
                            </div>

                            <div class="mt-3 flex justify-between border-t border-zinc-200 pt-4 text-lg font-bold dark:border-zinc-700">
                                <span>TOTAL</span>

                                <span class="text-emerald-700 dark:text-emerald-400">
                                    Bs {{ $cotizacion['total'] }}
                                </span>
                            </div>

                        </div>

                        @if($cotizacion['tiene_mora'])

                            <p class="rounded-lg bg-amber-100 p-3 text-sm text-amber-800">
                                Esta cotización incluye una multa única por mora.
                            </p>

                        @endif

                        @if($seleccionado->estado !== 'ACTIVO')

                            <p class="rounded-lg bg-red-50 p-3 text-sm text-red-700">
                                El afiliado puede regularizar deudas anteriores,
                                pero no está habilitado para emitir una hoja
                                de ruta mientras permanezca en este estado.
                            </p>

                        @endif

                        @can('cobrar-hojas-ruta')

                            @if(
                                config('hojas_ruta.cobros_habilitados', false)
                                && $cotizacion['cantidad_meses'] > 0
                            )

                                <button
                                    type="button"
                                    wire:click="procesarCobro"
                                    wire:confirm="¿Confirma que recibió el dinero y desea registrar este cobro?"
                                    wire:loading.attr="disabled"
                                    wire:target="procesarCobro"
                                    class="w-full rounded-lg bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                                >
                                    Confirmar y registrar cobro
                                </button>

                                <p
                                    wire:loading
                                    wire:target="procesarCobro"
                                    class="mt-2 text-center text-sm text-zinc-500"
                                >
                                    Procesando cobro, espere...
                                </p>

                            @else

                                <button
                                    type="button"
                                    disabled
                                    class="w-full cursor-not-allowed rounded-lg bg-zinc-300 px-5 py-3 text-sm font-semibold text-zinc-600"
                                >
                                    Procesar cobro — todavía no habilitado
                                </button>

                            @endif

                        @endcan

                    </div>

                @endif

            </div>

        </div>

    @endif

</div>