<?php

namespace App\Livewire\Ingresos;

use App\Models\ConceptoIngreso;
use App\Models\TarifaConceptoIngreso;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class TarifasConceptos extends Component
{
    public string $conceptoId = '';
    public string $vigencia = '';
    public string $importe = '';
    public string $observaciones = '';
    public string $mensaje = '';

    public function mount(): void
    {
        $this->vigencia = now('America/La_Paz')->format('Y-m');
    }

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

    public function guardar(): void
    {
        Gate::authorize('administrar-conceptos-ingreso');

        $this->mensaje = '';

        $teamId = $this->teamId();

        $datos = $this->validate([
            'conceptoId' => [
                'required',
                'integer',
                Rule::exists('conceptos_ingreso', 'id')
                    ->where('team_id', $teamId),
            ],
            'vigencia' => [
                'required',
                'date_format:Y-m',
            ],
            'importe' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9999999999.99',
                'decimal:0,2',
            ],
            'observaciones' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $concepto = ConceptoIngreso::query()
            ->where('team_id', $teamId)
            ->findOrFail((int) $datos['conceptoId']);

        // Los conceptos variables no utilizan tarifas fijas.
        if ($concepto->forma_importe !== 'FIJO') {
            $this->addError(
                'conceptoId',
                'Este concepto no está configurado con importe fijo.'
            );

            return;
        }

        $fechaVigencia = $datos['vigencia'] . '-01';

        $existe = TarifaConceptoIngreso::query()
            ->where('team_id', $teamId)
            ->where('concepto_ingreso_id', $concepto->id)
            ->whereDate('vigente_desde', $fechaVigencia)
            ->exists();

        if ($existe) {
            $this->addError(
                'vigencia',
                'Ya existe una tarifa para este concepto y mes.'
            );

            return;
        }

        TarifaConceptoIngreso::create([
            'team_id' => $teamId,
            'concepto_ingreso_id' => $concepto->id,
            'vigente_desde' => $fechaVigencia,
            'importe' => $datos['importe'],
            'observaciones' => trim($this->observaciones) ?: null,
            'creado_por' => Auth::id(),
        ]);

        $this->conceptoId = '';
        $this->importe = '';
        $this->observaciones = '';

        $this->resetValidation();

        $this->mensaje =
            'La nueva tarifa fue registrada correctamente.';
    }

    public function render()
    {
        Gate::authorize('administrar-conceptos-ingreso');

        $teamId = $this->teamId();

        $conceptos = ConceptoIngreso::query()
            ->where('team_id', $teamId)
            ->where('forma_importe', 'FIJO')
            ->orderBy('nombre')
            ->get();

        $tarifas = TarifaConceptoIngreso::query()
            ->with(['concepto', 'creadoPor'])
            ->where('team_id', $teamId)
            ->orderByDesc('vigente_desde')
            ->orderByDesc('id')
            ->get();

        return view('livewire.ingresos.tarifas-conceptos', [
            'conceptos' => $conceptos,
            'tarifas' => $tarifas,
        ]);
    }
}