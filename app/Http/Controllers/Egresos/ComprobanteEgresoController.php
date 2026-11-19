<?php

namespace App\Http\Controllers\Egresos;

use App\Http\Controllers\Controller;
use App\Models\Egreso;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ComprobanteEgresoController extends Controller
{
    public function imprimir(string $current_team, int $egreso): View
    {
        Gate::authorize('registrar-egresos');

        $usuario = Auth::user();

        abort_unless(
            $usuario instanceof User
            && $usuario->current_team_id !== null,
            403
        );

        $registro = Egreso::query()
            ->with([
                'registradoPor:id,name',
                'anuladoPor:id,name',
            ])
            ->where('team_id', (int) $usuario->current_team_id)
            ->findOrFail($egreso);

        return view(
            'egresos.comprobantes.imprimir',
            [
                'registro' => $registro,
            ]
        );
    }
}