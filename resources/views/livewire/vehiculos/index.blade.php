<div class="mx-auto w-full max-w-7xl space-y-6 pb-10">

    {{-- ENCABEZADO --}}
    <div class="rounded-2xl bg-gradient-to-r from-emerald-950 to-emerald-800 p-6 text-white shadow-lg">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

            <div>
                <p class="text-sm text-emerald-200">
                    Cooperativa de Transportes 12 de Mayo R.L.
                </p>

                <h1 class="mt-1 text-2xl font-bold">
                    Vehículos y cambio de herramienta
                </h1>

                <p class="mt-2 text-sm text-emerald-100">
                    Administración de movilidades e historial de asignaciones.
                </p>
            </div>

            @can('gestionar-padron')
                <button
                    type="button"
                    wire:click="nuevo"
                    class="rounded-xl bg-red-700 px-5 py-3 font-semibold text-white hover:bg-red-800"
                >
                    + Asignar vehículo
                </button>
            @endcan

        </div>
    </div>

    @if (session()->has('mensaje'))
        <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-emerald-800">
            {{ session('mensaje') }}
        </div>
    @endif

    {{-- FORMULARIO --}}
    @can('gestionar-padron')
        @if ($mostrarFormulario)
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

            <h2 class="mb-2 text-xl font-bold text-zinc-900 dark:text-white">
                Asignación o cambio de herramienta
            </h2>

            <p class="mb-6 text-sm text-zinc-500">
                Si el afiliado ya tiene un vehículo vigente, el sistema registrará
                automáticamente el cambio y conservará su historial.
            </p>

            <form wire:submit="guardar" class="space-y-5">

                <div class="grid gap-5 md:grid-cols-2">

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold dark:text-white">
                            Afiliado *
                        </label>

                        <select wire:model="afiliadoId"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">

                            <option value="">Seleccione un afiliado</option>

                            @foreach ($listaAfiliados as $afiliado)
                                <option value="{{ $afiliado->id }}">
                                    {{ $afiliado->numero_socio ?? 'S/N' }}
                                    - {{ $afiliado->nombre_completo }}
                                </option>
                            @endforeach
                        </select>

                        @error('afiliadoId')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold dark:text-white">
                            Placa de movilidad *
                        </label>

                        <input type="text" wire:model="placa"
                            placeholder="Ejemplo: 1234-ABC"
                            maxlength="20"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">

                        @error('placa')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold dark:text-white">
                            Marca
                        </label>

                        <input type="text" wire:model="marca"
                            placeholder="Ejemplo: Toyota"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold dark:text-white">
                            Modelo
                        </label>

                        <input type="text" wire:model="modelo"
                            placeholder="Modelo del vehículo"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold dark:text-white">
                            Tipo
                        </label>

                        <input type="text" wire:model="tipo"
                            placeholder="Ejemplo: Minibús"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold dark:text-white">
                            Año
                        </label>

                        <input type="number" wire:model="anio"
                            placeholder="Ejemplo: 2020"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">

                        @error('anio')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold dark:text-white">
                            Color
                        </label>

                        <input type="text" wire:model="color"
                            placeholder="Color del vehículo"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold dark:text-white">
                            Observaciones del cambio
                        </label>

                        <textarea wire:model="observaciones" rows="3"
                            placeholder="Motivo u observaciones administrativas..."
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold dark:text-white">
                            Fotografía del vehículo
                        </label>

                        <input
                            type="file"
                            wire:model="foto"
                            accept="image/jpeg,image/png,image/webp"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                        >

                        <p class="mt-2 text-xs text-zinc-500">
                            Formatos JPG, PNG o WebP. Tamaño máximo: 2 MB.
                        </p>

                        <div wire:loading wire:target="foto"
                            class="mt-2 text-sm text-emerald-700">
                            Cargando fotografía...
                        </div>

                        @error('foto')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                        @if ($foto)
                            <div class="mt-4">
                                <p class="mb-2 text-sm font-medium dark:text-white">
                                    Vista previa
                                </p>

                                <img
                                    src="{{ $foto->temporaryUrl() }}"
                                    alt="Fotografía del vehículo"
                                    class="h-44 w-64 rounded-xl border border-zinc-200 object-cover shadow-sm"
                                >
                            </div>
                        @endif
                    </div>

                </div>

                <div class="flex gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700">

                    <button type="submit"
                        wire:loading.attr="disabled"
                        wire:target="guardar"
                        class="rounded-xl bg-emerald-800 px-6 py-3 font-semibold text-white hover:bg-emerald-900 disabled:opacity-60">
                        Guardar asignación
                    </button>

                    <button type="button" wire:click="cancelar"
                        class="rounded-xl border border-zinc-300 px-6 py-3 font-semibold dark:border-zinc-600 dark:text-white">
                        Cancelar
                    </button>

                </div>

            </form>
        </div>
        @endif
    @endcan

    {{-- LISTADO --}}
    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

        <div class="border-b border-zinc-200 p-5 dark:border-zinc-700">
            <h2 class="mb-4 text-lg font-bold text-zinc-900 dark:text-white">
                Vehículos vigentes por afiliado
            </h2>

            <input type="search"
                wire:model.live.debounce.300ms="buscar"
                placeholder="Buscar por nombre, CI o placa..."
                class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-zinc-900 md:max-w-md dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">

                <thead class="bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                    <tr>
                        <th class="px-5 py-4">Fotografía</th>
                        <th class="px-5 py-4">Afiliado</th>
                        <th class="px-5 py-4">Grupo</th>
                        <th class="px-5 py-4">Placa vigente</th>
                        <th class="px-5 py-4">Desde</th>
                        <th class="px-5 py-4 text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">

                    @forelse ($afiliados as $afiliado)
                        <tr wire:key="vehiculo-afiliado-{{ $afiliado->id }}"
                            class="text-zinc-700 dark:text-zinc-200">

                            <td class="px-5 py-4">
                                @if ($afiliado->asignacionVigente?->vehiculo?->foto_path)
                                    <a
                                        href="{{ route('vehiculos.foto', [
                                            'vehiculo' => $afiliado->asignacionVigente->vehiculo->id
                                        ]) }}"
                                        target="_blank"
                                    >
                                        <img
                                            src="{{ route('vehiculos.foto', [
                                                'vehiculo' => $afiliado->asignacionVigente->vehiculo->id
                                            ]) }}"
                                            alt="Fotografía de la movilidad"
                                            loading="lazy"
                                            class="h-16 w-24 rounded-lg border border-zinc-200 object-cover"
                                        >
                                    </a>

                                @else

                                    <div class="flex h-16 w-24 items-center justify-center rounded-lg bg-zinc-100 text-xs text-zinc-500 dark:bg-zinc-800">
                                        Sin imagen
                                    </div>

                                @endif

                            </td>
                            <td class="px-5 py-4 font-semibold">
                                {{ $afiliado->nombre_completo }}
                            </td>

                            <td class="px-5 py-4">
                                {{ $afiliado->grupo?->codigo ?? '—' }}
                            </td>

                            <td class="px-5 py-4 font-bold text-emerald-700 dark:text-emerald-400">
                                {{ $afiliado->asignacionVigente?->vehiculo?->placa ?? 'Sin vehículo' }}
                            </td>

                            <td class="px-5 py-4">
                                {{ $afiliado->asignacionVigente?->fecha_inicio?->format('d/m/Y') ?? '—' }}
                            </td>

                            <td class="px-5 py-4 text-right">
                                <button
                                    type="button"
                                    wire:click="verHistorial({{ $afiliado->id }})"
                                    class="font-semibold text-emerald-700 hover:underline dark:text-emerald-400"
                                >
                                    Ver historial
                                </button>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-zinc-500">
                                No se encontraron afiliados.
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        <div class="border-t border-zinc-200 p-5 dark:border-zinc-700">
            {{ $afiliados->links() }}
        </div>

    </div>

    {{-- HISTORIAL --}}
    @if ($historialAfiliadoId !== null)

        <div class="rounded-2xl border border-emerald-300 bg-white p-6 shadow-sm dark:border-emerald-800 dark:bg-zinc-900">

            <div class="mb-5 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-zinc-900 dark:text-white">
                        Historial de vehículos
                    </h2>

                    <p class="text-sm text-zinc-500">
                        {{ $afiliadoHistorial?->nombre_completo }}
                    </p>
                </div>

                <button type="button"
                    wire:click="cerrarHistorial"
                    class="rounded-lg bg-zinc-200 px-4 py-2 text-sm font-semibold text-zinc-700 dark:bg-zinc-700 dark:text-white">
                    Cerrar
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-100 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-3">Placa</th>
                            <th class="px-4 py-3">Fecha de inicio</th>
                            <th class="px-4 py-3">Fecha de finalización</th>
                            <th class="px-4 py-3">Motivo</th>
                            <th class="px-4 py-3">Fotografía</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($historial as $registro)
                            <tr wire:key="historial-{{ $registro->id }}">
                                <td class="px-4 py-3 font-semibold">
                                    {{ $registro->vehiculo?->placa }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $registro->fecha_inicio?->format('d/m/Y H:i') }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $registro->fecha_fin?->format('d/m/Y H:i') ?? 'Vigente' }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ str_replace('_', ' ', $registro->motivo) }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($registro->vehiculo?->foto_path)
                                        <img
                                            src="{{ route('vehiculos.foto', [
                                                'vehiculo' => $registro->vehiculo->id
                                            ]) }}"
                                            alt="Vehículo anterior"
                                            loading="lazy"
                                            class="h-14 w-20 rounded-lg object-cover"
                                        >
                                    @else
                                        <span class="text-zinc-400">Sin imagen</span>
                                    @endif

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-zinc-500">
                                    Este afiliado todavía no tiene historial de vehículos.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

    @endif

</div>