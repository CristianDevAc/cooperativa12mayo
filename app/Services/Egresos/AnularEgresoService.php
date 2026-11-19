<?php

namespace App\Services\Egresos;

use App\Models\Egreso;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AnularEgresoService
{
    public function anular(
        int $egresoId,
        string $motivo
    ): Egreso {

        Gate::authorize('anular-egresos');

        $usuario = Auth::user();

        abort_unless(
            $usuario instanceof User
            && $usuario->current_team_id !== null,
            403
        );

        $teamId = (int) $usuario->current_team_id;

        $motivo = trim($motivo);

        if (
            mb_strlen($motivo) < 10
            || mb_strlen($motivo) > 500
        ) {
            throw new DomainException(
                'El motivo de anulación debe tener '
                . 'entre 10 y 500 caracteres.'
            );
        }

        return DB::transaction(function () use (
            $egresoId,
            $motivo,
            $usuario,
            $teamId
        ) {

            /*
             * Buscar exclusivamente en la cooperativa
             * activa y bloquear el registro.
             */
            $egreso = Egreso::query()
                ->where('team_id', $teamId)
                ->whereKey($egresoId)
                ->lockForUpdate()
                ->firstOrFail();

            /*
             * No permitir una segunda anulación.
             */
            if ($egreso->estado !== 'PAGADO') {
                throw new DomainException(
                    'Este comprobante no se encuentra '
                    . 'en estado PAGADO y no puede anularse.'
                );
            }

            /*
             * Conservar los datos originales del pago.
             * Únicamente modificar el estado y registrar
             * la información de auditoría.
             */
            $egreso->estado = 'ANULADO';

            $egreso->motivo_anulacion = $motivo;

            $egreso->anulado_por = $usuario->id;

            $egreso->anulado_en = now('America/La_Paz');

            $egreso->save();

            return $egreso;

        }, 3);
    }
}