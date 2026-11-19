<?php

namespace App\Http\Controllers\Ingresos;

use App\Http\Controllers\Controller;
use App\Models\ReciboOtroIngreso;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ImprimirReciboOtroIngresoController extends Controller
{
    public function __invoke(Request $request, int $recibo): View
    {
        $usuario = $request->user();

        abort_unless(
            $usuario instanceof User
            && $usuario->current_team_id !== null,
            403
        );

        Gate::authorize('cobrar-otros-ingresos');

        $registro = ReciboOtroIngreso::query()
            ->where('team_id', $usuario->current_team_id)
            ->with([
                'team',
                'afiliado',
                'registradoPor',
            ])
            ->findOrFail($recibo);

        return view(
            'ingresos.otros.imprimir',
            ['recibo' => $registro]
        );
    }
}