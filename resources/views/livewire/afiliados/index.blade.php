<div class="mx-auto w-full max-w-7xl space-y-6 pb-10">

    {{-- ENCABEZADO --}}
    <div class="rounded-2xl bg-gradient-to-r from-emerald-950 to-emerald-800 p-6 text-white shadow-lg">

        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="mb-1 text-sm text-emerald-200">
                    Cooperativa de Transportes 12 de Mayo R.L.
                </p>

                <h1 class="text-2xl font-bold">
                    Gestión de afiliados
                </h1>

                <p class="mt-2 text-sm text-emerald-100">
                    Registro y administración de socios de la cooperativa.
                </p>
            </div>

            @can('gestionar-padron')
                <button
                    type="button"
                    wire:click="nuevo"
                    class="rounded-xl bg-red-700 px-5 py-3 font-semibold text-white hover:bg-red-800"
                >
                    + Nuevo afiliado
                </button>
            @endcan
        </div>
    </div>

    {{-- TARJETAS --}}
    <div class="grid gap-4 md:grid-cols-4">

        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500">Total afiliados</p>
            <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">
                {{ $totalAfiliados }}
            </p>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500">Afiliados activos</p>
            <p class="mt-2 text-3xl font-bold text-emerald-700">
                {{ $totalActivos }}
            </p>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500">Afiliados bloqueados</p>
            <p class="mt-2 text-3xl font-bold text-red-700">
                {{ $totalBloqueados }}
            </p>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500">
                Afiliados inactivos
            </p>

            <p class="mt-2 text-3xl font-bold text-amber-600">
                {{ $totalInactivos }}
            </p>
        </div>

    </div>

    @if (session()->has('mensaje'))
        <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
            {{ session('mensaje') }}
        </div>
    @endif

    {{-- FORMULARIO --}}
    @can('gestionar-padron')
        @if ($mostrarFormulario)

        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

            <h2 class="mb-6 text-xl font-bold text-zinc-900 dark:text-white">
                {{ $editandoId ? 'Editar afiliado' : 'Registrar nuevo afiliado' }}
            </h2>

            <form wire:submit="guardar" class="space-y-5">

                <div class="grid gap-5 md:grid-cols-2">

                    <div>
                        <label class="mb-2 block text-sm font-semibold dark:text-white">
                            Número de socio
                        </label>

                        <input type="text" wire:model="numeroSocio"
                            placeholder="Ejemplo: 001"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">

                        @error('numeroSocio')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold dark:text-white">
                            Grupo
                        </label>

                        <select wire:model="grupoId"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">

                            <option value="">Sin grupo asignado</option>

                            @foreach ($grupos as $grupo)
                                <option value="{{ $grupo->id }}">
                                    {{ $grupo->codigo }} - {{ $grupo->nombre }}
                                </option>
                            @endforeach

                        </select>

                        @error('grupoId')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold dark:text-white">
                            Nombre completo *
                        </label>

                        <input type="text" wire:model="nombreCompleto"
                            placeholder="Nombres y apellidos"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">

                        @error('nombreCompleto')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold dark:text-white">
                            Cédula de identidad
                        </label>

                        <input type="text" wire:model="ci"
                            placeholder="Número de CI"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">

                        @error('ci')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold dark:text-white">
                            Teléfono
                        </label>

                        <input type="text" wire:model="telefono"
                            placeholder="Número de contacto"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">

                        @error('telefono')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold dark:text-white">
                            Fecha de nacimiento
                        </label>

                        <input
                            type="date"
                            wire:model="fechaNacimiento"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                        >

                        @error('fechaNacimiento')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold dark:text-white">
                            Fecha de ingreso
                        </label>

                        <input
                            type="date"
                            wire:model="fechaIngreso"
                            readonly
                            class="w-full rounded-xl border border-zinc-300 bg-zinc-100 px-4 py-3 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                        >
                        <p class="mt-1 text-xs text-zinc-500">
                            Fecha asignada automáticamente por el sistema.
                        </p>
                        @error('fechaIngreso')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold dark:text-white">
                            Estado
                        </label>

                        <select wire:model="estado" @disabled($editandoId !== null && $estado === 'INACTIVO')
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">

                            <option value="ACTIVO">Activo</option>
                            <option value="BLOQUEADO">Bloqueado</option>
                            <option value="RETIRADO">Retirado</option>
                            <option value="INACTIVO" disabled>
                                Inactivo (utilice Reactivar)
                            </option>

                        </select>
                        <p class="mt-1 text-xs text-zinc-500">
                            La inactivación y reactivación se realizan desde
                            las acciones del afiliado para registrar su historial.
                        </p>

                        @error('estado')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold dark:text-white">
                            Dirección
                        </label>

                        <input type="text" wire:model="direccion"
                            placeholder="Dirección del afiliado"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold dark:text-white">
                            Observaciones
                        </label>

                        <textarea wire:model="observaciones" rows="3"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"></textarea>
                    </div>

                </div>

                <div class="flex flex-wrap gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700">

                    <button type="submit"
                        class="rounded-xl bg-emerald-800 px-6 py-3 font-semibold text-white hover:bg-emerald-900">
                        Guardar afiliado
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

        <div class="grid gap-4 border-b border-zinc-200 p-5 md:grid-cols-3 dark:border-zinc-700">

            <input type="search"
                wire:model.live.debounce.300ms="buscar"
                placeholder="Buscar nombre, CI, placa o número de socio..."
                class="rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">

            <select wire:model.live="grupoFiltro"
                class="rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">

                <option value="">Todos los grupos</option>

                @foreach ($grupos as $grupo)
                    <option value="{{ $grupo->id }}">
                        {{ $grupo->nombre }}
                    </option>
                @endforeach

            </select>

            <select wire:model.live="estadoFiltro"
                class="rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">

                <option value="">Todos los estados</option>
                <option value="ACTIVO">Activos</option>
                <option value="BLOQUEADO">Bloqueados</option>
                <option value="RETIRADO">Retirados</option>
                <option value="INACTIVO">Inactivos</option>

            </select>

        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">

                <thead class="bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                    <tr>
                        <th class="px-5 py-4">N.º socio</th>
                        <th class="px-5 py-4">Nombre completo</th>
                        <th class="px-5 py-4">CI</th>
                        <th class="px-5 py-4">Grupo</th>
                        <th class="px-5 py-4">Vehículo vigente</th>
                        <th class="px-5 py-4">Estado</th>
                        <th class="px-5 py-4 text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">

                    @forelse ($afiliados as $afiliado)

                        <tr wire:key="afiliado-{{ $afiliado->id }}"
                            class="text-zinc-700 hover:bg-zinc-50 dark:text-zinc-200 dark:hover:bg-zinc-800">

                            <td class="px-5 py-4">
                                {{ $afiliado->numero_socio ?? '—' }}
                            </td>

                            <td class="px-5 py-4 font-semibold">
                                {{ $afiliado->nombre_completo }}
                            </td>

                            <td class="px-5 py-4">
                                {{ $afiliado->ci ?? '—' }}
                            </td>

                            <td class="px-5 py-4">
                                {{ $afiliado->grupo?->nombre ?? 'Sin asignar' }}
                            </td>
                            
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if ($afiliado->asignacionVigente?->vehiculo?->foto_path)
                                        <img
                                            src="{{ route('vehiculos.foto', [
                                                'vehiculo' => $afiliado->asignacionVigente->vehiculo->id
                                            ]) }}"
                                            alt="Fotografía del vehículo"
                                            loading="lazy"
                                            class="h-12 w-16 rounded-lg border border-zinc-200 object-cover"
                                        >

                                    @else

                                        <div class="flex h-12 w-16 items-center justify-center rounded-lg bg-zinc-100 text-xs text-zinc-400 dark:bg-zinc-800">
                                            Sin foto
                                        </div>

                                    @endif

                                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">
                                        {{ $afiliado->asignacionVigente?->vehiculo?->placa ?? 'Sin asignar' }}
                                    </span>

                                </div>

                            </td>



                            <td class="px-5 py-4">
                                <span @class([
                                    'rounded-full px-3 py-1 text-xs font-semibold',
                                    'bg-emerald-100 text-emerald-800' => $afiliado->estado === 'ACTIVO',
                                    'bg-red-100 text-red-800' => $afiliado->estado === 'BLOQUEADO',
                                    'bg-zinc-200 text-zinc-700' => $afiliado->estado === 'RETIRADO',
                                    'bg-amber-100 text-amber-800' => $afiliado->estado === 'INACTIVO',
                                ])>
                                    {{ ucfirst(strtolower($afiliado->estado)) }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-right">
                                <div class="flex flex-wrap justify-end gap-3">
                                    @can('gestionar-padron')
                                    <button
                                        type="button"
                                        wire:click="editar({{ $afiliado->id }})"
                                        class="font-semibold text-emerald-700 hover:underline dark:text-emerald-400"
                                    >
                                        Editar
                                    </button>
                                    @if ($afiliado->estado === 'ACTIVO')
                                        <button
                                            type="button"
                                            wire:click="solicitarInactivacion({{ $afiliado->id }})"
                                            class="font-semibold text-amber-600 hover:underline"
                                        >
                                            Inactivar
                                        </button>
                                    @elseif ($afiliado->estado === 'INACTIVO')
                                        <button
                                            type="button"
                                            wire:click="solicitarReactivacion({{ $afiliado->id }})"
                                            class="font-semibold text-blue-600 hover:underline"
                                        >
                                            Reactivar
                                        </button>
                                    @endif
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
                            <td colspan="7" class="px-5 py-12 text-center text-zinc-500">
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

    {{-- MODAL DE INACTIVACIÓN Y REACTIVACIÓN --}}
    @can('gestionar-padron')
        @if ($mostrarCambioEstado)

        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">

            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-zinc-900">

                <h2 class="mb-3 text-xl font-bold text-zinc-900 dark:text-white">
                    {{ $cambioEstadoAccion === 'INACTIVAR'
                        ? 'Inactivar afiliado'
                        : 'Reactivar afiliado' }}
                </h2>

                <div class="mb-5 rounded-xl bg-zinc-100 p-4 dark:bg-zinc-800">

                    <p class="text-sm text-zinc-500">
                        Afiliado seleccionado
                    </p>

                    <p class="mt-1 font-bold text-zinc-900 dark:text-white">
                        {{ $cambioEstadoNombre }}
                    </p>

                </div>

                <form wire:submit="confirmarCambioEstado" class="space-y-4">

                    @if ($cambioEstadoAccion === 'INACTIVAR')

                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                            La inactividad suspenderá las nuevas mensualidades
                            desde el primer día del próximo mes.
                            Las deudas anteriores permanecerán pendientes.
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold dark:text-white">
                                Motivo de inactividad *
                            </label>

                            <textarea
                                wire:model="motivoInactividad"
                                rows="3"
                                placeholder="Indique el motivo de la inactividad"
                                class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                            ></textarea>

                            @error('motivoInactividad')
                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                    @else

                        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                            Al reactivarse, el afiliado volverá a generar
                            la mensualidad correspondiente al mes actual.
                            Las deudas pendientes se conservarán.
                        </div>

                    @endif

                    @error('cambioEstado')
                        <p class="text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                    <div class="flex justify-end gap-3 border-t border-zinc-200 pt-4">

                        <button
                            type="button"
                            wire:click="cerrarCambioEstado"
                            class="rounded-xl border border-zinc-300 px-5 py-3 font-semibold text-zinc-700 dark:text-white"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="confirmarCambioEstado"
                            class="rounded-xl px-5 py-3 font-semibold text-white disabled:opacity-50
                                {{ $cambioEstadoAccion === 'INACTIVAR'
                                    ? 'bg-amber-600 hover:bg-amber-700'
                                    : 'bg-blue-700 hover:bg-blue-800' }}"
                        >
                            {{ $cambioEstadoAccion === 'INACTIVAR'
                                ? 'Confirmar inactividad'
                                : 'Confirmar reactivación' }}
                        </button>

                    </div>

                </form>

            </div>

        </div>

        @endif
    @endcan
</div>