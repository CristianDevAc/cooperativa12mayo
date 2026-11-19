<?php

namespace App\Livewire\Ingresos;

use App\Models\TarifaHojaRuta;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Support\Facades\Gate;

class Tarifas extends Component
{
    public string $vigencia = '';

    public string $importeBase = '60.00';

    public string $importeMulta = '20.00';

    public string $observaciones = '';

    public string $mensaje = '';

    public function mount(): void
    {
        $this->vigencia = now('America/La_Paz')->format('Y-m');

        $ultima = TarifaHojaRuta::query()
            ->where('team_id', $this->teamId())
            ->orderByDesc('vigente_desde')
            ->first();

        if ($ultima) {
            $this->importeBase = $ultima->importe_base;
            $this->importeMulta = $ultima->importe_multa;
        }
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
        Gate::authorize('administrar-tarifas-hoja-ruta');
        $this->mensaje = '';

        $datos = $this->validate([
            'vigencia' => [
                'required',
                'date_format:Y-m',
            ],
            'importeBase' => [
                'required',
                'numeric',
                'min:0.01',
                'decimal:0,2',
            ],
            'importeMulta' => [
                'required',
                'numeric',
                'min:0',
                'decimal:0,2',
            ],
            'observaciones' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $teamId = $this->teamId();

        $fechaVigencia = $datos['vigencia'] . '-01';

        $existe = TarifaHojaRuta::query()
            ->where('team_id', $teamId)
            ->whereDate('vigente_desde', $fechaVigencia)
            ->exists();

        if ($existe) {
            $this->addError(
                'vigencia',
                'Ya existe una tarifa registrada para ese mes.'
            );

            return;
        }

        TarifaHojaRuta::create([
            'team_id' => $teamId,
            'vigente_desde' => $fechaVigencia,
            'importe_base' => $datos['importeBase'],
            'importe_multa' => $datos['importeMulta'],
            'observaciones' => $datos['observaciones'] ?: null,
            'creado_por' => Auth::id(),
        ]);

        $this->mensaje = 'La nueva tarifa fue registrada correctamente.';

        $this->observaciones = '';
    }

    public function render()
    {
        $tarifas = TarifaHojaRuta::query()
            ->where('team_id', $this->teamId())
            ->orderByDesc('vigente_desde')
            ->get();

        return view('livewire.ingresos.tarifas', [
            'tarifas' => $tarifas,
        ]);
    }
}