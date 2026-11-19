<?php

namespace App\Livewire\Egresos;

use App\Models\ConceptoEgreso;
use App\Models\Egreso;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use App\Services\Egresos\AnularEgresoService;
use DomainException;
use Livewire\Attributes\Locked;

class HistorialEgresos extends Component
{
    use WithPagination;

    #[Locked]
    public ?int $egresoAnularId = null;

    #[Locked]
    public string $referenciaAnular = '';

    public string $motivoAnulacion = '';

    public string $mensajeAnulacion = '';

    public string $errorAnulacion = '';
    public string $busqueda = '';
    public string $numeroComprobante = '';
    public string $conceptoEgresoId = '';
    public string $estado = '';
    public string $fechaDesde = '';
    public string $fechaHasta = '';

    private function teamId(): int
    {
        $usuario = Auth::user();

        abort_unless(
            $usuario instanceof User
            && $usuario->current_team_id !== null,
            403
        );

        return (int) $usuario->current_team_id;
    }

    public function updated(string $propiedad): void
    {
        if (in_array($propiedad, [
            'busqueda',
            'numeroComprobante',
            'conceptoEgresoId',
            'estado',
            'fechaDesde',
            'fechaHasta',
        ], true)) {
            $this->resetPage();
        }
    }

    public function limpiarFiltros(): void
    {
        Gate::authorize('registrar-egresos');

        $this->reset([
            'busqueda',
            'numeroComprobante',
            'conceptoEgresoId',
            'estado',
            'fechaDesde',
            'fechaHasta',
        ]);

        $this->resetPage();
    }

    private function fechaValida(string $fecha): bool
    {
        if (! preg_match(
            '/^\d{4}-\d{2}-\d{2}$/D',
            $fecha
        )) {
            return false;
        }

        [$anio, $mes, $dia] = array_map(
            'intval',
            explode('-', $fecha)
        );

        return checkdate($mes, $dia, $anio);
    }

    public function solicitarAnulacion(int $id): void
    {
        Gate::authorize('anular-egresos');

        $this->mensajeAnulacion = '';
        $this->errorAnulacion = '';
        $this->resetValidation();

        $egreso = Egreso::query()
            ->where('team_id', $this->teamId())
            ->findOrFail($id);

        if ($egreso->estado !== 'PAGADO') {
            $this->errorAnulacion =
                'Solamente pueden anularse egresos PAGADOS.';

            return;
        }

        $this->egresoAnularId = $egreso->id;

        $this->referenciaAnular = sprintf(
            'EGR-%06d',
            $egreso->numero_comprobante
        );

        $this->motivoAnulacion = '';
    }

    public function cancelarAnulacion(): void
    {
        Gate::authorize('anular-egresos');

        $this->egresoAnularId = null;
        $this->referenciaAnular = '';
        $this->motivoAnulacion = '';
        $this->errorAnulacion = '';

        $this->resetValidation();
    }

    public function confirmarAnulacion(
        AnularEgresoService $servicio
    ): void {

        Gate::authorize('anular-egresos');

        $this->errorAnulacion = '';
        $this->mensajeAnulacion = '';

        if ($this->egresoAnularId === null) {
            $this->errorAnulacion =
                'Seleccione un comprobante para anular.';

            return;
        }

        $this->validate([
            'motivoAnulacion' => [
                'required',
                'string',
                'min:10',
                'max:500',
            ],
        ]);

        try {

            $egreso = $servicio->anular(
                $this->egresoAnularId,
                $this->motivoAnulacion
            );

            $numero = sprintf(
                'EGR-%06d',
                $egreso->numero_comprobante
            );

            $this->cancelarAnulacion();

            $this->mensajeAnulacion =
                "El comprobante {$numero} fue anulado correctamente.";

            $this->resetPage();

        } catch (DomainException $e) {

            $this->errorAnulacion = $e->getMessage();

        }
    }
    public function render()
    {
        Gate::authorize('registrar-egresos');

        $teamId = $this->teamId();

        /*
         * Los conceptos deshabilitados también aparecen
         * porque pueden tener pagos históricos.
         */
        $conceptos = ConceptoEgreso::query()
            ->where('team_id', $teamId)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'activo']);

        /*
         * Todas las consultas están limitadas
         * a la cooperativa del usuario.
         */
        $consulta = Egreso::query()
            ->where('team_id', $teamId);

        /*
         * Buscar beneficiario o detalle.
         */
        $texto = mb_substr(
            trim($this->busqueda),
            0,
            150
        );

        if ($texto !== '') {
            $consulta->where(function ($query) use ($texto) {

                $query
                    ->where(
                        'beneficiario',
                        'ILIKE',
                        '%' . $texto . '%'
                    )
                    ->orWhere(
                        'detalle',
                        'ILIKE',
                        '%' . $texto . '%'
                    );

            });
        }

        /*
         * Buscar por número correlativo.
         * Acepta: 1, 000001, EGR-000001.
         */
        $numero = strtoupper(
            trim($this->numeroComprobante)
        );

        if ($numero !== '') {

            $numero = preg_replace('/^EGR-/', '', $numero);

            if (
                preg_match('/^\d{1,18}$/D', $numero)
                && (int) $numero > 0
            ) {
                $consulta->where(
                    'numero_comprobante',
                    (int) $numero
                );
            } else {
                $consulta->whereRaw('1 = 0');
            }
        }

        /*
         * Filtrar por concepto.
         */
        if ($this->conceptoEgresoId !== '') {

            if (
                ctype_digit($this->conceptoEgresoId)
                && (int) $this->conceptoEgresoId > 0
            ) {
                $consulta->where(
                    'concepto_egreso_id',
                    (int) $this->conceptoEgresoId
                );
            } else {
                $consulta->whereRaw('1 = 0');
            }
        }

        /*
         * Filtrar por estado.
         */
        if ($this->estado !== '') {

            if (in_array(
                $this->estado,
                ['PAGADO', 'ANULADO'],
                true
            )) {
                $consulta->where('estado', $this->estado);
            } else {
                $consulta->whereRaw('1 = 0');
            }
        }

        /*
         * Fechas interpretadas en horario boliviano.
         * El límite superior es exclusivo:
         * comienzo del día siguiente.
         */
        $errorFechas = null;

        if (
            ($this->fechaDesde !== ''
                && ! $this->fechaValida($this->fechaDesde))
            ||
            ($this->fechaHasta !== ''
                && ! $this->fechaValida($this->fechaHasta))
        ) {
            $errorFechas = 'Ingrese fechas válidas.';
        } elseif (
            $this->fechaDesde !== ''
            && $this->fechaHasta !== ''
            && $this->fechaDesde > $this->fechaHasta
        ) {
            $errorFechas =
                'La fecha inicial no puede ser posterior a la fecha final.';
        }

        if ($errorFechas !== null) {

            $consulta->whereRaw('1 = 0');

        } else {

            if ($this->fechaDesde !== '') {

                $desde = CarbonImmutable::parse(
                    $this->fechaDesde . ' 00:00:00',
                    'America/La_Paz'
                )->utc();

                $consulta->where(
                    'fecha_pago',
                    '>=',
                    $desde
                );
            }

            if ($this->fechaHasta !== '') {

                $hastaExclusiva = CarbonImmutable::parse(
                    $this->fechaHasta . ' 00:00:00',
                    'America/La_Paz'
                )->addDay()->utc();

                $consulta->where(
                    'fecha_pago',
                    '<',
                    $hastaExclusiva
                );
            }
        }

        /*
         * Resumen de todos los registros filtrados,
         * no solamente de la página visible.
         */
        $resumen = (clone $consulta)
            ->selectRaw("
                COUNT(*) AS cantidad,
                COALESCE(
                    SUM(
                        CASE
                            WHEN estado = 'PAGADO'
                            THEN importe
                            ELSE 0
                        END
                    ),
                    0
                ) AS total_pagado
            ")
            ->first();

        /*
         * Historial paginado.
         */
        $egresos = (clone $consulta)
            ->with('registradoPor:id,name')
            ->orderByDesc('fecha_pago')
            ->orderByDesc('id')
            ->paginate(15);

        return view('livewire.egresos.historial-egresos', [
            'conceptos' => $conceptos,
            'egresos' => $egresos,
            'cantidad' => (int) ($resumen?->cantidad ?? 0),
            'totalPagado' => (string) (
                $resumen?->total_pagado ?? '0'
            ),
            'errorFechas' => $errorFechas,
        ]);
    }
}