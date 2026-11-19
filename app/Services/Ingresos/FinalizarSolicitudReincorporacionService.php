<?php

namespace App\Services\Ingresos;

use App\Models\Afiliado;
use App\Models\ReciboOtroIngreso;
use App\Models\SolicitudReincorporacion;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class FinalizarSolicitudReincorporacionService
{
    public function finalizar(
        int $solicitudId
    ): SolicitudReincorporacion {

        Gate::authorize('gestionar-padron');

        $usuario = Auth::user();

        abort_unless(
            $usuario instanceof User
            && $usuario->current_team_id !== null,
            403
        );

        $teamId = (int) $usuario->current_team_id;

        return DB::transaction(function () use (
            $teamId,
            $solicitudId
        ) {

            $solicitud = SolicitudReincorporacion::query()
                ->where('team_id', $teamId)
                ->lockForUpdate()
                ->findOrFail($solicitudId);

            if ($solicitud->estado !== 'COBRADA') {
                throw new DomainException(
                    'Solamente puede finalizar una solicitud '
                    . 'que ya esté COBRADA.'
                );
            }

            if ($solicitud->recibo_otro_ingreso_id === null) {
                throw new DomainException(
                    'La solicitud no tiene un recibo asociado.'
                );
            }

            // Verificar que exista un recibo válido
            // del mismo afiliado y cooperativa.
            $recibo = ReciboOtroIngreso::query()
                ->where('team_id', $teamId)
                ->where('id', $solicitud->recibo_otro_ingreso_id)
                ->where('afiliado_id', $solicitud->afiliado_id)
                ->where('concepto_codigo', 'REINCORPORACION')
                ->where('estado', 'EMITIDO')
                ->first();

            if ($recibo === null) {
                throw new DomainException(
                    'No existe un recibo válido de reincorporación.'
                );
            }

            $afiliado = Afiliado::query()
                ->where('team_id', $teamId)
                ->lockForUpdate()
                ->findOrFail($solicitud->afiliado_id);

            /*
             * La reactivación debe realizarse previamente
             * mediante el procedimiento del padrón.
             *
             * Este servicio NO modifica el estado del afiliado
             * ni altera sus períodos de inactividad.
             */
            if ($afiliado->estado !== 'ACTIVO') {
                throw new DomainException(
                    'El afiliado todavía no está ACTIVO. '
                    . 'Primero complete su reactivación '
                    . 'desde el padrón.'
                );
            }

            $solicitud->estado = 'FINALIZADA';
            $solicitud->cerrada_en = now('America/La_Paz');

            $solicitud->save();

            return $solicitud;

        }, 3);
    }
}