<?php

namespace App\Livewire\Egresos;

use App\Models\ConceptoEgreso as ConceptoEgresoModel;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ConceptosEgreso extends Component
{
    #[Locked]
    public ?int $editandoId = null;

    public bool $mostrarFormulario = false;

    public string $nombre = '';
    public string $grupoReporte = 'ORDINARIO';
    public string $descripcion = '';

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

    public function editar(int $id): void
    {
        Gate::authorize('administrar-conceptos-egreso');

        $this->mensaje = '';
        $this->errorOperacion = '';
        $this->resetValidation();

        $concepto = ConceptoEgresoModel::query()
            ->where('team_id', $this->teamId())
            ->findOrFail($id);

        if ($concepto->activo) {
            $this->errorOperacion =
                'Primero deshabilite el concepto para modificarlo.';

            return;
        }

        $this->editandoId = $concepto->id;
        $this->nombre = $concepto->nombre;
        $this->grupoReporte = $concepto->grupo_reporte;
        $this->descripcion = $concepto->descripcion ?? '';

        $this->mostrarFormulario = true;
    }

    public function guardar(): void
    {
        Gate::authorize('administrar-conceptos-egreso');

        $this->mensaje = '';
        $this->errorOperacion = '';

        if ($this->editandoId === null) {
            $this->errorOperacion =
                'Primero seleccione un concepto para configurar.';

            return;
        }

        $datos = $this->validate([
            'nombre' => [
                'required',
                'string',
                'min:3',
                'max:150',
            ],
            'grupoReporte' => [
                'required',
                Rule::in(['ORDINARIO', 'OTROS']),
            ],
            'descripcion' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        try {
            DB::transaction(function () use ($datos) {

                $concepto = ConceptoEgresoModel::query()
                    ->where('team_id', $this->teamId())
                    ->lockForUpdate()
                    ->findOrFail($this->editandoId);

                if ($concepto->activo) {
                    throw new DomainException(
                        'El concepto está activo. '
                        . 'Deshabilítelo antes de modificarlo.'
                    );
                }

                $concepto->update([
                    'nombre' => trim($datos['nombre']),
                    'grupo_reporte' => $datos['grupoReporte'],
                    'descripcion' =>
                        trim($datos['descripcion'] ?? '') ?: null,
                ]);

            }, 3);

            $this->cancelar();

            $this->mensaje =
                'La configuración fue guardada correctamente.';

        } catch (DomainException $e) {
            $this->errorOperacion = $e->getMessage();
        }
    }

    public function cambiarActivacion(int $id): void
    {
        Gate::authorize('administrar-conceptos-egreso');

        $this->mensaje = '';
        $this->errorOperacion = '';

        try {
            $activado = DB::transaction(function () use ($id) {

                $concepto = ConceptoEgresoModel::query()
                    ->where('team_id', $this->teamId())
                    ->lockForUpdate()
                    ->findOrFail($id);

                if ($concepto->activo) {
                    $concepto->update(['activo' => false]);

                    return false;
                }

                if (
                    trim($concepto->codigo) === ''
                    || trim($concepto->nombre) === ''
                    || ! in_array(
                        $concepto->grupo_reporte,
                        ['ORDINARIO', 'OTROS'],
                        true
                    )
                ) {
                    throw new DomainException(
                        'El concepto tiene una configuración incompleta.'
                    );
                }

                $concepto->update(['activo' => true]);

                return true;

            }, 3);

            $this->cancelar();

            $this->mensaje = $activado
                ? 'El concepto fue habilitado correctamente.'
                : 'El concepto fue deshabilitado correctamente.';

        } catch (DomainException $e) {
            $this->errorOperacion = $e->getMessage();
        }
    }

    public function cancelar(): void
    {
        Gate::authorize('administrar-conceptos-egreso');

        $this->editandoId = null;
        $this->mostrarFormulario = false;

        $this->nombre = '';
        $this->grupoReporte = 'ORDINARIO';
        $this->descripcion = '';

        $this->resetValidation();
    }

    public function render()
    {
        Gate::authorize('administrar-conceptos-egreso');

        $conceptos = ConceptoEgresoModel::query()
            ->where('team_id', $this->teamId())
            ->orderBy('grupo_reporte')
            ->orderBy('nombre')
            ->get();

        return view('livewire.egresos.conceptos-egreso', [
            'conceptos' => $conceptos,
        ]);
    }
}