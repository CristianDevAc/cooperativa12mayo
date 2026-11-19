<div class="mx-auto w-full max-w-7xl space-y-6 pb-10">

    {{-- ENCABEZADO --}}
    <div class="rounded-2xl bg-gradient-to-r from-emerald-950 to-emerald-800 p-6 text-white shadow-lg">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

            <div>
                <p class="mb-1 text-sm text-emerald-200">
                    Administración de la cooperativa
                </p>

                <h1 class="text-2xl font-bold">
                    Gestión de grupos
                </h1>

                <p class="mt-2 text-sm text-emerald-100">
                    Registro y administración de los grupos de transporte.
                </p>
            </div>

            @can('gestionar-padron')
                <button
                    type="button"
                    wire:click="nuevo"
                    class="rounded-xl bg-red-700 px-5 py-3 font-semibold text-white shadow hover:bg-red-800"
                >
                    + Nuevo grupo
                </button>
            @endcan

        </div>
    </div>

    {{-- MENSAJE --}}
    @if (session()->has('mensaje'))
        <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
            {{ session('mensaje') }}
        </div>
    @endif

    {{-- FORMULARIO --}}
    @can('gestionar-padron')
        @if ($mostrarFormulario)
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

            <h2 class="mb-5 text-xl font-bold text-zinc-900 dark:text-white">
                {{ $editandoId ? 'Editar grupo' : 'Registrar nuevo grupo' }}
            </h2>

            <form wire:submit="guardar" class="space-y-5">

                <div class="grid gap-5 md:grid-cols-2">

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                            Código del grupo
                        </label>

                        <input
                            type="text"
                            wire:model="codigo"
                            maxlength="20"
                            placeholder="Ejemplo: A"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-zinc-900 outline-none focus:border-emerald-600 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                        >

                        @error('codigo')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                            Nombre del grupo
                        </label>

                        <input
                            type="text"
                            wire:model="nombre"
                            maxlength="100"
                            placeholder="Ejemplo: Grupo A"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-zinc-900 outline-none focus:border-emerald-600 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                        >

                        @error('nombre')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                </div>

                <label class="flex items-center gap-3 text-sm font-medium text-zinc-700 dark:text-zinc-200">
                    <input
                        type="checkbox"
                        wire:model="activo"
                        class="size-5 rounded border-zinc-300 text-emerald-700"
                    >

                    Grupo activo
                </label>

                <div class="flex flex-wrap gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700">

                    <button
                        type="submit"
                        class="rounded-xl bg-emerald-800 px-6 py-3 font-semibold text-white hover:bg-emerald-900"
                    >
                        Guardar grupo
                    </button>

                    <button
                        type="button"
                        wire:click="cancelar"
                        class="rounded-xl border border-zinc-300 px-6 py-3 font-semibold text-zinc-700 hover:bg-zinc-100 dark:border-zinc-600 dark:text-zinc-200 dark:hover:bg-zinc-800"
                    >
                        Cancelar
                    </button>

                </div>

            </form>
        </div>
        @endif
    @endcan

    {{-- LISTADO --}}
    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

        <div class="flex flex-col gap-4 border-b border-zinc-200 p-5 md:flex-row md:items-center md:justify-between dark:border-zinc-700">

            <div>
                <h2 class="text-lg font-bold text-zinc-900 dark:text-white">
                    Grupos registrados
                </h2>

                <p class="text-sm text-zinc-500">
                    Total: {{ $grupos->count() }}
                </p>
            </div>

            <input
                type="search"
                wire:model.live.debounce.300ms="buscar"
                placeholder="Buscar por código o nombre..."
                class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none focus:border-emerald-600 md:w-80 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
            >

        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">

                <thead class="bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                    <tr>
                        <th class="px-5 py-4">Código</th>
                        <th class="px-5 py-4">Nombre</th>
                        <th class="px-5 py-4">Afiliados</th>
                        <th class="px-5 py-4">Estado</th>
                        <th class="px-5 py-4 text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">

                    @forelse ($grupos as $grupo)

                        <tr wire:key="grupo-{{ $grupo->id }}" class="text-zinc-700 hover:bg-zinc-50 dark:text-zinc-200 dark:hover:bg-zinc-800">

                            <td class="px-5 py-4 font-bold">
                                {{ $grupo->codigo }}
                            </td>

                            <td class="px-5 py-4">
                                {{ $grupo->nombre }}
                            </td>

                            <td class="px-5 py-4">
                                {{ $grupo->afiliados()->count() }}
                            </td>

                            <td class="px-5 py-4">
                                @if ($grupo->activo)
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                                        Activo
                                    </span>
                                @else
                                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">
                                        Inactivo
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-3">

                                    @can('gestionar-padron')

                                        <button
                                            type="button"
                                            wire:click="editar({{ $grupo->id }})"
                                            class="font-semibold text-emerald-700 hover:underline dark:text-emerald-400"
                                        >
                                            Editar
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="cambiarEstado({{ $grupo->id }})"
                                            class="font-semibold text-red-700 hover:underline dark:text-red-400"
                                        >
                                            {{ $grupo->activo ? 'Desactivar' : 'Activar' }}
                                        </button>

                                    @else

                                        <span class="text-xs text-zinc-400">
                                            Solo consulta
                                        </span>

                                    @endcan

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-zinc-500">
                                No se encontraron grupos registrados.
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>
        </div>

    </div>

</div>