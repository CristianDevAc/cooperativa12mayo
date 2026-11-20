<?php

namespace App\Services\Dashboard;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

class ResumenCooperativa
{
    /**
     * Obtiene datos exclusivamente de la cooperativa activa.
     * Devuelve null si el esquema no permite un conteo seguro por team_id.
     */
    public function obtener(User $usuario): array
    {
        $teamId = (int) $usuario->current_team_id;

        abort_unless($teamId > 0, 403);

        abort_unless(
            DB::table('team_members')
                ->where('team_id', $teamId)
                ->where('user_id', $usuario->id)
                ->exists(),
            403
        );

        $totalAfiliados = $this->contarPorEquipo('afiliados', $teamId);
        $activos = null;

        if ($totalAfiliados !== null && Schema::hasColumn('afiliados', 'estado')) {
            $activos = (int) DB::table('afiliados')
                ->where('team_id', $teamId)
                ->whereRaw('UPPER(TRIM(estado)) = ?', ['ACTIVO'])
                ->count();
        }

        $vehiculos = $this->contarPorEquipo('vehiculos', $teamId);

        // Algunos diseños vinculan el vehículo a la cooperativa por su historial.
        if ($vehiculos === null
            && Schema::hasTable('historial_vehiculos')
            && Schema::hasColumn('historial_vehiculos', 'vehiculo_id')
            && Schema::hasColumn('historial_vehiculos', 'afiliado_id')
            && Schema::hasTable('afiliados')
            && Schema::hasColumn('afiliados', 'team_id')) {
            $vehiculos = (int) DB::table('historial_vehiculos as hv')
                ->join('afiliados as a', 'a.id', '=', 'hv.afiliado_id')
                ->where('a.team_id', $teamId)
                ->distinct()
                ->count('hv.vehiculo_id');
        }

        $puedeVerRecibos = Gate::allows('consultar-recibos-hoja-ruta');
        $puedeVerOtrosIngresos = Gate::allows('cobrar-otros-ingresos');
        $puedeVerEgresos = Gate::allows('registrar-egresos');

        $movimientos = [];

        if ($puedeVerRecibos) {
            $movimientos = array_merge($movimientos, $this->ultimos(
                'recibos_hoja_ruta', $teamId, 'Recibo de hoja de ruta',
                'recibos-hoja-ruta.index'
            ));
        }
        if ($puedeVerOtrosIngresos) {
            $movimientos = array_merge($movimientos, $this->ultimos(
                'recibos_otros_ingresos', $teamId, 'Recibo de otro ingreso',
                'ingresos.otros.historial'
            ));
        }
        if ($puedeVerEgresos) {
            $movimientos = array_merge($movimientos, $this->ultimos(
                'egresos', $teamId, 'Egreso registrado',
                'egresos.historial.index'
            ));
        }

        usort($movimientos, fn (array $a, array $b) => strcmp(
            (string) $b['fecha'], (string) $a['fecha']
        ));

        return [
            'afiliados' => $totalAfiliados,
            'activos' => $activos,
            'vehiculos' => $vehiculos,
            'grupos' => $this->contarPorEquipo('grupos', $teamId),
            'usuarios' => (int) DB::table('team_members')
                ->where('team_id', $teamId)
                ->count(),
            'recibos_ruta' => $puedeVerRecibos
                ? $this->contarPorEquipo('recibos_hoja_ruta', $teamId) : null,
            'otros_ingresos' => $puedeVerOtrosIngresos
                ? $this->contarPorEquipo('recibos_otros_ingresos', $teamId) : null,
            'egresos' => $puedeVerEgresos
                ? $this->contarPorEquipo('egresos', $teamId) : null,
            'puede_ver_recibos' => $puedeVerRecibos,
            'puede_ver_otros_ingresos' => $puedeVerOtrosIngresos,
            'puede_ver_egresos' => $puedeVerEgresos,
            'movimientos' => array_slice($movimientos, 0, 6),
        ];
    }

    private function contarPorEquipo(string $tabla, int $teamId): ?int
    {
        if (! Schema::hasTable($tabla) || ! Schema::hasColumn($tabla, 'team_id')) {
            return null;
        }

        return (int) DB::table($tabla)->where('team_id', $teamId)->count();
    }

    private function ultimos(
        string $tabla,
        int $teamId,
        string $nombre,
        string $ruta
    ): array {
        if (! Schema::hasTable($tabla)
            || ! Schema::hasColumn($tabla, 'team_id')
            || ! Schema::hasColumn($tabla, 'created_at')) {
            return [];
        }

        return DB::table($tabla)
            ->where('team_id', $teamId)
            ->whereNotNull('created_at')
            ->latest('created_at')
            ->limit(5)
            ->get(['id', 'created_at'])
            ->map(fn ($fila) => [
                'tipo' => $nombre,
                'numero' => $fila->id,
                'fecha' => $fila->created_at,
                'ruta' => $ruta,
            ])->all();
    }
}
