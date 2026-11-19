<x-layouts::app :title="__('Inicio')">

    <livewire:pages::teams.pending-invitations-modal />

    <div class="flex w-full flex-1 flex-col gap-6">

        <!-- ENCABEZADO INSTITUCIONAL -->
        <section class="rounded-2xl bg-gradient-to-r
                        from-red-900 via-red-700 to-red-600
                        p-6 text-white shadow-lg">

            <p class="text-xs font-semibold uppercase tracking-widest
                      text-red-100">
                Sistema de Gestión Administrativa
            </p>

            <h1 class="mt-2 text-2xl font-bold sm:text-3xl">
                Cooperativa de Transportes 12 de Mayo R.L.
            </h1>

            <p class="mt-3 text-sm text-red-100">
                Bienvenido, {{ auth()->user()->name }}
            </p>

            <p class="mt-1 text-xs text-red-100">
                Potosí - Bolivia
            </p>

        </section>

        <!-- INDICADORES DE AFILIADOS -->
        <div class="grid gap-4 md:grid-cols-3">

            <div class="rounded-xl border border-zinc-200
                        bg-white p-5 shadow-sm
                        dark:border-zinc-700 dark:bg-zinc-900">

                <p class="text-sm font-medium text-zinc-500
                          dark:text-zinc-400">
                    Total de afiliados
                </p>

                <p class="mt-3 text-3xl font-bold text-zinc-900
                          dark:text-white">
                    —
                </p>

                <p class="mt-2 text-xs text-zinc-500">
                    Pendiente del módulo Afiliados
                </p>
            </div>

            <div class="rounded-xl border border-zinc-200
                        bg-white p-5 shadow-sm
                        dark:border-zinc-700 dark:bg-zinc-900">

                <p class="text-sm font-medium text-zinc-500
                          dark:text-zinc-400">
                    Afiliados activos
                </p>

                <p class="mt-3 text-3xl font-bold text-emerald-600">
                    —
                </p>

                <p class="mt-2 text-xs text-zinc-500">
                    Pendiente de implementación
                </p>
            </div>

            <div class="rounded-xl border border-zinc-200
                        bg-white p-5 shadow-sm
                        dark:border-zinc-700 dark:bg-zinc-900">

                <p class="text-sm font-medium text-zinc-500
                          dark:text-zinc-400">
                    Afiliados bloqueados
                </p>

                <p class="mt-3 text-3xl font-bold text-red-600">
                    —
                </p>

                <p class="mt-2 text-xs text-zinc-500">
                    Pendiente de implementación
                </p>
            </div>

        </div>

        <!-- ESTADO DEL SISTEMA -->
        <section class="rounded-xl border border-zinc-200
                        bg-white p-6 shadow-sm
                        dark:border-zinc-700 dark:bg-zinc-900">

            <h2 class="text-lg font-semibold text-zinc-900
                       dark:text-white">
                Información del sistema
            </h2>

            <div class="mt-5 grid gap-4 md:grid-cols-2">

                <div class="rounded-lg bg-emerald-50 p-4
                            dark:bg-emerald-950">

                    <p class="text-sm text-emerald-700
                              dark:text-emerald-300">
                        Usuarios registrados
                    </p>

                    <p class="mt-2 text-3xl font-bold text-emerald-800
                              dark:text-emerald-200">
                        {{ number_format($totalUsuarios) }}
                    </p>

                    <p class="mt-2 text-xs text-emerald-700
                              dark:text-emerald-300">
                        Información obtenida de PostgreSQL
                    </p>
                </div>

                <div class="rounded-lg bg-zinc-100 p-4
                            dark:bg-zinc-800">

                    <p class="text-sm text-zinc-600
                              dark:text-zinc-300">
                        Estado del sistema
                    </p>

                    <p class="mt-2 text-lg font-bold text-zinc-900
                              dark:text-white">
                        Configuración inicial
                    </p>

                    <p class="mt-2 text-xs text-zinc-500">
                        Próximamente: gestión de afiliados y caja
                    </p>
                </div>

            </div>

        </section>

    </div>

</x-layouts::app>