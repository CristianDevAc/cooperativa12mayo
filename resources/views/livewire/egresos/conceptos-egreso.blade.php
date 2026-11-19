<div class="mx-auto w-full max-w-7xl space-y-6 pb-10">

    {{-- ENCABEZADO --}}
    <div class="rounded-2xl bg-gradient-to-r from-emerald-950 to-emerald-800 p-6 text-white shadow-lg">
        <h1 class="text-2xl font-bold">
            Configuración de Conceptos de Egreso
        </h1>

        <p class="mt-2 text-sm text-emerald-100">
            Cooperativa de Transportes 12 de Mayo R.L.
        </p>

        <p class="mt-2 text-sm text-emerald-100">
            Administración de categorías de gastos y su clasificación
            en los informes económicos.
        </p>
    </div>

    {{-- MENSAJES --}}
    @if($mensaje)
        <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">
            {{ $mensaje }}
        </div>
    @endif

    @if($errorOperacion)
        <div class="rounded-xl border border-red-300 bg-red-50 p-4 text-sm font-semibold text-red-800">
            {{ $errorOperacion }}
        </div>
    @endif

    {{-- TABLA DE CONCEPTOS --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

        <div class="border-b border-zinc-200 p-5 dark:border-zinc-700">
            <h2 class="text-lg font-bold">
                Conceptos de egreso
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">

                <thead class="bg-zinc-100 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3">Código</th>
                        <th class="px-4 py-3">Concepto</th>
                        <th class="px-4 py-3">Grupo económico</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">

                    @forelse($conceptos as $concepto)

                        <tr wire:key="concepto-egreso-{{ $concepto->id }}">

                            <td class="px-4 py-4 text-xs text-zinc-500">
                                {{ $concepto->codigo }}
                            </td>

                            <td class="px-4 py-4">
                                <p class="font-bold">
                                    {{ $concepto->nombre }}
                                </p>

                                <p class="mt-1 max-w-sm text-xs text-zinc-500">
                                    {{ $concepto->descripcion ?: 'Sin descripción' }}
                                </p>
                            </td>

                            <td class="px-4 py-4">
                                @if($concepto->grupo_reporte === 'ORDINARIO')
                                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">
                                        Ordinario
                                    </span>
                                @else
                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                                        Otros egresos
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-4">
                                @if($concepto->activo)
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                                        Activo
                                    </span>
                                @else
                                    <span class="rounded-full bg-zinc-200 px-3 py-1 text-xs font-semibold text-zinc-700">
                                        Deshabilitado
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-2">

                                    <button
                                        type="button"
                                        wire:click="editar({{ $concepto->id }})"
                                        @disabled($concepto->activo)
                                        class="rounded-lg border border-zinc-300 px-3 py-2 text-xs font-semibold hover:bg-zinc-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-zinc-600 dark:hover:bg-zinc-800"
                                    >
                                        Configurar
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="cambiarActivacion({{ $concepto->id }})"
                                        wire:confirm="¿Confirma el cambio de estado de {{ $concepto->nombre }}?"
                                        wire:loading.attr="disabled"
                                        wire:target="cambiarActivacion"
                                        class="rounded-lg px-3 py-2 text-xs font-semibold text-white disabled:opacity-50
                                            {{ $concepto->activo
                                                ? 'bg-red-700 hover:bg-red-800'
                                                : 'bg-emerald-700 hover:bg-emerald-800' }}"
                                    >
                                        {{ $concepto->activo ? 'Deshabilitar' : 'Habilitar' }}
                                    </button>

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-zinc-500">
                                No existen conceptos de egreso registrados.
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>
        </div>

    </div>

    {{-- FORMULARIO DE EDICIÓN --}}
    @if($mostrarFormulario)

        <div class="rounded-xl border border-emerald-300 bg-white p-6 shadow-sm dark:border-emerald-800 dark:bg-zinc-900">

            <h2 class="mb-5 text-lg font-bold">
                Configurar concepto de egreso
            </h2>

            <form wire:submit="guardar" class="space-y-5">

                <div class="grid gap-5 md:grid-cols-2">

                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            Nombre del concepto
                        </label>

                        <input
                            type="text"
                            wire:model="nombre"
                            maxlength="150"
                            required
                            class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                        >

                        @error('nombre')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            Clasificación económica
                        </label>

                        <select
                            wire:model="grupoReporte"
                            required
                            class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                        >
                            <option value="ORDINARIO">
                                Egresos ordinarios
                            </option>

                            <option value="OTROS">
                                Otros egresos
                            </option>
                        </select>

                        @error('grupoReporte')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold">
                        Descripción
                    </label>

                    <textarea
                        wire:model="descripcion"
                        rows="3"
                        maxlength="1000"
                        placeholder="Describa los gastos que corresponden a esta categoría."
                        class="w-full rounded-lg border border-zinc-300 bg-white p-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    ></textarea>

                    @error('descripcion')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="flex flex-wrap justify-end gap-3">

                    <button
                        type="button"
                        wire:click="cancelar"
                        class="rounded-lg border border-zinc-300 px-5 py-3 font-semibold dark:border-zinc-600"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="guardar"
                        class="rounded-lg bg-emerald-700 px-6 py-3 font-semibold text-white hover:bg-emerald-800 disabled:opacity-50"
                    >
                        Guardar configuración
                    </button>

                </div>

            </form>

        </div>

    @endif

    <p class="text-xs text-zinc-500">
        La activación de un concepto no habilita por sí sola
        el registro definitivo de pagos.
    </p>

</div>