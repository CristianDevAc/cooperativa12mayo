<?php

namespace App\Services\Afiliados;

use App\Models\Afiliado;
use App\Models\PeriodoInactividadAfiliado;
use App\Models\User;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Facades\DB;

class GestionInactividadAfiliadoService
{
    /**
     * Inactivar un afiliado.
     *
     * La suspensión de mensualidades comienza
     * el primer día del mes siguiente.
     */
    public function inactivar(
        int $teamId,
        int $afiliadoId,
        int $usuarioId,
        ?string $motivo = null
    ): PeriodoInactividadAfiliado {

        $this->validarUsuario($teamId, $usuarioId);

        return DB::transaction(function () use (
            $teamId,
            $afiliadoId,
            $usuarioId,
            $motivo
        ) {

            $afiliado = Afiliado::query()
                ->where('team_id', $teamId)
                ->lockForUpdate()
                ->findOrFail($afiliadoId);

            if ($afiliado->estado !== 'ACTIVO') {
                throw new DomainException(
                    'Solamente puede inactivarse un afiliado ACTIVO.'
                );
            }

            $suspensionAbierta = $afiliado
                ->periodosInactividad()
                ->whereNull('periodo_hasta')
                ->exists();

            if ($suspensionAbierta) {
                throw new DomainException(
                    'El afiliado ya tiene un período de inactividad abierto.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Inicio efectivo de la suspensión
            |--------------------------------------------------------------------------
            */

            $fecha = CarbonImmutable::now('America/La_Paz');

            $primerMesSuspendido = $fecha
                ->startOfMonth()
                ->addMonthNoOverflow();

            /*
            |--------------------------------------------------------------------------
            | Registrar historial
            |--------------------------------------------------------------------------
            */

            $registro = $afiliado
                ->periodosInactividad()
                ->create([
                    'team_id' => $teamId,
                    'periodo_desde' => $primerMesSuspendido->toDateString(),
                    'periodo_hasta' => null,
                    'motivo' => $motivo,
                    'registrado_por' => $usuarioId,
                    'reactivado_por' => null,
                ]);

            /*
            |--------------------------------------------------------------------------
            | Actualizar estado
            |--------------------------------------------------------------------------
            */

            $afiliado->estado = 'INACTIVO';

            $afiliado->save();

            return $registro;

        }, 3);
    }

    /**
     * Reactivar un afiliado.
     *
     * El mes de reactivación vuelve a generar
     * la mensualidad completa.
     */
    public function reactivar(
        int $teamId,
        int $afiliadoId,
        int $usuarioId
    ): PeriodoInactividadAfiliado {

        $this->validarUsuario($teamId, $usuarioId);

        return DB::transaction(function () use (
            $teamId,
            $afiliadoId,
            $usuarioId
        ) {

            $afiliado = Afiliado::query()
                ->where('team_id', $teamId)
                ->lockForUpdate()
                ->findOrFail($afiliadoId);

            if ($afiliado->estado !== 'INACTIVO') {
                throw new DomainException(
                    'Solamente puede reactivarse un afiliado INACTIVO.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Localizar suspensión vigente
            |--------------------------------------------------------------------------
            */

            $registro = $afiliado
                ->periodosInactividad()
                ->whereNull('periodo_hasta')
                ->lockForUpdate()
                ->first();

            if (! $registro) {
                throw new DomainException(
                    'No existe un período de inactividad abierto.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Determinar primer mes nuevamente exigible
            |--------------------------------------------------------------------------
            */

            $mesActual = CarbonImmutable::now('America/La_Paz')
                ->startOfMonth();

            $primerMesSuspendido = CarbonImmutable::parse(
                $registro->periodo_desde->toDateString(),
                'America/La_Paz'
            );

            /*
             * Si se reactiva antes de que comience la
             * suspensión, no se exonera ningún mes.
             */

            $primerMesExigible = $mesActual->lessThan(
                $primerMesSuspendido
            )
                ? $primerMesSuspendido
                : $mesActual;

            /*
            |--------------------------------------------------------------------------
            | Cerrar historial de inactividad
            |--------------------------------------------------------------------------
            */

            $registro->periodo_hasta =
                $primerMesExigible->toDateString();

            $registro->reactivado_por = $usuarioId;

            $registro->save();

            /*
            |--------------------------------------------------------------------------
            | Reactivar afiliado
            |--------------------------------------------------------------------------
            */

            $afiliado->estado = 'ACTIVO';

            $afiliado->save();

            return $registro;

        }, 3);
    }

    /**
     * Verificar que el usuario pertenece al equipo activo.
     *
     * La autorización por rol administrativo se
     * incorporará al conectar este servicio con Livewire.
     */
    private function validarUsuario(
        int $teamId,
        int $usuarioId
    ): void {

        $usuarioValido = User::query()
            ->whereKey($usuarioId)
            ->where('current_team_id', $teamId)
            ->exists();

        abort_unless($usuarioValido, 403);
    }
}