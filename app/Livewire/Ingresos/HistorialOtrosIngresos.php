<?php

namespace App\Livewire\Ingresos;

use App\Models\ConceptoIngreso;
use App\Models\ReciboOtroIngreso;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Locked;

class HistorialOtrosIngresos extends Component
{
    use WithPagination;

    public string $buscar = '';
    public string $conceptoId = '';
    public string $estado = '';
    public string $desde = '';
    public string $hasta = '';

    #[Locked]
    public string $buscarAplicado = '';
    #[Locked]
    public string $conceptoAplicado = '';
    #[Locked]
    public string $estadoAplicado = '';
    #[Locked]
    public string $desdeAplicado = '';
    #[Locked]
    public string $hastaAplicado = '';

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

    public function filtrar(): void
    {
        Gate::authorize('cobrar-otros-ingresos');

        $teamId = $this->teamId();

        $this->validate([
            'buscar' => ['nullable', 'string', 'max:120'],

            'conceptoId' => [
                'nullable',
                'integer',
                Rule::exists('conceptos_ingreso', 'id')
                    ->where('team_id', $teamId),
            ],

            'estado' => [
                'nullable',
                Rule::in(['EMITIDO', 'ANULADO']),
            ],

            'desde' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'hasta' => [
                'nullable',
                'date_format:Y-m-d',
            ],
        ]);

        if (
            $this->desde !== ''
            && $this->hasta !== ''
            && $this->desde > $this->hasta
        ) {
            $this->addError(
                'hasta',
                'La fecha final no puede ser anterior a la inicial.'
            );

            return;
        }

        $this->buscarAplicado = trim($this->buscar);
        $this->conceptoAplicado = $this->conceptoId;
        $this->estadoAplicado = $this->estado;
        $this->desdeAplicado = $this->desde;
        $this->hastaAplicado = $this->hasta;

        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->reset([
            'buscar',
            'conceptoId',
            'estado',
            'desde',
            'hasta',
            'buscarAplicado',
            'conceptoAplicado',
            'estadoAplicado',
            'desdeAplicado',
            'hastaAplicado',
        ]);

        $this->resetValidation();
        $this->resetPage();
    }

    public function render()
    {
        Gate::authorize('cobrar-otros-ingresos');

        $teamId = $this->teamId();

        $conceptos = ConceptoIngreso::query()
            ->where('team_id', $teamId)
            ->orderBy('nombre')
            ->get();

        $consulta = ReciboOtroIngreso::query()
            ->where('team_id', $teamId);

        if ($this->conceptoAplicado !== '') {
            $consulta->where(
                'concepto_ingreso_id',
                (int) $this->conceptoAplicado
            );
        }

        if ($this->estadoAplicado !== '') {
            $consulta->where('estado', $this->estadoAplicado);
        }

        if ($this->desdeAplicado !== '') {
            $inicio = CarbonImmutable::parse(
                $this->desdeAplicado . ' 00:00:00',
                'America/La_Paz'
            )->utc();

            $consulta->where('fecha_cobro', '>=', $inicio);
        }

        if ($this->hastaAplicado !== '') {
            $fin = CarbonImmutable::parse(
                $this->hastaAplicado . ' 00:00:00',
                'America/La_Paz'
            )->addDay()->utc();

            $consulta->where('fecha_cobro', '<', $fin);
        }

        $termino = trim($this->buscarAplicado);

        if ($termino !== '') {
            $consulta->where(function (Builder $query) use ($termino) {

                $query
                    ->where('detalle', 'ILIKE', "%{$termino}%")
                    ->orWhere('concepto_nombre', 'ILIKE', "%{$termino}%")
                    ->orWhereHas(
                        'afiliado',
                        function (Builder $afiliado) use ($termino) {
                            $afiliado->where(function (Builder $datos) use ($termino) {
                                $datos
                                    ->where(
                                        'nombre_completo',
                                        'ILIKE',
                                        "%{$termino}%"
                                    )
                                    ->orWhere(
                                        'ci',
                                        'ILIKE',
                                        "%{$termino}%"
                                    )
                                    ->orWhere(
                                        'numero_socio',
                                        'ILIKE',
                                        "%{$termino}%"
                                    );
                            });
                        }
                    );

                // Buscar por número: 1, 000001, etc.
                if (
                    ctype_digit($termino)
                    && strlen($termino) <= 18
                ) {
                    $query->orWhere(
                        'numero_recibo',
                        (int) $termino
                    );
                }

                // Buscar por código completo:
                // CERTIFICACION-000001.
                if (
                    preg_match(
                        '/^([A-Z_]+)-(\d{1,18})$/i',
                        $termino,
                        $coincidencia
                    )
                ) {
                    $query->orWhere(function (Builder $sub) use ($coincidencia) {
                        $sub
                            ->where(
                                'concepto_codigo',
                                strtoupper($coincidencia[1])
                            )
                            ->where(
                                'numero_recibo',
                                (int) $coincidencia[2]
                            );
                    });
                }
            });
        }

        // Solo los recibos emitidos forman parte
        // de la recaudación vigente.
        $resumen = (clone $consulta)
            ->where('estado', 'EMITIDO')
            ->selectRaw(
                'COUNT(*) AS cantidad,
                 COALESCE(SUM(importe), 0) AS total'
            )
            ->first();

        $porConcepto = (clone $consulta)
            ->where('estado', 'EMITIDO')
            ->selectRaw(
                'concepto_codigo,
                 concepto_nombre,
                 COUNT(*) AS cantidad,
                 COALESCE(SUM(importe), 0) AS total'
            )
            ->groupBy(
                'concepto_codigo',
                'concepto_nombre'
            )
            ->orderBy('concepto_nombre')
            ->get();

        $recibos = (clone $consulta)
            ->with([
                'afiliado',
                'registradoPor',
            ])
            ->orderByDesc('fecha_cobro')
            ->orderByDesc('id')
            ->paginate(15);

        return view('livewire.ingresos.historial-otros-ingresos', [
            'conceptos' => $conceptos,
            'resumen' => $resumen,
            'porConcepto' => $porConcepto,
            'recibos' => $recibos,
        ]);
    }
}