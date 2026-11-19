<?php

use App\Http\Middleware\EnsureTeamMembership;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Ingresos\ImprimirReciboHojaRutaController;
use App\Http\Controllers\Ingresos\ImprimirReciboOtroIngresoController;
use App\Http\Controllers\Egresos\ComprobanteEgresoController;
use App\Http\Controllers\Informes\InformeEconomicoImprimirController;

/*
|--------------------------------------------------------------------------
| Página principal
|--------------------------------------------------------------------------
*/

Route::view('/', 'welcome')->name('home');

/*
|--------------------------------------------------------------------------
| Redirección después del inicio de sesión
|--------------------------------------------------------------------------
|
| Si Laravel envía al usuario a /dashboard,
| lo redirigimos al dashboard de su equipo activo.
|
*/

Route::get('/dashboard', function () {

    $user = auth()->user();

    $team = $user->currentTeam;

    if (! $team) {
        abort(403, 'El usuario no tiene un equipo activo.');
    }

    return redirect()->route('dashboard', [
        'current_team' => $team->slug,
    ]);

})->middleware(['auth', 'verified']);

/*
|--------------------------------------------------------------------------
| Rutas administrativas de la cooperativa
|--------------------------------------------------------------------------
|
| Las rutas están protegidas mediante autenticación,
| verificación de correo y pertenencia al equipo.
|
*/

Route::prefix('{current_team}')
    ->middleware([
        'auth',
        'verified',
        EnsureTeamMembership::class,
    ])
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard administrativo
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', function () {

            return view('dashboard', [
                'totalUsuarios' => User::query()->count(),
            ]);

        })->name('dashboard');
        Route::view('grupos', 'grupos.index')->name('grupos.index');
        Route::view('afiliados', 'afiliados.index')->name('afiliados.index');
        Route::view('vehiculos', 'vehiculos.index')->name('vehiculos.index');
        Route::get('vehiculos/{vehiculo}/foto', function (
            string $current_team,
            \App\Models\Vehiculo $vehiculo
        ) {
            $usuario = \Illuminate\Support\Facades\Auth::user();
            abort_unless(
                $usuario instanceof \App\Models\User
                && (int) $vehiculo->team_id === (int) $usuario->current_team_id,
                404
            );

            $disco = \Illuminate\Support\Facades\Storage::disk('local');

            abort_unless(
                $vehiculo->foto_path && $disco->exists($vehiculo->foto_path),
                404
            );

            return response()->file(
                $disco->path($vehiculo->foto_path),
                [
                    'Cache-Control' => 'private, max-age=3600',
                    'X-Content-Type-Options' => 'nosniff',
                ]
            );

        })->name('vehiculos.foto');
        // INGRESOS - HOJAS DE RUTA
        Route::view(
            'ingresos/hojas-ruta',
            'ingresos.hojas-ruta'
        )->name('ingresos.hojas-ruta');
        Route::view(
            'ingresos/tarifas',
            'ingresos.tarifas'
        )->name('ingresos.tarifas');
        Route::view('recibos', 'ingresos.recibos.index')
        ->name('recibos-hoja-ruta.index');
        Route::view(
            'ingresos/conceptos',
            'ingresos.conceptos.index'
        )
            ->middleware('can:administrar-conceptos-ingreso')
            ->name('ingresos.conceptos.index');
        Route::view(
            'ingresos/tarifas-conceptos',
            'ingresos.tarifas-conceptos.index'
        )
            ->middleware('can:administrar-conceptos-ingreso')
            ->name('ingresos.tarifas-conceptos.index');
        Route::view(
            'ingresos/otros',
            'ingresos.otros.index'
        )
            ->middleware('can:cobrar-otros-ingresos')
            ->name('ingresos.otros.index');
        Route::view(
            'ingresos/otros/historial',
            'ingresos.otros.historial'
        )
            ->middleware('can:cobrar-otros-ingresos')
            ->name('ingresos.otros.historial');
        Route::view(
            'ingresos/reincorporaciones',
            'ingresos.reincorporaciones.index'
        )
            ->middleware('can:gestionar-padron')
            ->name('ingresos.reincorporaciones.index');
        Route::view(
            'egresos/conceptos',
            'egresos.conceptos.index'
        )
            ->middleware('can:administrar-conceptos-egreso')
            ->name('egresos.conceptos.index');
        Route::view(
            'egresos/registro',
            'egresos.registro.index'
        )
            ->middleware('can:registrar-egresos')
            ->name('egresos.registro.index');

        Route::get(
            'egresos/{egreso}/imprimir',
            [ComprobanteEgresoController::class, 'imprimir']
        )
            ->whereNumber('egreso')
            ->middleware('can:registrar-egresos')
            ->name('egresos.imprimir');
        Route::view(
            'egresos/historial',
            'egresos.historial.index'
        )
            ->middleware('can:registrar-egresos')
            ->name('egresos.historial.index');
        Route::view(
            'informes/economico',
            'informes.economico.index'
        )
            ->middleware('can:ver-informe-economico')
            ->name('informes.economico.index');
        Route::get(
            'informes/economico/imprimir/{anio}/{mes}',
            [InformeEconomicoImprimirController::class, 'imprimir']
        )
            ->whereNumber('anio')
            ->whereNumber('mes')
            ->middleware('can:ver-informe-economico')
            ->name('informes.economico.imprimir');

    });
    Route::middleware(['auth', 'verified'])
        ->get(
            '/comprobantes/hojas-ruta/{recibo}/imprimir',
            ImprimirReciboHojaRutaController::class
        )
        ->whereNumber('recibo')
        ->name('recibos-hoja-ruta.imprimir');
    Route::middleware(['auth', 'verified'])
        ->get(
            '/comprobantes/otros-ingresos/{recibo}/imprimir',
            ImprimirReciboOtroIngresoController::class
        )
        ->whereNumber('recibo')
        ->name('recibos-otros-ingresos.imprimir');
    
/*
|--------------------------------------------------------------------------
| Configuración del usuario y equipos
|--------------------------------------------------------------------------
*/

require __DIR__.'/settings.php';