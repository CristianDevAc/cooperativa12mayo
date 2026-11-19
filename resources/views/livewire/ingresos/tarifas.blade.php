<div class="space-y-6">

    {{-- ENCABEZADO --}}
    <div>
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
            Tarifas de Hojas de Ruta
        </h1>

        <p class="mt-1 text-sm text-zinc-500">
            Administración de precios y multas de la cooperativa.
        </p>
    </div>

    {{-- MENSAJE --}}
    @if($mensaje)
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
            {{ $mensaje }}
        </div>
    @endif

    {{-- FORMULARIO --}}
    @can('administrar-tarifas-hoja-ruta')
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

        <h2 class="mb-5 text-lg font-semibold">
            Registrar nueva tarifa
        </h2>

        <form wire:submit="guardar" class="space-y-5">

            <div class="grid gap-5 md:grid-cols-3">

                {{-- VIGENCIA --}}
                <div>
                    <label class="mb-2 block text-sm font-medium">
                        Vigente desde
                    </label>

                    <input
                        type="month"
                        wire:model="vigencia"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    >

                    @error('vigencia')
                        <p class="mt-1 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- IMPORTE BASE --}}
                <div>
                    <label class="mb-2 block text-sm font-medium">
                        Precio hoja de ruta (Bs)
                    </label>

                    <input
                        type="number"
                        min="0.01"
                        step="0.01"
                        wire:model="importeBase"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    >

                    @error('importeBase')
                        <p class="mt-1 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- MULTA --}}
                <div>
                    <label class="mb-2 block text-sm font-medium">
                        Multa única (Bs)
                    </label>

                    <input
                        type="number"
                        min="0"
                        step="0.01"
                        wire:model="importeMulta"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    >

                    @error('importeMulta')
                        <p class="mt-1 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">
                    Observaciones
                </label>

                <textarea
                    wire:model="observaciones"
                    rows="3"
                    placeholder="Ejemplo: Tarifa aprobada por el directorio..."
                    class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                ></textarea>

                @error('observaciones')
                    <p class="mt-1 text-xs text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="guardar"
                class="rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
            >
                Guardar tarifa
            </button>

        </form>

    </div>
    @else

        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-200">

            Usted tiene acceso de consulta a las tarifas,
            pero no cuenta con autorización para modificar
            precios ni multas.

        </div>

    @endcan
    {{-- HISTORIAL --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

        <div class="border-b border-zinc-200 p-5 dark:border-zinc-700">

            <h2 class="text-lg font-semibold">
                Historial de tarifas
            </h2>

            <p class="text-sm text-zinc-500">
                Se conservan las tarifas registradas por fecha de vigencia.
            </p>

        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-left text-sm">

                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-5 py-3">Vigente desde</th>
                        <th class="px-5 py-3">Precio</th>
                        <th class="px-5 py-3">Multa</th>
                        <th class="px-5 py-3">Observaciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">

                    @forelse($tarifas as $tarifa)

                        <tr>

                            <td class="px-5 py-4 font-medium">
                                {{ $tarifa->vigente_desde->format('m/Y') }}
                            </td>

                            <td class="px-5 py-4 font-semibold">
                                Bs {{ number_format((float) $tarifa->importe_base, 2, ',', '.') }}
                            </td>

                            <td class="px-5 py-4">
                                Bs {{ number_format((float) $tarifa->importe_multa, 2, ',', '.') }}
                            </td>

                            <td class="px-5 py-4 text-zinc-500">
                                {{ $tarifa->observaciones ?: 'Sin observaciones' }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-zinc-500">
                                Todavía no existen tarifas registradas.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>