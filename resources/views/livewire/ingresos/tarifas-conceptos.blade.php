<div class="mx-auto w-full max-w-7xl space-y-6 pb-10">

    {{-- ENCABEZADO --}}
    <div class="rounded-2xl bg-gradient-to-r from-emerald-950 to-emerald-800 p-6 text-white shadow-lg">

        <h1 class="text-2xl font-bold">
            Tarifas de Otros Ingresos
        </h1>

        <p class="mt-2 text-sm text-emerald-100">
            Configuración e historial de precios de la Cooperativa 12 de Mayo R.L.
        </p>

        <p class="mt-2 text-xs text-emerald-200">
            Registrar tarifas no habilita automáticamente los cobros.
        </p>

    </div>

    @if($mensaje)
        <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">
            {{ $mensaje }}
        </div>
    @endif

    {{-- FORMULARIO --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

        <h2 class="mb-5 text-lg font-bold">
            Registrar nueva tarifa
        </h2>

        <form wire:submit="guardar" class="space-y-5">

            <div class="grid gap-5 md:grid-cols-3">

                <div>
                    <label class="mb-2 block text-sm font-semibold">
                        Concepto de ingreso
                    </label>

                    <select
                        wire:model="conceptoId"
                        required
                        class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    >
                        <option value="">Seleccione un concepto</option>

                        @foreach($conceptos as $concepto)
                            <option value="{{ $concepto->id }}">
                                {{ $concepto->nombre }}
                            </option>
                        @endforeach

                    </select>

                    @error('conceptoId')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold">
                        Vigente desde
                    </label>

                    <input
                        type="month"
                        wire:model="vigencia"
                        required
                        class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    >

                    @error('vigencia')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold">
                        Importe (Bs)
                    </label>

                    <input
                        type="number"
                        min="0.01"
                        step="0.01"
                        wire:model="importe"
                        required
                        placeholder="0.00"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    >

                    @error('importe')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold">
                    Observaciones
                </label>

                <textarea
                    wire:model="observaciones"
                    rows="3"
                    maxlength="1000"
                    placeholder="Motivo de la tarifa o referencia de aprobación..."
                    class="w-full rounded-lg border border-zinc-300 bg-white p-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                ></textarea>
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="guardar"
                @disabled($conceptos->isEmpty())
                class="rounded-lg bg-emerald-700 px-6 py-3 font-semibold text-white hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-50"
            >
                Guardar tarifa
            </button>

            @if($conceptos->isEmpty())
                <p class="text-sm text-amber-700 dark:text-amber-300">
                    Primero debe configurar al menos un concepto
                    con importe FIJO en Conceptos de Ingresos.
                </p>
            @endif

        </form>

    </div>

    {{-- HISTORIAL --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

        <div class="border-b border-zinc-200 p-5 dark:border-zinc-700">
            <h2 class="text-lg font-bold">
                Historial de tarifas
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">

                <thead class="bg-zinc-100 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3">Concepto</th>
                        <th class="px-4 py-3">Vigencia</th>
                        <th class="px-4 py-3 text-right">Importe (Bs)</th>
                        <th class="px-4 py-3">Registrado por</th>
                        <th class="px-4 py-3">Observaciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">

                    @forelse($tarifas as $tarifa)
                        <tr wire:key="tarifa-concepto-{{ $tarifa->id }}">

                            <td class="px-4 py-4 font-semibold">
                                {{ $tarifa->concepto?->nombre ?? '—' }}
                            </td>

                            <td class="px-4 py-4">
                                {{ $tarifa->vigente_desde->format('m/Y') }}
                            </td>

                            <td class="px-4 py-4 text-right font-bold text-emerald-700 dark:text-emerald-400">
                                {{ number_format((float) $tarifa->importe, 2, ',', '.') }}
                            </td>

                            <td class="px-4 py-4">
                                {{ $tarifa->creadoPor?->name ?? '—' }}
                            </td>

                            <td class="px-4 py-4 text-zinc-600 dark:text-zinc-300">
                                {{ $tarifa->observaciones ?: '—' }}
                            </td>

                        </tr>
                    @empty

                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-zinc-500">
                                Todavía no existen tarifas registradas.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>
    </div>

</div>