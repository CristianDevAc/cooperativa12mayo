<?php

namespace App\Livewire\Grupos;

use App\Models\Grupo;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;


class Index extends Component
{
    public string $buscar = '';

    public string $codigo = '';
    public string $nombre = '';
    public bool $activo = true;

    public ?int $editandoId = null;
    public bool $mostrarFormulario = false;

    private function teamId(): int
    {
        $usuario = Auth::user();

        if (! $usuario instanceof User || ! $usuario->current_team_id) {
            abort(403, 'No existe un equipo activo.');
        }

        return (int) $usuario->current_team_id;
    }

    public function nuevo(): void
    {
        Gate::authorize('gestionar-padron');

        $this->limpiarFormulario();
        $this->mostrarFormulario = true;
    }

    public function editar(int $id): void
    {
        Gate::authorize('gestionar-padron');
        $grupo = Grupo::query()
            ->where('team_id', $this->teamId())
            ->findOrFail($id);

        $this->editandoId = $grupo->id;
        $this->codigo = $grupo->codigo;
        $this->nombre = $grupo->nombre;
        $this->activo = $grupo->activo;

        $this->mostrarFormulario = true;
        $this->resetValidation();
    }

    public function guardar(): void
    {
        Gate::authorize('gestionar-padron');
        $teamId = $this->teamId();

        $datos = $this->validate([
            'codigo' => [
                'required',
                'string',
                'max:20',
                Rule::unique('grupos', 'codigo')
                    ->where('team_id', $teamId)
                    ->ignore($this->editandoId),
            ],
            'nombre' => [
                'required',
                'string',
                'max:100',
            ],
            'activo' => [
                'required',
                'boolean',
            ],
        ]);

        $datos['team_id'] = $teamId;
        $datos['codigo'] = strtoupper(trim($datos['codigo']));
        $datos['nombre'] = trim($datos['nombre']);

        if ($this->editandoId !== null) {
            $grupo = Grupo::query()
                ->where('team_id', $teamId)
                ->findOrFail($this->editandoId);

            $grupo->update($datos);

            session()->flash(
                'mensaje',
                'El grupo fue actualizado correctamente.'
            );
        } else {
            Grupo::create($datos);

            session()->flash(
                'mensaje',
                'El grupo fue registrado correctamente.'
            );
        }

        $this->limpiarFormulario();
    }

    public function cancelar(): void
    {
        $this->limpiarFormulario();
    }

    public function cambiarEstado(int $id): void
    {
        Gate::authorize('gestionar-padron');

        $grupo = Grupo::query()
            ->where('team_id', $this->teamId())
            ->findOrFail($id);

        $grupo->update([
            'activo' => ! $grupo->activo,
        ]);

        session()->flash(
            'mensaje',
            'El estado del grupo fue actualizado.'
        );
    }

    private function limpiarFormulario(): void
    {
        $this->codigo = '';
        $this->nombre = '';
        $this->activo = true;
        $this->editandoId = null;
        $this->mostrarFormulario = false;

        $this->resetValidation();
    }

    public function render()
    {
        $buscar = trim($this->buscar);

        $grupos = Grupo::query()
            ->where('team_id', $this->teamId())
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($subquery) use ($buscar) {
                    $subquery
                        ->where('codigo', 'ilike', "%{$buscar}%")
                        ->orWhere('nombre', 'ilike', "%{$buscar}%");
                });
            })
            ->orderBy('codigo')
            ->get();

        return view('livewire.grupos.index', [
            'grupos' => $grupos,
        ]);
    }
}