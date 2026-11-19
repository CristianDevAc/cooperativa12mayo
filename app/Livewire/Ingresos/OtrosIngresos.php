<?php

namespace App\Livewire\Ingresos;

use App\Models\Afiliado;
use App\Models\ConceptoIngreso;
use App\Models\ReciboOtroIngreso;
use App\Models\TarifaConceptoIngreso;
use App\Models\User;
use App\Services\Ingresos\CobrarOtroIngresoService;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Illuminate\Support\Str;
use App\Models\SolicitudReincorporacion;
use App\Models\HistorialVehiculo;

class OtrosIngresos extends Component
{
    public string $conceptoId = '';
    public string $buscarAfiliado = '';
    public string $detalle = '';
    public string $importeVariable = '';

    #[Locked]
    public ?int $afiliadoSeleccionadoId = null;

    #[Locked]
    public ?array $cotizacion = null;

    #[Locked]
    public ?array $ultimoRecibo = null;

    public string $errorCobro = '';
    public string $historialVehiculoId = '';

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

    public function updatedConceptoId(): void
    {
        $this->cotizacion = null;
        $this->ultimoRecibo = null;
        $this->afiliadoSeleccionadoId = null;
        $this->buscarAfiliado = '';
        $this->importeVariable = '';
        $this->errorCobro = '';
        $this->resetValidation();
        $this->historialVehiculoId = '';
    }

    public function updatedDetalle(): void
    {
        $this->cotizacion = null;
        $this->errorCobro = '';
    }

    public function updatedImporteVariable(): void
    {
        $this->cotizacion = null;
        $this->errorCobro = '';
    }

    public function seleccionarAfiliado(int $id): void
    {
        Gate::authorize('cobrar-otros-ingresos');

        $afiliado = Afiliado::query()
            ->where('team_id', $this->teamId())
            ->findOrFail($id);

        $this->afiliadoSeleccionadoId = $afiliado->id;
        $this->buscarAfiliado = '';
        $this->historialVehiculoId = '';
        $this->cotizacion = null;
        $this->errorCobro = '';
    }

    public function quitarAfiliado(): void
    {
        Gate::authorize('cobrar-otros-ingresos');

        $this->afiliadoSeleccionadoId = null;
        $this->cotizacion = null;
        $this->historialVehiculoId = '';
        $this->errorCobro = '';
    }

    private function normalizarMonto(string $valor): ?string
    {
        $valor = trim($valor);

        if (! preg_match(
            '/^\d{1,10}(?:\.\d{1,2})?$/',
            $valor
        )) {
            return null;
        }

        $partes = explode('.', $valor, 2);

        $entero = ltrim($partes[0], '0');
        $entero = $entero === '' ? '0' : $entero;

        $decimales = str_pad(
            $partes[1] ?? '',
            2,
            '0'
        );

        $resultado = $entero . '.' . $decimales;

        return $resultado === '0.00' ? null : $resultado;
    }

    public function previsualizar(): void
    {
        Gate::authorize('cobrar-otros-ingresos');

        $this->cotizacion = null;
        $this->ultimoRecibo = null;
        $this->errorCobro = '';
        $this->resetValidation();

        $this->validate([
            'conceptoId' => ['required', 'integer'],
            'detalle' => [
                'required',
                'string',
                'min:5',
                'max:500',
            ],
        ]);

        $teamId = $this->teamId();

        $concepto = ConceptoIngreso::query()
            ->where('team_id', $teamId)
            ->where('activo', true)
            ->findOrFail((int) $this->conceptoId);

        if (
            $concepto->codigo === 'OTROS_INGRESOS'
            && trim($this->detalle) === ''
        ) {
            $this->addError(
                'detalle',
                'Indique qué ingreso se está cobrando.'
            );

            return;
        }
        if (
            ! in_array(
                $concepto->forma_importe,
                ['FIJO', 'VARIABLE'],
                true
            )
            || ! in_array(
                $concepto->periodicidad,
                ['UNICO', 'POR_EVENTO'],
                true
            )
            || ! in_array(
                $concepto->naturaleza,
                ['OPERATIVO', 'EXTRAORDINARIO'],
                true
            )
            || $concepto->requiere_afiliado === null
            || $concepto->codigo === 'FONDO_SOCIAL'
        ) {
            $this->addError(
                'conceptoId',
                'El concepto todavía no está habilitado '
                . 'para este tipo de cobro.'
            );

            return;
        }

        $afiliado = null;

        if ($this->afiliadoSeleccionadoId !== null) {
            $afiliado = Afiliado::query()
                ->where('team_id', $teamId)
                ->findOrFail($this->afiliadoSeleccionadoId);
        }

        if (
            ($concepto->requiere_afiliado
                || $concepto->periodicidad === 'UNICO')
            && $afiliado === null
        ) {
            $this->addError(
                'afiliadoSeleccionadoId',
                'Debe seleccionar un afiliado.'
            );

            return;
        }

        /*
        * No permitir cotizar reincorporaciones
        * sin una solicitud administrativa pendiente.
        */
        $solicitudPendiente = null;

        if ($concepto->codigo === 'REINCORPORACION') {

            if ($afiliado === null) {
                $this->addError(
                    'afiliadoSeleccionadoId',
                    'Seleccione un afiliado con solicitud pendiente.'
                );

                return;
            }

            if ($afiliado->estado !== 'INACTIVO') {
                $this->addError(
                    'afiliadoSeleccionadoId',
                    'El afiliado debe encontrarse INACTIVO.'
                );

                return;
            }

            $solicitudPendiente = SolicitudReincorporacion::query()
                ->where('team_id', $teamId)
                ->where('afiliado_id', $afiliado->id)
                ->where('estado', 'PENDIENTE')
                ->whereNull('recibo_otro_ingreso_id')
                ->first();

            if ($solicitudPendiente === null) {
                $this->addError(
                    'afiliadoSeleccionadoId',
                    'Este afiliado no tiene una solicitud '
                    . 'de reincorporación pendiente de cobro.'
                );

                return;
            }
        }
        
        $movimientoCambio = null;

        if ($concepto->codigo === 'CAMBIO_HERRAMIENTA') {

            if (
                ! ctype_digit($this->historialVehiculoId)
                || (int) $this->historialVehiculoId < 1
            ) {
                $this->addError(
                    'historialVehiculoId',
                    'Seleccione un cambio de vehículo pendiente de cobro.'
                );

                return;
            }

            $movimientoCambio = HistorialVehiculo::query()
                ->where('team_id', $teamId)
                ->where('afiliado_id', $afiliado?->id)
                ->where('motivo', 'CAMBIO_HERRAMIENTA')
                ->where('id', (int) $this->historialVehiculoId)
                ->whereNotIn(
                    'id',
                    ReciboOtroIngreso::query()
                        ->select('historial_vehiculo_id')
                        ->where('team_id', $teamId)
                        ->whereNotNull('historial_vehiculo_id')
                )
                ->first();

            if ($movimientoCambio === null) {
                $this->addError(
                    'historialVehiculoId',
                    'El cambio seleccionado no existe o ya fue cobrado.'
                );

                return;
            }
        }


        if ($concepto->periodicidad === 'UNICO') {
            $yaPagado = ReciboOtroIngreso::query()
                ->where('team_id', $teamId)
                ->where('concepto_ingreso_id', $concepto->id)
                ->where('afiliado_id', $afiliado->id)
                ->where('estado', 'EMITIDO')
                ->exists();

            if ($yaPagado) {
                $this->addError(
                    'conceptoId',
                    'Este afiliado ya tiene registrado '
                    . 'el pago único de este concepto.'
                );

                return;
            }
        }

        $importe = null;
        $importeVariable = null;

        if ($concepto->forma_importe === 'FIJO') {
            $hoy = CarbonImmutable::now(
                'America/La_Paz'
            )->toDateString();

            $tarifa = TarifaConceptoIngreso::query()
                ->where('team_id', $teamId)
                ->where('concepto_ingreso_id', $concepto->id)
                ->whereDate('vigente_desde', '<=', $hoy)
                ->orderByDesc('vigente_desde')
                ->first();

            if (! $tarifa) {
                $this->addError(
                    'conceptoId',
                    'Este concepto no tiene una tarifa vigente.'
                );

                return;
            }

            $importe = (string) $tarifa->importe;

        } else {
            $importeVariable = trim($this->importeVariable);

            $importe = $this->normalizarMonto(
                $importeVariable
            );

            if ($importe === null) {
                $this->addError(
                    'importeVariable',
                    'Ingrese un importe mayor que cero, '
                    . 'con máximo dos decimales y punto decimal.'
                );

                return;
            }
        }

        $this->cotizacion = [
            'concepto_id' => $concepto->id,
            'concepto' => $concepto->nombre,
            'afiliado_id' => $afiliado?->id,
            'afiliado' => $afiliado?->nombre_completo ?? 'No aplica',
            'forma_importe' => $concepto->forma_importe,
            'periodicidad' => $concepto->periodicidad,
            'detalle' => trim($this->detalle),
            'importe_esperado' => $importe,
            'importe_variable' => $importeVariable,
            'operacion_uuid' => (string) Str::uuid(),
            'solicitud_reincorporacion_id' => $solicitudPendiente?->id,
            'historial_vehiculo_id' => $movimientoCambio?->id,
        ];
    }

    public function procesarCobro(
        CobrarOtroIngresoService $servicio
    ): void {
        Gate::authorize('cobrar-otros-ingresos');

        $this->errorCobro = '';

        if ($this->cotizacion === null) {
            $this->errorCobro =
                'Primero debe calcular el importe del ingreso.';

            return;
        }

        if (! config('otros_ingresos.cobros_habilitados', false)) {
            $this->errorCobro =
                'Los cobros de otros ingresos aún no están habilitados.';

            return;
        }

        try {
            $recibo = $servicio->cobrar(
                (int) $this->cotizacion['concepto_id'],
                $this->cotizacion['afiliado_id'],
                $this->cotizacion['detalle'],
                $this->cotizacion['importe_esperado'],
                $this->cotizacion['operacion_uuid'],
                $this->cotizacion['importe_variable'],
                $this->cotizacion['historial_vehiculo_id'] ?? null
            );

        } catch (DomainException $e) {
            $this->cotizacion = null;
            $this->errorCobro = $e->getMessage();

            return;
        }

        $this->ultimoRecibo = [
            'id' => $recibo->id,
            'numero' => $recibo->numero_recibo,
            'concepto' => $recibo->concepto_nombre,
            'importe' => $recibo->importe,
            'codigo' => $recibo->concepto_codigo,
        ];

        $this->cotizacion = null;
        $this->conceptoId = '';
        $this->afiliadoSeleccionadoId = null;
        $this->buscarAfiliado = '';
        $this->detalle = '';
        $this->importeVariable = '';
        $this->historialVehiculoId = '';
    }

    public function cancelar(): void
    {
        Gate::authorize('cobrar-otros-ingresos');

        $this->conceptoId = '';
        $this->buscarAfiliado = '';
        $this->afiliadoSeleccionadoId = null;
        $this->detalle = '';
        $this->importeVariable = '';
        $this->cotizacion = null;
        $this->ultimoRecibo = null;
        $this->historialVehiculoId = '';
        $this->errorCobro = '';

        $this->resetValidation();
    }

    public function render()
    {
        Gate::authorize('cobrar-otros-ingresos');

        $teamId = $this->teamId();

        $conceptos = ConceptoIngreso::query()
            ->where('team_id', $teamId)
            ->where('activo', true)
            ->whereIn('forma_importe', ['FIJO', 'VARIABLE'])
            ->whereIn('periodicidad', ['UNICO', 'POR_EVENTO'])
            ->whereIn('naturaleza', ['OPERATIVO', 'EXTRAORDINARIO'])
            ->whereNotNull('requiere_afiliado')
            ->where('codigo', '!=', 'FONDO_SOCIAL')
            ->orderBy('nombre')
            ->get();

        $conceptoSeleccionado = $conceptos->first(
            fn ($concepto) =>
                (string) $concepto->id === $this->conceptoId
        );

        $afiliadoSeleccionado = null;

        if ($this->afiliadoSeleccionadoId !== null) {
            $afiliadoSeleccionado = Afiliado::query()
                ->where('team_id', $teamId)
                ->find($this->afiliadoSeleccionadoId);
        }

        $afiliados = collect();

        $termino = trim($this->buscarAfiliado);

        if (mb_strlen($termino) >= 2) {

            $consultaAfiliados = Afiliado::query()
                ->where('team_id', $teamId);

            /*
            * Para reincorporaciones, buscar solamente
            * afiliados inactivos con solicitudes pendientes.
            */
            if (
                $conceptoSeleccionado?->codigo === 'REINCORPORACION'
            ) {

                $consultaAfiliados
                    ->where('estado', 'INACTIVO')
                    ->whereIn(
                        'id',
                        SolicitudReincorporacion::query()
                            ->select('afiliado_id')
                            ->where('team_id', $teamId)
                            ->where('estado', 'PENDIENTE')
                            ->whereNull('recibo_otro_ingreso_id')
                    );
            }

            $afiliados = $consultaAfiliados
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

        $movimientosPendientes = collect();

        if (
            $conceptoSeleccionado?->codigo === 'CAMBIO_HERRAMIENTA'
            && $afiliadoSeleccionado !== null
        ) {
            $movimientosPendientes = HistorialVehiculo::query()
                ->where('team_id', $teamId)
                ->where('afiliado_id', $afiliadoSeleccionado->id)
                ->where('motivo', 'CAMBIO_HERRAMIENTA')
                ->whereNotIn(
                    'id',
                    ReciboOtroIngreso::query()
                        ->select('historial_vehiculo_id')
                        ->where('team_id', $teamId)
                        ->whereNotNull('historial_vehiculo_id')
                )
                ->orderByDesc('id')
                ->get();
        }
        return view('livewire.ingresos.otros-ingresos', [
            'conceptos' => $conceptos,
            'conceptoSeleccionado' => $conceptoSeleccionado,
            'afiliadoSeleccionado' => $afiliadoSeleccionado,
            'afiliados' => $afiliados,
            'movimientosPendientes' => $movimientosPendientes,
            'cobrosHabilitados' => (bool) config(
                'otros_ingresos.cobros_habilitados',
                false
            ),
        ]);
    }
    public function updatedHistorialVehiculoId(): void
    {
        $this->cotizacion = null;
        $this->errorCobro = '';
    }
}