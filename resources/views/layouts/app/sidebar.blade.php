<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar
                sticky
                collapsible="mobile"
                class="border-e border-emerald-950
                    !bg-emerald-950
                    dark:border-emerald-950
                    dark:!bg-emerald-950"
            >
            <flux:sidebar.header>
                <a href="{{ route('dashboard') }}"
                    wire:navigate
                    class="flex items-center gap-3 px-2 py-2 text-white">

                        <div class="flex size-11 shrink-0 items-center
                                    justify-center rounded-full bg-red-700
                                    text-lg font-extrabold text-white
                                    ring-2 ring-white/30">
                            12
                        </div>

                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase leading-tight">
                                Cooperativa de Transportes
                            </p>

                            <p class="text-sm font-extrabold">
                                12 DE MAYO R.L.
                            </p>
                        </div>

                    </a>
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <livewire:team-switcher />

            <flux:sidebar.nav>
                <flux:sidebar.group heading="Administración" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        Inicio
                    </flux:sidebar.item>
                    <flux:sidebar.item
                        icon="users"
                        :href="route('grupos.index')"
                        :current="request()->routeIs('grupos.*')"
                        wire:navigate
                    >
                        Grupos
                    </flux:sidebar.item>
                    <flux:sidebar.item
                        icon="user-group"
                        :href="route('afiliados.index')"
                        :current="request()->routeIs('afiliados.*')"
                        wire:navigate
                    >
                        Afiliados
                    </flux:sidebar.item>
                    <flux:sidebar.item
                        icon="truck"
                        :href="route('vehiculos.index')"
                        :current="request()->routeIs('vehiculos.*')"
                        wire:navigate
                    >
                        Vehículos
                    </flux:sidebar.item>
                    <flux:sidebar.group heading="Administración financiera" class="grid">
                    <flux:sidebar.item
                        icon="document-text"
                        :href="route('ingresos.hojas-ruta')"
                        :current="request()->routeIs('ingresos.hojas-ruta')"
                        wire:navigate
                    >
                        Hojas de Ruta
                    </flux:sidebar.item>
                    <flux:sidebar.item
                        icon="currency-dollar"
                        :href="route('ingresos.tarifas')"
                        :current="request()->routeIs('ingresos.tarifas')"
                        wire:navigate
                    >
                        Tarifas
                    </flux:sidebar.item>
                    @can('consultar-recibos-hoja-ruta')

                        <flux:sidebar.item
                            icon="document-text"
                            :href="route('recibos-hoja-ruta.index', [
                                'current_team' => auth()->user()->currentTeam->slug
                            ])"
                            :current="request()->routeIs('recibos-hoja-ruta.index')"
                            wire:navigate
                        >
                            Historial de recibos
                        </flux:sidebar.item>

                    @endcan
                    @can('administrar-conceptos-ingreso')

                        <flux:sidebar.item
                            icon="adjustments-horizontal"
                            :href="route('ingresos.conceptos.index', [
                                'current_team' => auth()->user()->currentTeam->slug
                            ])"
                            :current="request()->routeIs('ingresos.conceptos.index')"
                            wire:navigate
                        >
                            Conceptos de ingresos
                        </flux:sidebar.item>

                    @endcan
                    @can('administrar-conceptos-ingreso')

                        <flux:sidebar.item
                            icon="banknotes"
                            :href="route('ingresos.tarifas-conceptos.index', [
                                'current_team' => auth()->user()->currentTeam->slug
                            ])"
                            :current="request()->routeIs('ingresos.tarifas-conceptos.index')"
                            wire:navigate
                        >
                            Tarifas de otros ingresos
                        </flux:sidebar.item>

                    @endcan
                    @can('cobrar-otros-ingresos')

                        <flux:sidebar.item
                            icon="wallet"
                            :href="route('ingresos.otros.index', [
                                'current_team' => auth()->user()->currentTeam->slug
                            ])"
                            :current="request()->routeIs('ingresos.otros.index')"
                            wire:navigate
                        >
                            Otros ingresos
                        </flux:sidebar.item>

                    @endcan
                    @can('cobrar-otros-ingresos')

                        <flux:sidebar.item
                            icon="clock"
                            :href="route('ingresos.otros.historial', [
                                'current_team' => auth()->user()->currentTeam->slug
                            ])"
                            :current="request()->routeIs('ingresos.otros.historial')"
                            wire:navigate
                        >
                            Historial de otros ingresos
                        </flux:sidebar.item>

                    @endcan
                    @can('gestionar-padron')
                        <flux:sidebar.item
                            icon="user-plus"
                            :href="route('ingresos.reincorporaciones.index', [
                                'current_team' => auth()->user()->currentTeam->slug
                            ])"
                            :current="request()->routeIs('ingresos.reincorporaciones.index')"
                            wire:navigate
                        >
                            Reincorporaciones
                        </flux:sidebar.item>
                    @endcan
                    @can('administrar-conceptos-egreso')

                        <flux:sidebar.item
                            :href="route('egresos.conceptos.index', [
                                'current_team' => auth()->user()->currentTeam->slug
                            ])"
                            :current="request()->routeIs('egresos.conceptos.index')"
                            wire:navigate
                        >
                            Conceptos de egreso
                        </flux:sidebar.item>

                    @endcan
                    @can('registrar-egresos')

                        <flux:sidebar.item
                            :href="route('egresos.registro.index', [
                                'current_team' => auth()->user()->currentTeam->slug
                            ])"
                            :current="request()->routeIs('egresos.registro.index')"
                            wire:navigate
                        >
                            Registrar egreso
                        </flux:sidebar.item>

                    @endcan
                    @can('registrar-egresos')

                        <flux:sidebar.item
                            :href="route('egresos.historial.index', [
                                'current_team' => auth()->user()->currentTeam->slug
                            ])"
                            :current="request()->routeIs('egresos.historial.index')"
                            wire:navigate
                        >
                            Historial de egresos
                        </flux:sidebar.item>

                    @endcan
                    @can('ver-informe-economico')

                        <flux:sidebar.item
                            :href="route('informes.economico.index', [
                                'current_team' => auth()->user()->currentTeam->slug
                            ])"
                            :current="request()->routeIs('informes.economico.index')"
                            wire:navigate
                        >
                            Informe Económico
                        </flux:sidebar.item>

                    @endcan

                </flux:sidebar.group>
                </flux:sidebar.group>
                
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                    {{ __('Repository') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                    {{ __('Documentation') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        <livewire:create-team-modal />

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
