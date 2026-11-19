<div class="mx-auto w-full max-w-7xl space-y-6 pb-10">

    <div class="rounded-2xl bg-gradient-to-r from-emerald-950 to-emerald-800 p-6 text-white shadow-lg">
        <h1 class="text-2xl font-bold">
            Solicitudes de Reincorporación
        </h1>

        <p class="mt-2 text-sm text-emerald-100">
            Cooperativa de Transportes 12 de Mayo R.L.
        </p>

        <p class="mt-2 text-sm text-emerald-100">
            Registro administrativo previo al cobro.
        </p>
    </div>

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

    <form
        wire:submit="registrar"
        class="space-y-5 rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"
    >
        <h2 class="text-lg font-bold">
            Nueva solicitud
        </h2>

        <div>
            <label class="mb-2 block text-sm font-semibold">
                Buscar afiliado inactivo
            </label>

            <input
                type="text"
                wire:model.live.debounce.300ms="buscarAfiliado"
                placeholder="Buscar por nombre, CI o número de socio"
                class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
            >

            <p class="mt-2 text-xs text-zinc-500">
                Solo se muestran afiliados con estado INACTIVO.
            </p>

            @if($afiliados->isNotEmpty())
                <div class="mt-3 divide-y divide-zinc-200 rounded-lg border border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">

                    @foreach($afiliados as $afiliado)
                        <div
                            wire:key="reinc-afiliado-{{ $afiliado->id }}"
                            class="flex flex-wrap items-center justify-between gap-3 p-3"
                        >
                            <div class="text-sm">
                                <p class="font-semibold">
                                    {{ $afiliado->nombre_completo }}
                                </p>

                                <p class="text-xs text-zinc-500">
                                    CI: {{ $afiliado->ci ?? '—' }}
                                    · Socio: {{ $afiliado->numero_socio ?? '—' }}
                                </p>
                            </div>

                            <button
                                type="button"
                                wire:click="seleccionarAfiliado({{ $afiliado->id }})"
                                class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800"
                            >
                                Seleccionar
                            </button>
                        </div>
                    @endforeach

                </div>
            @endif
        </div>

        @if($afiliadoSeleccionado)
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-emerald-900">
                <div>
                    <p class="font-bold">
                        {{ $afiliadoSeleccionado->nombre_completo }}
                    </p>

                    <p class="text-sm">
                        Socio: {{ $afiliadoSeleccionado->numero_socio ?? '—' }}
                        · Estado: {{ $afiliadoSeleccionado->estado }}
                    </p>
                </div>

                <button
                    type="button"
                    wire:click="quitarAfiliado"
                    class="rounded-lg border border-emerald-700 px-3 py-2 text-sm font-semibold"
                >
                    Quitar
                </button>
            </div>
        @endif

        @error('afiliadoId')
            <p class="text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror

        <div>
            <label class="mb-2 block text-sm font-semibold">
                Observaciones (opcional)
            </label>

            <textarea
                wire:model="observaciones"
                rows="3"
                maxlength="500"
                placeholder="Referencia o antecedentes de la solicitud..."
                class="w-full rounded-lg border border-zinc-300 bg-white p-3 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
            ></textarea>

            @error('observaciones')
                <p class="mt-1 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="flex justify-end">
            <button
                type="submit"
                wire:confirm="¿Confirma el registro de esta solicitud de reincorporación?"
                wire:loading.attr="disabled"
                wire:target="registrar"
                @disabled(!$afiliadoSeleccionado)
                class="rounded-lg bg-emerald-700 px-6 py-3 font-semibold text-white hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-50"
            >
                Registrar solicitud
            </button>
        </div>
    </form>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

        <div class="border-b border-zinc-200 p-5 dark:border-zinc-700">
            <h2 class="text-lg font-bold">
                Últimas 25 solicitudes
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">

                <thead class="bg-zinc-100 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3">N.º</th>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Afiliado</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Recibo</th>
                        <th class="px-4 py-3">Registrado por</th>
                        <th class="px-4 py-3">Acción</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($solicitudes as $solicitud)
                        <tr wire:key="sol-reinc-{{ $solicitud->id }}">
                            <td class="px-4 py-4 font-bold">
                                {{ str_pad((string) $solicitud->id, 6, '0', STR_PAD_LEFT) }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4">
                                {{ $solicitud->fecha_solicitud->timezone('America/La_Paz')->format('d/m/Y H:i') }}
                            </td>

                            <td class="px-4 py-4">
                                {{ $solicitud->afiliado?->nombre_completo ?? '—' }}
                            </td>

                            <td class="px-4 py-4">
                                <span class="font-semibold
                                    {{ $solicitud->estado === 'PENDIENTE'
                                        ? 'text-amber-700 dark:text-amber-400'
                                        : 'text-emerald-700 dark:text-emerald-400' }}">
                                    {{ $solicitud->estado }}
                                </span>
                            </td>

                            <td class="px-4 py-4">
                                @if($solicitud->recibo)
                                    {{ $solicitud->recibo->concepto_codigo }}-{{ str_pad((string) $solicitud->recibo->numero_recibo, 6, '0', STR_PAD_LEFT) }}
                                @else
                                    <span class="text-zinc-500">
                                        Sin cobro
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-4">
                                {{ $solicitud->registradoPor?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-4">

                                @if($solicitud->estado === 'COBRADA')

                                    <button
                                        type="button"
                                        wire:click="finalizar({{ $solicitud->id }})"
                                        wire:confirm="¿Confirma que el afiliado fue reactivado administrativamente y desea finalizar esta solicitud?"
                                        wire:loading.attr="disabled"
                                        class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800 disabled:opacity-50"
                                    >
                                        Finalizar
                                    </button>

                                @elseif($solicitud->estado === 'FINALIZADA')

                                    <span class="font-semibold text-emerald-700">
                                        Finalizada
                                    </span>

                                @elseif($solicitud->estado === 'PENDIENTE')

                                    <span class="text-sm text-amber-700">
                                        Esperando cobro
                                    </span>

                                @else

                                    <span class="text-sm text-zinc-500">
                                        {{ $solicitud->estado }}
                                    </span>

                                @endif

                            </td>
                        </tr>
                        
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-zinc-500">
                                Todavía no hay solicitudes de reincorporación.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>
</div>