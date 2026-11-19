<?php

namespace App\Livewire\Ingresos;

use App\Models\ReciboHojaRuta;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class HistorialRecibos extends Component
{
    use WithPagination;

    public string $buscar = '';
    public string $estado = '';
    public string $desde = '';
    public string $hasta = '';

    public string $buscarAplicado = '';
    public string $estadoAplicado = '';
    public ?string $desdeAplicado = null;
    public ?string $hastaAplicado = null;

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
        Gate::authorize('consultar-recibos-hoja-ruta');

        $this->validate([
            'buscar' => ['nullable', 'string', 'max:120'],
            'estado' => [
                'nullable',
                Rule::in(['EMITIDO', 'ANULADO']),
            ],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d'],
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
        $this->estadoAplicado = $this->estado;
        $this->desdeAplicado = $this->desde ?: null;
        $this->hastaAplicado = $this->hasta ?: null;

        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->reset([
            'buscar',
            'estado',
            'desde',
            'hasta',
            'buscarAplicado',
            'estadoAplicado',
            'desdeAplicado',
            'hastaAplicado',
        ]);

        $this->resetValidation();
        $this->resetPage();
    }

    public function render()
    {
        Gate::authorize('consultar-recibos-hoja-ruta');

        $consulta = ReciboHojaRuta::query()
            ->where('team_id', $this->teamId())
            ->with([
                'afiliado',
                'registradoPor',
            ])
            ->withCount('detalles');

        if ($this->buscarAplicado !== '') {

            $termino = $this->buscarAplicado;

            $consulta->where(function (Builder $query) use ($termino) {

                $query->whereHas(
                    'afiliado',
                    function (Builder $afiliado) use ($termino) {
                        $afiliado->where(function (Builder $datos) use ($termino) {
                            $datos
                                ->where('nombre_completo', 'ILIKE', "%{$termino}%")
                                ->orWhere('ci', 'ILIKE', "%{$termino}%")
                                ->orWhere('numero_socio', 'ILIKE', "%{$termino}%");
                        });
                    }
                );

                if (ctype_digit($termino) && strlen($termino) <= 18) {
                    $query->orWhere(
                        'numero_recibo',
                        (int) $termino
                    );
                }
            });
        }

        if ($this->estadoAplicado !== '') {
            $consulta->where('estado', $this->estadoAplicado);
        }

        if ($this->desdeAplicado !== null) {

            $inicio = CarbonImmutable::parse(
                $this->desdeAplicado . ' 00:00:00',
                'America/La_Paz'
            )->utc();

            $consulta->where('fecha_cobro', '>=', $inicio);
        }

        if ($this->hastaAplicado !== null) {

            $fin = CarbonImmutable::parse(
                $this->hastaAplicado . ' 00:00:00',
                'America/La_Paz'
            )->addDay()->utc();

            $consulta->where('fecha_cobro', '<', $fin);
        }

        $recibos = $consulta
            ->orderByDesc('fecha_cobro')
            ->orderByDesc('id')
            ->paginate(15);

        return view('livewire.ingresos.historial-recibos', [
            'recibos' => $recibos,
        ]);
    }
}