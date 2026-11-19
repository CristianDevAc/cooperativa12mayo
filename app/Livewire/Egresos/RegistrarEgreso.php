<?php

namespace App\Livewire\Egresos;

use App\Models\ConceptoEgreso;
use App\Models\User;
use App\Services\Egresos\RegistrarEgresoService;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Illuminate\Support\Str;

class RegistrarEgreso extends Component
{
    public string $conceptoEgresoId = '';
    public string $beneficiario = '';
    public string $detalle = '';
    public string $importe = '';
    public string $medioPago = 'EFECTIVO';
    public string $referenciaPago = '';
    public string $tipoDocumento = '';
    public string $numeroDocumento = '';
    public string $observaciones = '';

    #[Locked]
    public ?array $cotizacion = null;

    #[Locked]
    public ?array $ultimoEgreso = null;

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

    /*
     * Si cambia cualquier dato después de calcular,
     * se elimina la vista previa anterior.
     */
    public function updated(string $propiedad): void
    {
        if (in_array($propiedad, [
            'conceptoEgresoId',
            'beneficiario',
            'detalle',
            'importe',
            'medioPago',
            'referenciaPago',
            'tipoDocumento',
            'numeroDocumento',
            'observaciones',
        ], true)) {
            $this->cotizacion = null;
            $this->errorOperacion = '';
        }
    }

    public function previsualizar(): void
    {
        Gate::authorize('registrar-egresos');

        $this->cotizacion = null;
        $this->ultimoEgreso = null;
        $this->errorOperacion = '';
        $this->resetValidation();

        $teamId = $this->teamId();

        $this->validate([
            'conceptoEgresoId' => [
                'required',
                'integer',
                Rule::exists('conceptos_egreso', 'id')
                    ->where('team_id', $teamId),
            ],
            'beneficiario' => [
                'required',
                'string',
                'min:3',
                'max:200',
            ],
            'detalle' => [
                'required',
                'string',
                'min:5',
                'max:500',
            ],
            'importe' => [
                'required',
                'regex:/^\d{1,10}(?:\.\d{1,2})?$/D',
            ],
            'medioPago' => [
                'required',
                Rule::in([
                    'EFECTIVO',
                    'TRANSFERENCIA',
                    'DEPOSITO',
                    'OTRO',
                ]),
            ],
            'referenciaPago' => [
                'nullable',
                'string',
                'max:150',
            ],
            'tipoDocumento' => [
                'nullable',
                Rule::in([
                    'FACTURA',
                    'RECIBO',
                    'OTRO',
                ]),
            ],
            'numeroDocumento' => [
                'nullable',
                'string',
                'max:100',
            ],
            'observaciones' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        $concepto = ConceptoEgreso::query()
            ->where('team_id', $teamId)
            ->where('activo', true)
            ->find((int) $this->conceptoEgresoId);

        if ($concepto === null) {
            $this->addError(
                'conceptoEgresoId',
                'El concepto seleccionado no está habilitado.'
            );

            return;
        }

        $importe = trim($this->importe);

        if (preg_match('/^0+(?:\.0{1,2})?$/D', $importe)) {
            $this->addError(
                'importe',
                'El importe debe ser mayor que cero.'
            );

            return;
        }

        $referencia = trim($this->referenciaPago);
        $numeroDocumento = trim($this->numeroDocumento);

        if (
            in_array(
                $this->medioPago,
                ['TRANSFERENCIA', 'DEPOSITO'],
                true
            )
            && $referencia === ''
        ) {
            $this->addError(
                'referenciaPago',
                'Indique la referencia del pago bancario.'
            );

            return;
        }

        if (
            $this->tipoDocumento !== ''
            && $numeroDocumento === ''
        ) {
            $this->addError(
                'numeroDocumento',
                'Indique el número del documento.'
            );

            return;
        }

        if (
            $this->tipoDocumento === ''
            && $numeroDocumento !== ''
        ) {
            $this->addError(
                'tipoDocumento',
                'Seleccione el tipo de documento.'
            );

            return;
        }

        /*
         * Cotización protegida:
         * los datos confirmados no pueden modificarse
         * directamente desde el navegador.
         */
        $this->cotizacion = [
            'concepto_egreso_id' => $concepto->id,
            'concepto_nombre' => $concepto->nombre,
            'grupo_reporte' => $concepto->grupo_reporte,
            'beneficiario' => trim($this->beneficiario),
            'detalle' => trim($this->detalle),
            'importe' => $importe,
            'medio_pago' => $this->medioPago,
            'referencia_pago' => $referencia,
            'tipo_documento' =>
                $this->tipoDocumento !== ''
                    ? $this->tipoDocumento
                    : null,
            'numero_documento' => $numeroDocumento,
            'observaciones' => trim($this->observaciones),
            'operacion_uuid' => (string) Str::uuid(),
        ];
    }
    public function editarDatos(): void
    {
        Gate::authorize('registrar-egresos');

        $this->cotizacion = null;
        $this->errorOperacion = '';
    }

    public function confirmar(
        RegistrarEgresoService $servicio
    ): void {
        Gate::authorize('registrar-egresos');

        $this->errorOperacion = '';

        if ($this->cotizacion === null) {
            $this->errorOperacion =
                'Primero revise los datos del egreso.';

            return;
        }

        if (! config('egresos.pagos_habilitados', false)) {
            $this->errorOperacion =
                'El registro definitivo de egresos '
                . 'todavía está deshabilitado.';

            return;
        }

        try {
            $egreso = $servicio->registrar(
                $this->cotizacion
            );

        } catch (DomainException $e) {
            $this->cotizacion = null;
            $this->errorOperacion = $e->getMessage();

            return;
        }

        $this->ultimoEgreso = [
            'id' => $egreso->id,
            'numero' => $egreso->numero_comprobante,
            'concepto' => $egreso->concepto_nombre,
            'beneficiario' => $egreso->beneficiario,
            'importe' => $egreso->importe,
        ];

        $this->cotizacion = null;

        $this->reset([
            'conceptoEgresoId',
            'beneficiario',
            'detalle',
            'importe',
            'referenciaPago',
            'tipoDocumento',
            'numeroDocumento',
            'observaciones',
        ]);

        $this->medioPago = 'EFECTIVO';
    }

    public function cancelar(): void
    {
        Gate::authorize('registrar-egresos');

        $this->cotizacion = null;
        $this->ultimoEgreso = null;
        $this->errorOperacion = '';

        $this->reset([
            'conceptoEgresoId',
            'beneficiario',
            'detalle',
            'importe',
            'referenciaPago',
            'tipoDocumento',
            'numeroDocumento',
            'observaciones',
        ]);

        $this->medioPago = 'EFECTIVO';
        $this->resetValidation();
    }

    public function render()
    {
        Gate::authorize('registrar-egresos');

        $conceptos = ConceptoEgreso::query()
            ->where('team_id', $this->teamId())
            ->where('activo', true)
            ->orderBy('grupo_reporte')
            ->orderBy('nombre')
            ->get();

        return view('livewire.egresos.registrar-egreso', [
            'conceptos' => $conceptos,
            'pagosHabilitados' => (bool) config(
                'egresos.pagos_habilitados',
                false
            ),
        ]);
    }
}