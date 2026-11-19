<?php

namespace App\Livewire\Ingresos;

use App\Models\Afiliado;
use App\Models\SolicitudReincorporacion;
use App\Models\User;
use App\Services\Ingresos\RegistrarSolicitudReincorporacionService;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use App\Services\Ingresos\FinalizarSolicitudReincorporacionService;

class SolicitudesReincorporacion extends Component
{
    public string $buscarAfiliado = '';
    public string $observaciones = '';

    #[Locked]
    public ?int $afiliadoId = null;

    public string $mensaje = '';
    public string $errorOperacion = '';

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

    public function seleccionarAfiliado(int $id): void
    {
        Gate::authorize('gestionar-padron');

        $afiliado = Afiliado::query()
            ->where('team_id', $this->teamId())
            ->where('estado', 'INACTIVO')
            ->findOrFail($id);

        $this->afiliadoId = $afiliado->id;
        $this->buscarAfiliado = '';
        $this->mensaje = '';
        $this->errorOperacion = '';
        $this->resetValidation();
    }

    public function quitarAfiliado(): void
    {
        Gate::authorize('gestionar-padron');

        $this->afiliadoId = null;
        $this->mensaje = '';
        $this->errorOperacion = '';
    }

    public function registrar(
        RegistrarSolicitudReincorporacionService $servicio
    ): void {
        Gate::authorize('gestionar-padron');

        $this->mensaje = '';
        $this->errorOperacion = '';

        $this->validate([
            'observaciones' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        if ($this->afiliadoId === null) {
            $this->addError(
                'afiliadoId',
                'Seleccione un afiliado inactivo.'
            );

            return;
        }

        try {
            $solicitud = $servicio->registrar(
                $this->afiliadoId,
                $this->observaciones
            );

            $this->mensaje =
                'Solicitud N.º ' . $solicitud->id
                . ' registrada correctamente. '
                . 'Estado: PENDIENTE. '
                . 'Todavía no se realizó ningún cobro.';

            $this->afiliadoId = null;
            $this->buscarAfiliado = '';
            $this->observaciones = '';
            $this->resetValidation();

        } catch (DomainException $e) {
            $this->errorOperacion = $e->getMessage();
        }
    }

    public function finalizar(
        int $id,
        FinalizarSolicitudReincorporacionService $servicio
    ): void {

        Gate::authorize('gestionar-padron');

        $this->mensaje = '';
        $this->errorOperacion = '';

        try {

            $solicitud = $servicio->finalizar($id);

            $this->mensaje =
                'La solicitud N.º '
                . str_pad(
                    (string) $solicitud->id,
                    6,
                    '0',
                    STR_PAD_LEFT
                )
                . ' fue finalizada correctamente.';

        } catch (DomainException $e) {

            $this->errorOperacion = $e->getMessage();
        }
    }
    public function render()
    {
        Gate::authorize('gestionar-padron');

        $teamId = $this->teamId();

        $afiliadoSeleccionado = null;

        if ($this->afiliadoId !== null) {
            $afiliadoSeleccionado = Afiliado::query()
                ->where('team_id', $teamId)
                ->find($this->afiliadoId);
        }

        $afiliados = collect();

        $termino = trim($this->buscarAfiliado);

        if (mb_strlen($termino) >= 2) {
            $afiliados = Afiliado::query()
                ->where('team_id', $teamId)
                ->where('estado', 'INACTIVO')
                ->where(function (Builder $query) use ($termino) {
                    $query
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
                })
                ->orderBy('nombre_completo')
                ->limit(10)
                ->get();
        }

        $solicitudes = SolicitudReincorporacion::query()
            ->where('team_id', $teamId)
            ->with([
                'afiliado',
                'registradoPor',
                'recibo',
            ])
            ->orderByDesc('id')
            ->limit(25)
            ->get();

        return view(
            'livewire.ingresos.solicitudes-reincorporacion',
            [
                'afiliadoSeleccionado' => $afiliadoSeleccionado,
                'afiliados' => $afiliados,
                'solicitudes' => $solicitudes,
            ]
        );
    }
}