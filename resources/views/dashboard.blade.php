<x-layouts::app :title="__('Inicio | Cooperativa 12 de Mayo')">
    @php
        $usuario = auth()->user();
        $equipo = $usuario->currentTeam;
        $resumen = app(\App\Services\Dashboard\ResumenCooperativa::class)->obtener($usuario);
        $teamSlug = $equipo?->slug;
        $nombreCorto = explode(' ', trim($usuario->name))[0] ?? $usuario->name;
        $porcentajeActivos = ($resumen['afiliados'] ?? 0) > 0 && $resumen['activos'] !== null
            ? min(100, round($resumen['activos'] * 100 / $resumen['afiliados']))
            : 0;
        $formatear = fn ($dato) => $dato === null ? '—' : number_format($dato, 0, ',', '.');
    @endphp

    <main class="mx-auto w-full max-w-[1600px] space-y-6 p-4 sm:p-6 xl:p-8">

        {{-- PORTADA INSTITUCIONAL --}}
        <section class="relative overflow-hidden rounded-2xl border border-red-900/40 bg-gradient-to-r from-[#7f101b] via-[#af0d1c] to-[#df1021] text-white shadow-xl shadow-black/10">
            <div class="absolute -right-24 -top-32 size-96 rounded-full border-[38px] border-white/5" aria-hidden="true"></div>
            <div class="absolute bottom-0 right-48 size-36 translate-y-1/2 rounded-full bg-emerald-950/20 blur-2xl" aria-hidden="true"></div>
            <div class="relative flex flex-col items-start justify-between gap-5 px-6 py-6 sm:px-8 md:flex-row md:items-center md:py-8">
                <div class="flex items-center gap-5">
                    <div class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white p-1 shadow-lg ring-4 ring-white/20 sm:size-24">
                        <img src="{{ asset('images/logo-12-mayo.png') }}"
                             alt="Escudo Cooperativa 12 de Mayo" class="size-full object-contain">
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.18em] text-white/75">Sistema de gestión administrativa</p>
                        <h1 class="mt-1 text-2xl font-extrabold leading-tight tracking-tight sm:text-3xl lg:text-[32px]">
                            {{ $equipo?->name ?? 'Cooperativa de Transportes 12 de Mayo R.L.' }}
                        </h1>
                        <p class="mt-2 text-sm text-white/85">Bienvenido, <strong>{{ $nombreCorto }}</strong>. Este es el estado general de tu cooperativa.</p>
                    </div>
                </div>
                <div class="shrink-0 rounded-xl border border-white/25 bg-white/10 px-4 py-3 text-sm backdrop-blur-sm">
                    <div class="flex items-center gap-2 font-semibold"><span class="size-2 rounded-full bg-emerald-300"></span> Panel principal</div>
                    <p class="mt-1 text-xs capitalize text-white/80">{{ now()->locale('es')->translatedFormat('l, j F Y') }}</p>
                    <p class="mt-0.5 text-[11px] text-white/65">Potosí · Bolivia</p>
                </div>
            </div>
        </section>

        {{-- INDICADORES REALES DE LA COOPERATIVA --}}
        <section aria-labelledby="indicadores" class="space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 id="indicadores" class="text-lg font-bold text-zinc-100">Resumen general</h2>
                <span class="text-xs text-zinc-400">Información de PostgreSQL · Cooperativa activa</span>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <a href="{{ route('afiliados.index') }}" wire:navigate
                   class="group rounded-2xl border border-zinc-700/80 bg-zinc-900 p-5 shadow-sm transition hover:border-emerald-500/60 hover:bg-zinc-800/70">
                    <div class="flex items-start justify-between gap-3">
                        <div><p class="text-sm text-zinc-400">Total de afiliados</p>
                            <p class="mt-3 text-3xl font-extrabold tabular-nums text-white">{{ $formatear($resumen['afiliados']) }}</p></div>
                        <span class="flex size-11 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-400"><flux:icon name="users" class="size-6" /></span>
                    </div>
                    <p class="mt-4 text-xs font-semibold text-emerald-400 group-hover:text-emerald-300">Ver padrón <span aria-hidden="true">→</span></p>
                </a>

                <a href="{{ route('afiliados.index') }}" wire:navigate
                   class="group rounded-2xl border border-zinc-700/80 bg-zinc-900 p-5 shadow-sm transition hover:border-emerald-500/60 hover:bg-zinc-800/70">
                    <div class="flex items-start justify-between gap-3">
                        <div><p class="text-sm text-zinc-400">Afiliados activos</p>
                            <p class="mt-3 text-3xl font-extrabold tabular-nums text-white">{{ $formatear($resumen['activos']) }}</p></div>
                        <span class="flex size-11 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-400"><flux:icon name="check-circle" class="size-6" /></span>
                    </div>
                    <p class="mt-4 text-xs text-zinc-500">Estado: ACTIVO</p>
                </a>

                <a href="{{ route('vehiculos.index') }}" wire:navigate
                   class="group rounded-2xl border border-zinc-700/80 bg-zinc-900 p-5 shadow-sm transition hover:border-emerald-500/60 hover:bg-zinc-800/70">
                    <div class="flex items-start justify-between gap-3">
                        <div><p class="text-sm text-zinc-400">Vehículos registrados</p>
                            <p class="mt-3 text-3xl font-extrabold tabular-nums text-white">{{ $formatear($resumen['vehiculos']) }}</p></div>
                        <span class="flex size-11 items-center justify-center rounded-xl bg-sky-500/15 text-sky-400"><flux:icon name="truck" class="size-6" /></span>
                    </div>
                    <p class="mt-4 text-xs font-semibold text-sky-400 group-hover:text-sky-300">Ver vehículos <span aria-hidden="true">→</span></p>
                </a>

                @can('gestionar-usuarios')
                    <a href="{{ route('usuarios.index', ['current_team' => $teamSlug]) }}" wire:navigate
                       class="group rounded-2xl border border-zinc-700/80 bg-zinc-900 p-5 shadow-sm transition hover:border-emerald-500/60 hover:bg-zinc-800/70">
                        <div class="flex items-start justify-between gap-3">
                            <div><p class="text-sm text-zinc-400">Usuarios de la cooperativa</p>
                                <p class="mt-3 text-3xl font-extrabold tabular-nums text-white">{{ $formatear($resumen['usuarios']) }}</p></div>
                            <span class="flex size-11 items-center justify-center rounded-xl bg-violet-500/15 text-violet-400"><flux:icon name="shield-check" class="size-6" /></span>
                        </div>
                        <p class="mt-4 text-xs font-semibold text-violet-400 group-hover:text-violet-300">Administrar accesos <span aria-hidden="true">→</span></p>
                    </a>
                @else
                    <div class="rounded-2xl border border-zinc-700/80 bg-zinc-900 p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div><p class="text-sm text-zinc-400">Grupos de la cooperativa</p>
                                <p class="mt-3 text-3xl font-extrabold tabular-nums text-white">{{ $formatear($resumen['grupos']) }}</p></div>
                            <span class="flex size-11 items-center justify-center rounded-xl bg-violet-500/15 text-violet-400"><flux:icon name="users" class="size-6" /></span>
                        </div>
                        <p class="mt-4 text-xs text-zinc-500">Padrón por grupos</p>
                    </div>
                @endcan
            </div>
        </section>

        {{-- SECCIÓN CENTRAL --}}
        <div class="grid gap-5 xl:grid-cols-5">
            <section class="rounded-2xl border border-zinc-700/80 bg-zinc-900 p-5 sm:p-6 xl:col-span-3" aria-labelledby="operacion-diaria">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 id="operacion-diaria" class="text-lg font-bold text-white">Resumen de operaciones</h2>
                        <p class="mt-1 text-xs text-zinc-400">Cantidad acumulada de registros · No son montos en Bs</p>
                    </div>
                    @can('ver-informe-economico')
                        <a href="{{ route('informes.economico.index', ['current_team' => $teamSlug]) }}"
                           wire:navigate class="text-xs font-bold text-emerald-400 hover:text-emerald-300">Informe económico →</a>
                    @endcan
                </div>
                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    @if($resumen['puede_ver_recibos'])
                        <a href="{{ route('recibos-hoja-ruta.index', ['current_team' => $teamSlug]) }}" wire:navigate
                           class="rounded-xl border border-zinc-700 bg-zinc-950/50 p-4 transition hover:border-emerald-600">
                            <div class="text-xs text-zinc-400">Recibos hojas de ruta</div>
                            <div class="mt-3 text-2xl font-bold tabular-nums text-white">{{ $formatear($resumen['recibos_ruta']) }}</div>
                            <div class="mt-2 text-[11px] text-emerald-400">Ver historial →</div>
                        </a>
                    @endif
                    @if($resumen['puede_ver_otros_ingresos'])
                        <a href="{{ route('ingresos.otros.historial', ['current_team' => $teamSlug]) }}" wire:navigate
                           class="rounded-xl border border-zinc-700 bg-zinc-950/50 p-4 transition hover:border-emerald-600">
                            <div class="text-xs text-zinc-400">Recibos otros ingresos</div>
                            <div class="mt-3 text-2xl font-bold tabular-nums text-white">{{ $formatear($resumen['otros_ingresos']) }}</div>
                            <div class="mt-2 text-[11px] text-emerald-400">Ver historial →</div>
                        </a>
                    @endif
                    @if($resumen['puede_ver_egresos'])
                        <a href="{{ route('egresos.historial.index', ['current_team' => $teamSlug]) }}" wire:navigate
                           class="rounded-xl border border-zinc-700 bg-zinc-950/50 p-4 transition hover:border-emerald-600">
                            <div class="text-xs text-zinc-400">Egresos registrados</div>
                            <div class="mt-3 text-2xl font-bold tabular-nums text-white">{{ $formatear($resumen['egresos']) }}</div>
                            <div class="mt-2 text-[11px] text-emerald-400">Ver historial →</div>
                        </a>
                    @endif
                    @if(! $resumen['puede_ver_recibos'] && ! $resumen['puede_ver_otros_ingresos'] && ! $resumen['puede_ver_egresos'])
                        <p class="text-sm text-zinc-400 sm:col-span-3">No tienes permisos para consultar las operaciones financieras.</p>
                    @endif
                </div>
            </section>

            <section class="rounded-2xl border border-zinc-700/80 bg-zinc-900 p-5 sm:p-6 xl:col-span-2" aria-labelledby="estado-padron">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 id="estado-padron" class="text-lg font-bold text-white">Estado del padrón</h2>
                        <p class="mt-1 text-xs text-zinc-400">Distribución de afiliados</p>
                    </div>
                    <span class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-400">{{ $porcentajeActivos }}% activos</span>
                </div>
                <div class="mt-8 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-4xl font-extrabold tabular-nums text-white">{{ $formatear($resumen['afiliados']) }}</p>
                        <p class="mt-1 text-xs text-zinc-400">Afiliados registrados</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-extrabold tabular-nums text-emerald-400">{{ $formatear($resumen['activos']) }}</p>
                        <p class="text-xs text-zinc-400">Con estado ACTIVO</p>
                    </div>
                </div>
                <div class="mt-6 h-3 w-full overflow-hidden rounded-full bg-zinc-700" role="progressbar"
                     aria-label="Porcentaje de afiliados activos" aria-valuemin="0" aria-valuemax="100"
                     aria-valuenow="{{ $porcentajeActivos }}">
                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-700 to-emerald-400" style="width: {{ $porcentajeActivos }}%"></div>
                </div>
                <p class="mt-4 text-xs leading-relaxed text-zinc-500">El resto corresponde a afiliados con otros estados registrados en el sistema. El indicador se calcula con los datos de tu cooperativa.</p>
            </section>
        </div>

        {{-- ACCESOS Y ACTIVIDAD --}}
        <div class="grid gap-5 xl:grid-cols-5">
            <section class="rounded-2xl border border-zinc-700/80 bg-zinc-900 p-5 sm:p-6 xl:col-span-3" aria-labelledby="accesos-rapidos">
                <h2 id="accesos-rapidos" class="text-lg font-bold text-white">Accesos rápidos</h2>
                <p class="mt-1 text-xs text-zinc-400">Ingresa directamente a los módulos de trabajo</p>
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <a href="{{ route('afiliados.index') }}" wire:navigate class="flex items-center gap-3 rounded-xl border border-zinc-700 bg-zinc-950/40 p-4 transition hover:border-emerald-600 hover:bg-zinc-800">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-400"><flux:icon name="users" class="size-5" /></span>
                        <span><strong class="block text-sm text-zinc-100">Padrón de afiliados</strong><small class="text-xs text-zinc-400">Consultar y gestionar afiliados</small></span>
                    </a>
                    <a href="{{ route('vehiculos.index') }}" wire:navigate class="flex items-center gap-3 rounded-xl border border-zinc-700 bg-zinc-950/40 p-4 transition hover:border-emerald-600 hover:bg-zinc-800">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-sky-500/15 text-sky-400"><flux:icon name="truck" class="size-5" /></span>
                        <span><strong class="block text-sm text-zinc-100">Vehículos</strong><small class="text-xs text-zinc-400">Padrón y seguimiento de flota</small></span>
                    </a>
                    <a href="{{ route('ingresos.hojas-ruta') }}" wire:navigate class="flex items-center gap-3 rounded-xl border border-zinc-700 bg-zinc-950/40 p-4 transition hover:border-emerald-600 hover:bg-zinc-800">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-amber-500/15 text-amber-400"><flux:icon name="document-text" class="size-5" /></span>
                        <span><strong class="block text-sm text-zinc-100">Hojas de ruta</strong><small class="text-xs text-zinc-400">Venta y control de mensualidades</small></span>
                    </a>
                    <a href="{{ route('grupos.index') }}" wire:navigate class="flex items-center gap-3 rounded-xl border border-zinc-700 bg-zinc-950/40 p-4 transition hover:border-emerald-600 hover:bg-zinc-800">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-violet-500/15 text-violet-400"><flux:icon name="users" class="size-5" /></span>
                        <span><strong class="block text-sm text-zinc-100">Grupos</strong><small class="text-xs text-zinc-400">Organización de la cooperativa</small></span>
                    </a>
                    @can('cobrar-otros-ingresos')
                        <a href="{{ route('ingresos.otros.index', ['current_team' => $teamSlug]) }}" wire:navigate class="flex items-center gap-3 rounded-xl border border-zinc-700 bg-zinc-950/40 p-4 transition hover:border-emerald-600 hover:bg-zinc-800">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-400"><flux:icon name="wallet" class="size-5" /></span>
                            <span><strong class="block text-sm text-zinc-100">Otros ingresos</strong><small class="text-xs text-zinc-400">Registrar cobros autorizados</small></span>
                        </a>
                    @endcan
                    @can('registrar-egresos')
                        <a href="{{ route('egresos.registro.index', ['current_team' => $teamSlug]) }}" wire:navigate class="flex items-center gap-3 rounded-xl border border-zinc-700 bg-zinc-950/40 p-4 transition hover:border-emerald-600 hover:bg-zinc-800">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-red-500/15 text-red-400"><flux:icon name="arrow-up-tray" class="size-5" /></span>
                            <span><strong class="block text-sm text-zinc-100">Registrar egreso</strong><small class="text-xs text-zinc-400">Controlar salidas autorizadas</small></span>
                        </a>
                    @endcan
                </div>
            </section>

            <section class="rounded-2xl border border-zinc-700/80 bg-zinc-900 p-5 sm:p-6 xl:col-span-2" aria-labelledby="movimientos-recientes">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 id="movimientos-recientes" class="text-lg font-bold text-white">Movimientos recientes</h2>
                        <p class="mt-1 text-xs text-zinc-400">Últimos registros permitidos</p>
                    </div>
                    <flux:icon name="clock" class="size-5 text-zinc-500" />
                </div>
                <div class="mt-5 divide-y divide-zinc-800">
                    @forelse($resumen['movimientos'] as $movimiento)
                        <a href="{{ route($movimiento['ruta'], ['current_team' => $teamSlug]) }}" wire:navigate class="flex items-center justify-between gap-3 py-3 transition hover:text-emerald-400">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-zinc-800 text-emerald-400"><flux:icon name="document-text" class="size-4" /></span>
                                <div class="min-w-0"><p class="truncate text-sm font-semibold text-zinc-200">{{ $movimiento['tipo'] }}</p>
                                    <p class="text-xs text-zinc-500">Registro #{{ $movimiento['numero'] }}</p></div>
                            </div>
                            <span class="shrink-0 text-right text-[11px] text-zinc-500">{{ \Illuminate\Support\Carbon::parse($movimiento['fecha'])->format('d/m/Y') }}</span>
                        </a>
                    @empty
                        <div class="rounded-xl border border-dashed border-zinc-700 px-4 py-8 text-center">
                            <p class="text-sm font-semibold text-zinc-300">Sin movimientos para mostrar</p>
                            <p class="mt-1 text-xs text-zinc-500">Aparecerán los registros que tengas permiso para consultar.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <footer class="flex flex-wrap items-center justify-between gap-2 border-t border-zinc-800 pt-3 text-[11px] text-zinc-500">
            <span>Cooperativa de Transportes 12 de Mayo R.L. · Potosí, Bolivia</span>
            <span>Panel administrativo · Datos filtrados por cooperativa</span>
        </footer>
    </main>
</x-layouts::app>
