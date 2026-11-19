<?php

namespace App\Services\Ingresos;

use App\Models\Afiliado;
use App\Models\SolicitudReincorporacion;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RegistrarSolicitudReincorporacionService
{
    public function registrar(
        int $afiliadoId,
        ?string $observaciones = null
    ): SolicitudReincorporacion {

        Gate::authorize('gestionar-padron');

        $usuario = Auth::user();

        abort_unless(
            $usuario instanceof User
            && $usuario->current_team_id !== null,
            403
        );

        $teamId = (int) $usuario->current_team_id;

        $observaciones = trim((string) $observaciones);

        if (mb_strlen($observaciones) > 500) {
            throw new DomainException(
                'Las observaciones no pueden superar 500 caracteres.'
            );
        }

        return DB::transaction(function () use (
            $teamId,
            $usuario,
            $afiliadoId,
            $observaciones
        ) {

            $afiliado = Afiliado::query()
                ->where('team_id', $teamId)
                ->lockForUpdate()
                ->findOrFail($afiliadoId);

            /*
             * Primera regla:
             * solamente afiliados INACTIVOS.
             *
             * No se modifica automáticamente su estado.
             */
            if ($afiliado->estado !== 'INACTIVO') {
                throw new DomainException(
                    'El afiliado debe encontrarse INACTIVO '
                    . 'para solicitar su reincorporación.'
                );
            }

            $existe = SolicitudReincorporacion::query()
                ->where('team_id', $teamId)
                ->where('afiliado_id', $afiliado->id)
                ->whereIn('estado', [
                    'PENDIENTE',
                    'COBRADA',
                ])
                ->exists();

            if ($existe) {
                throw new DomainException(
                    'Este afiliado ya tiene una solicitud '
                    . 'de reincorporación abierta.'
                );
            }

            return SolicitudReincorporacion::create([
                'team_id' => $teamId,
                'afiliado_id' => $afiliado->id,
                'fecha_solicitud' => now('America/La_Paz'),
                'observaciones' => $observaciones ?: null,
                'registrado_por' => $usuario->id,
            ]);

        }, 3);
    }
}