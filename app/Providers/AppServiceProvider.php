<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use App\Enums\TeamRole;
use App\Models\User;
use Illuminate\Support\Facades\Gate;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        /*
        |--------------------------------------------------------------------------
        | Permiso para cobrar hojas de ruta
        |--------------------------------------------------------------------------
        |
        | Inicialmente, solamente el propietario
        | del equipo puede registrar cobros.
        |
        */

        Gate::define('cobrar-hojas-ruta', function (User $user): bool {

            if ($user->current_team_id === null) {
                return false;
            }

            return DB::table('team_members')
                ->where('team_id', $user->current_team_id)
                ->where('user_id', $user->id)
                ->whereIn('role', [
                    TeamRole::Owner->value,
                    TeamRole::Admin->value,
                    TeamRole::Cashier->value,
                ])
                ->exists();
        });
        /*
        |--------------------------------------------------------------------------
        | Permiso para administrar tarifas
        |--------------------------------------------------------------------------
        |
        | Solamente propietarios y administradores
        | pueden modificar tarifas y multas.
        |
        */

        Gate::define('administrar-tarifas-hoja-ruta', function (User $user): bool {

            if ($user->current_team_id === null) {
                return false;
            }

            return DB::table('team_members')
                ->where('team_id', $user->current_team_id)
                ->where('user_id', $user->id)
                ->whereIn('role', [
                    TeamRole::Owner->value,
                    TeamRole::Admin->value,
                ])
                ->exists();
        });
        Gate::define('gestionar-padron', function (User $user): bool {

            if (! $user->current_team_id) {
                return false;
            }

            return DB::table('team_members')
                ->where('team_id', $user->current_team_id)
                ->where('user_id', $user->id)
                ->whereIn('role', [
                    TeamRole::Owner->value,
                    TeamRole::Admin->value,
                ])
                ->exists();
        });
        Gate::define('consultar-recibos-hoja-ruta', function (User $user): bool {

            if ($user->current_team_id === null) {
                return false;
            }

            return DB::table('team_members')
                ->where('team_id', $user->current_team_id)
                ->where('user_id', $user->id)
                ->whereIn('role', [
                    'owner',
                    'admin',
                    'cashier',
                    'supervisor',
                ])
                ->exists();
        });
        Gate::define('administrar-conceptos-ingreso', function (User $user): bool {

            if ($user->current_team_id === null) {
                return false;
            }

            return DB::table('team_members')
                ->where('team_id', $user->current_team_id)
                ->where('user_id', $user->id)
                ->whereIn('role', [
                    'owner',
                    'admin',
                ])
                ->exists();
        });
        Gate::define('cobrar-otros-ingresos', function (User $user): bool {

            if ($user->current_team_id === null) {
                return false;
            }

            return DB::table('team_members')
                ->where('team_id', $user->current_team_id)
                ->where('user_id', $user->id)
                ->whereIn('role', [
                    'owner',
                    'admin',
                    'cashier',
                ])
                ->exists();
        });
        Gate::define('registrar-egresos', function (User $user): bool {

            if ($user->current_team_id === null) {
                return false;
            }

            return DB::table('team_members')
                ->where('team_id', $user->current_team_id)
                ->where('user_id', $user->id)
                ->whereIn('role', [
                    'owner',
                    'admin',
                    'cashier',
                ])
                ->exists();
        });
        Gate::define('administrar-conceptos-egreso', function (User $user): bool {

            if ($user->current_team_id === null) {
                return false;
            }

            return DB::table('team_members')
                ->where('team_id', $user->current_team_id)
                ->where('user_id', $user->id)
                ->whereIn('role', [
                    'owner',
                    'admin',
                ])
                ->exists();
        });
        Gate::define('anular-egresos', function (User $user): bool {

            if ($user->current_team_id === null) {
                return false;
            }

            return DB::table('team_members')
                ->where('team_id', $user->current_team_id)
                ->where('user_id', $user->id)
                ->whereIn('role', [
                    'owner',
                    'admin',
                ])
                ->exists();
        });
        Gate::define('ver-informe-economico', function (User $user): bool {

            if ($user->current_team_id === null) {
                return false;
            }

            return DB::table('team_members')
                ->where('team_id', $user->current_team_id)
                ->where('user_id', $user->id)
                ->whereIn('role', [
                    'owner',
                    'admin',
                ])
                ->exists();
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
