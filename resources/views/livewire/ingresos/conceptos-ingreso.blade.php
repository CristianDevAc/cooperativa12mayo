<div class="mx-auto w-full max-w-7xl space-y-6 pb-10">

    {{-- ENCABEZADO --}}
    <div class="rounded-2xl bg-gradient-to-r from-emerald-950 to-emerald-800 p-6 text-white shadow-lg">

        <h1 class="text-2xl font-bold">
            Configuración de Conceptos de Ingreso
        </h1>

        <p class="mt-2 text-sm text-emerald-100">
            Cooperativa de Transportes 12 de Mayo R.L.
        </p>

        <p class="mt-2 text-xs text-emerald-200">
            Los conceptos permanecen deshabilitados para cobros.
        </p>

    </div>

    @if($mensaje)
        <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">
            {{ $mensaje }}
        </div>
    @endif
    @if($errorActivacion)
        <div class="rounded-xl border border-red-300 bg-red-50 p-4 text-sm font-semibold text-red-800">
            {{ $errorActivacion }}
        </div>
    @endif

    {{-- LISTADO --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

        <div class="border-b border-zinc-200 p-5 dark:border-zinc-700">
            <h2 class="text-lg font-bold">
                Conceptos registrados
            </h2>
        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-left text-sm">

                <thead class="bg-zinc-100 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3">Concepto</th>
                        <th class="px-4 py-3">Importe</th>
                        <th class="px-4 py-3">Periodicidad</th>
                        <th class="px-4 py-3">Naturaleza</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">

                    @foreach($conceptos as $concepto)

                        <tr wire:key="concepto-{{ $concepto->id }}">

                            <td class="px-4 py-4">
                                <div class="font-semibold">
                                    {{ $concepto->nombre }}
                                </div>
                                <div class="text-xs text-zinc-500">
                                    {{ $concepto->codigo }}
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                {{ $concepto->forma_importe ?? 'Por definir' }}
                            </td>

                            <td class="px-4 py-4">
                                {{ $concepto->periodicidad ?? 'Por definir' }}
                            </td>

                            <td class="px-4 py-4">
                                {{ $concepto->naturaleza ?? 'Por definir' }}
                            </td>

                            <td class="px-4 py-4">
                                @if($concepto->activo)
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                                        Activo
                                    </span>
                                @else
                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">
                                        Sin habilitar
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-4 text-right">
                                <button
                                    type="button"
                                    wire:click="editar({{ $concepto->id }})"
                                    class="rounded-lg bg-blue-700 px-4 py-2 font-semibold text-white hover:bg-blue-800"
                                >
                                    Configurar
                                </button>
                                @if(in_array(
                                    $concepto->codigo,
                                    [
                                        'CERTIFICACION',
                                        'CUOTA_INGRESO',
                                        'LOGOTIPO',
                                        'OTROS_INGRESOS',
                                        'REINCORPORACION',
                                        'CAMBIO_HERRAMIENTA',
                                    ],
                                    true
                                ))

                                    <button
                                        type="button"
                                        wire:click="cambiarActivacion({{ $concepto->id }})"
                                        wire:confirm="¿Confirma el cambio de estado de {{ $concepto->nombre }}?"
                                        wire:loading.attr="disabled"
                                        wire:target="cambiarActivacion"
                                        class="mt-2 rounded-lg px-4 py-2 text-sm font-semibold text-white disabled:opacity-50
                                            {{ $concepto->activo
                                                ? 'bg-red-700 hover:bg-red-800'
                                                : 'bg-emerald-700 hover:bg-emerald-800' }}"
                                    >
                                        {{ $concepto->activo ? 'Deshabilitar' : 'Habilitar' }}
                                    </button>

                                @endif
                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

    {{-- FORMULARIO --}}
    @if($mostrarFormulario)

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

            <h2 class="mb-5 text-xl font-bold">
                Definir reglas del concepto
            </h2>

            <form wire:submit="guardar" class="space-y-5">

                <div class="grid gap-5 md:grid-cols-2">

                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            Forma del importe
                        </label>

                        <select wire:model="formaImporte"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">

                            <option value="">Por definir</option>
                            <option value="FIJO">Tarifa fija configurable</option>
                            <option value="VARIABLE">Importe variable</option>

                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            Periodicidad
                        </label>

                        <select wire:model="periodicidad"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">

                            <option value="">Por definir</option>
                            <option value="UNICO">Único</option>
                            <option value="MENSUAL">Mensual</option>
                            <option value="ANUAL">Anual</option>
                            <option value="POR_EVENTO">Por operación o evento</option>

                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            Clasificación financiera
                        </label>

                        <select wire:model="naturaleza"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">

                            <option value="">Por definir</option>
                            <option value="OPERATIVO">Ingreso operativo</option>
                            <option value="EXTRAORDINARIO">Ingreso extraordinario</option>
                            <option value="PATRIMONIAL">Fondo patrimonial</option>

                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            ¿Requiere identificar un afiliado?
                        </label>

                        <select wire:model="requiereAfiliado"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">

                            <option value="">Por definir</option>
                            <option value="SI">Sí</option>
                            <option value="NO">No</option>

                        </select>
                    </div>

                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold">
                        Observaciones y reglas especiales
                    </label>

                    <textarea
                        wire:model="descripcion"
                        rows="4"
                        maxlength="1000"
                        placeholder="Anote aquí cómo funciona este concepto en la cooperativa..."
                        class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    ></textarea>
                </div>

                @if($errors->any())
                    <div class="rounded-lg bg-red-50 p-3 text-sm text-red-700">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="flex justify-end gap-3">

                    <button
                        type="button"
                        wire:click="cancelar"
                        class="rounded-lg border border-zinc-300 px-5 py-3 font-semibold"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="guardar"
                        class="rounded-lg bg-emerald-700 px-5 py-3 font-semibold text-white hover:bg-emerald-800 disabled:opacity-50"
                    >
                        Guardar configuración
                    </button>

                </div>

            </form>

        </div>

    @endif

</div>