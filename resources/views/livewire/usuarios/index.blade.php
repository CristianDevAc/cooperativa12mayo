<div class="mx-auto w-full max-w-7xl space-y-6 pb-10">

    {{-- ENCABEZADO --}}
    <div class="rounded-2xl bg-gradient-to-r from-emerald-950 to-emerald-800 p-6 text-white shadow-lg">

        <h1 class="text-2xl font-bold">
            Administración de Usuarios
        </h1>

        <p class="mt-2 text-sm text-emerald-100">
            Cooperativa de Transportes 12 de Mayo R.L.
        </p>

        <p class="mt-2 text-sm text-emerald-100">
            Gestión de integrantes y asignación de roles.
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

    {{-- RESUMEN --}}
    <div class="grid gap-4 md:grid-cols-3">

        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500">
                Usuarios de la cooperativa
            </p>

            <p class="mt-2 text-3xl font-bold">
                {{ $usuarios->count() }}
            </p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500">
                Administradores y propietarios
            </p>

            <p class="mt-2 text-3xl font-bold">
                {{ $usuarios->whereIn('role', ['owner', 'admin'])->count() }}
            </p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500">
                Cajeros
            </p>

            <p class="mt-2 text-3xl font-bold">
                {{ $usuarios->where('role', 'cashier')->count() }}
            </p>
        </div>

    </div>

    {{-- FORMULARIO PARA CAMBIAR ROL --}}
    @if($usuarioEditarId !== null)

        <div class="rounded-xl border border-emerald-300 bg-white p-6 shadow-sm dark:border-emerald-800 dark:bg-zinc-900">

            <h2 class="text-lg font-bold">
                Modificar rol de usuario
            </h2>

            <p class="mt-2 text-sm text-zinc-500">
                Usuario: <strong>{{ $usuarioEditarNombre }}</strong>
            </p>

            <form wire:submit="guardarRol" class="mt-5 space-y-5">

                <div>
                    <label class="mb-2 block text-sm font-semibold">
                        Rol asignado
                    </label>

                    <select
                        wire:model="rolNuevo"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    >

                        @if($rolActor === 'owner')
                            <option value="admin">
                                Administrador
                            </option>
                        @endif

                        <option value="cashier">
                            Cajero
                        </option>

                        <option value="supervisor">
                            Supervisor
                        </option>

                        <option value="member">
                            Miembro
                        </option>

                    </select>

                    @error('rolNuevo')
                        <p class="mt-2 text-sm text-red-600">
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
                        wire:confirm="¿Confirma el cambio de rol de este usuario?"
                        wire:loading.attr="disabled"
                        wire:target="guardarRol"
                        class="rounded-lg bg-emerald-700 px-6 py-3 font-bold text-white hover:bg-emerald-800 disabled:opacity-50"
                    >
                        Guardar rol
                    </button>

                </div>

            </form>

        </div>

    @endif

    <div class="flex justify-end">

        <button
            type="button"
            wire:click="abrirNuevoUsuario"
            class="rounded-lg bg-emerald-700 px-5 py-3 font-semibold text-white hover:bg-emerald-800"
        >
            + Nuevo usuario
        </button>

    </div>

    @if($formularioNuevoVisible)

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

            <h2 class="mb-5 text-xl font-bold">
                Registrar nuevo usuario
            </h2>

            <form wire:submit="crearUsuario" class="space-y-4">

                <div>
                    <label class="mb-1 block text-sm font-semibold">
                        Nombre completo
                    </label>

                    <input
                        type="text"
                        wire:model="nuevoNombre"
                        autocomplete="off"
                        class="w-full rounded-lg border border-zinc-300 bg-white p-3 text-zinc-900 placeholder:text-zinc-400 focus:border-emerald-600 focus:ring-emerald-600 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-400"
                    >

                    @error('nuevoNombre')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold">
                        Correo electrónico
                    </label>

                    <input
                        type="email"
                        wire:model="nuevoEmail"
                        autocomplete="off"
                        class="w-full rounded-lg border border-zinc-300 bg-white p-3 text-zinc-900 placeholder:text-zinc-400 focus:border-emerald-600 focus:ring-emerald-600 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-400"
                    >

                    @error('nuevoEmail')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold">
                        Rol
                    </label>

                    <select
                        wire:model="nuevoRolUsuario"
                        class="w-full rounded-lg border border-zinc-300 bg-white p-3 text-zinc-900 placeholder:text-zinc-400 focus:border-emerald-600 focus:ring-emerald-600 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-400"
                    >
                        @if($rolActor === 'owner')
                            <option value="admin">Administrador</option>
                        @endif

                        <option value="cashier">Cajero</option>
                        <option value="supervisor">Supervisor</option>
                        <option value="member">Miembro</option>
                    </select>

                    @error('nuevoRolUsuario')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 md:grid-cols-2">

                    <div>
                        <label class="mb-1 block text-sm font-semibold">
                            Contraseña
                        </label>

                        <input
                            type="password"
                            wire:model="nuevoPassword"
                            autocomplete="new-password"
                            class="w-full rounded-lg border border-zinc-300 bg-white p-3 text-zinc-900 placeholder:text-zinc-400 focus:border-emerald-600 focus:ring-emerald-600 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-400"
                        >

                        @error('nuevoPassword')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold">
                            Confirmar contraseña
                        </label>

                        <input
                            type="password"
                            wire:model="nuevoPassword_confirmation"
                            autocomplete="new-password"
                            class="w-full rounded-lg border border-zinc-300 bg-white p-3 text-zinc-900 placeholder:text-zinc-400 focus:border-emerald-600 focus:ring-emerald-600 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-400"
                        >
                    </div>

                </div>

                <div class="flex justify-end gap-3">

                    <button
                        type="button"
                        wire:click="cancelarNuevoUsuario"
                        class="rounded-lg border border-zinc-300 px-5 py-3"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="crearUsuario"
                        class="rounded-lg bg-emerald-700 px-5 py-3 font-semibold text-white hover:bg-emerald-800 disabled:opacity-50"
                    >
                        Guardar usuario
                    </button>

                </div>

            </form>

        </div>

    @endif
    



    {{-- LISTA DE USUARIOS --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

        <div class="border-b border-zinc-200 p-5 dark:border-zinc-700">
            <h2 class="text-lg font-bold">
                Usuarios registrados
            </h2>
        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-left text-sm">

                <thead class="bg-zinc-100 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3">Usuario</th>
                        <th class="px-4 py-3">Correo electrónico</th>
                        <th class="px-4 py-3">Rol</th>
                        <th class="px-4 py-3">Acción</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">

                    @forelse($usuarios as $usuario)

                        <tr wire:key="usuario-{{ $usuario->id }}">

                            <td class="px-4 py-4 font-semibold">
                                {{ $usuario->name }}

                                @if((int) $usuario->id === $actorId)
                                    <span class="ml-1 text-xs text-zinc-500">
                                        (Tú)
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-4">
                                {{ $usuario->email }}
                            </td>

                            <td class="px-4 py-4">

                                @php
                                    $nombreRol = match($usuario->role) {
                                        'owner' => 'Propietario',
                                        'admin' => 'Administrador',
                                        'cashier' => 'Cajero',
                                        'supervisor' => 'Supervisor',
                                        'member' => 'Miembro',
                                        default => $usuario->role,
                                    };
                                @endphp

                                <span class="rounded-full px-3 py-1 text-xs font-bold
                                    {{ in_array($usuario->role, ['owner', 'admin'])
                                        ? 'bg-emerald-100 text-emerald-800'
                                        : 'bg-zinc-200 text-zinc-800' }}">
                                    {{ $nombreRol }}
                                </span>

                            </td>

                            <td class="px-4 py-4">

                                @if(
                                    $usuario->role === 'owner'
                                    || (int) $usuario->id === $actorId
                                    || (
                                        $rolActor === 'admin'
                                        && $usuario->role === 'admin'
                                    )
                                )

                                    <span class="text-xs text-zinc-500">
                                        Protegido
                                    </span>

                                @else

                                    <button
                                        type="button"
                                        wire:click="editar({{ $usuario->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="editar"
                                        class="rounded-lg bg-emerald-700 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-800 disabled:opacity-50"
                                    >
                                        Cambiar rol
                                    </button>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-zinc-500">
                                No hay usuarios registrados.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    <p class="text-xs text-zinc-500">
        Los usuarios mostrados pertenecen únicamente a la cooperativa activa.
        La creación de nuevas cuentas y la revocación de accesos
        se incorporarán en el siguiente paso.
    </p>

</div>  