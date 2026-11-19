<?php

namespace App\Livewire\Ingresos;

use App\Models\Afiliado;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use App\Services\Ingresos\CotizarHojaRutaService;
use App\Services\Ingresos\ReglasHojaRuta;
use Carbon\CarbonImmutable;
use DomainException;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Services\Ingresos\CobrarHojaRutaService;
use Illuminate\Support\Facades\Gate;

class HojasRuta extends Component
{
    use WithPagination;

    public string $buscar = '';

    public ?int $afiliadoSeleccionadoId = null;
    public string $periodoObjetivo = '';

    public ?array $cotizacion = null;

    public ?string $errorCotizacion = null;
    public ?string $mensajeCobro = null;
    public ?int $reciboGeneradoId = null;

    /**
     * Obtener el equipo activo.
     */
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

    /**
     * Reiniciar paginación al buscar.
     */
    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    /**
     * Seleccionar afiliado para emitir hoja de ruta.
     */
    public function seleccionar(int $afiliadoId): void
    {
        $teamId = $this->teamId();

        $afiliado = Afiliado::query()
            ->where('team_id', $teamId)
            ->whereIn('estado', [
                'ACTIVO',
                'BLOQUEADO',
                'INACTIVO',
            ])
            ->findOrFail($afiliadoId);

        $this->afiliadoSeleccionadoId = $afiliado->id;
        $this->cotizacion = null;
        $this->errorCotizacion = null;
        $this->mensajeCobro = null;
        $this->reciboGeneradoId = null;
    }

    /**
     * Cancelar selección.
     */
    public function cancelarSeleccion(): void
    {
        $this->afiliadoSeleccionadoId = null;

        $this->cotizacion = null;
        $this->errorCotizacion = null;
        $this->mensajeCobro = null;
        $this->reciboGeneradoId = null;
    }

    /**
     * Listado de afiliados y datos seleccionados.
     */
    public function render()
    {
        $teamId = $this->teamId();

        $termino = trim($this->buscar);

        $consulta = Afiliado::query()
            ->with([
                'grupo',
                'asignacionVigente.vehiculo',
            ])
            ->where('team_id', $teamId)
            ->whereIn('estado', [
                'ACTIVO',
                'BLOQUEADO',
                'INACTIVO',
            ]);

        if ($termino !== '') {

            $consulta->where(function (Builder $query) use ($termino) {

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
                    ->orWhereHas(
                        'asignacionVigente.vehiculo',
                        function (Builder $vehiculo) use ($termino) {

                            $vehiculo->where(
                                'placa',
                                'ILIKE',
                                "%{$termino}%"
                            );
                        }
                    );
            });
        }

        $afiliados = $consulta
            ->orderBy('nombre_completo')
            ->paginate(10);

        $seleccionado = null;

        if ($this->afiliadoSeleccionadoId !== null) {

            $seleccionado = Afiliado::query()
                ->with([
                    'grupo',
                    'asignacionVigente.vehiculo',
                ])
                ->where('team_id', $teamId)
                ->whereIn('estado', [
                    'ACTIVO',
                    'BLOQUEADO',
                    'INACTIVO',
                ])
                ->find($this->afiliadoSeleccionadoId);
        }

        return view(
            'livewire.ingresos.hojas-ruta',
            [
                'afiliados' => $afiliados,
                'seleccionado' => $seleccionado,
            ]
        );
    }
    public function mount(): void
    {
        $hoy = CarbonImmutable::now('America/La_Paz');

        $mesSiguiente = $hoy->addMonthNoOverflow();

        $apertura = ReglasHojaRuta::fechaApertura(
            (int) $mesSiguiente->year,
            (int) $mesSiguiente->month
        );

        // A partir del día de apertura, sugerir el mes siguiente.
        $this->periodoObjetivo = (
            $hoy->greaterThanOrEqualTo($apertura)
                ? $mesSiguiente
                : $hoy
        )->format('Y-m');
    }

    public function updatedPeriodoObjetivo(): void
    {
        $this->cotizacion = null;
        $this->errorCotizacion = null;
        $this->mensajeCobro = null;
    }

    /**
     * Solo calcula una cotización.
     * No crea recibos ni registra pagos.
     */
    public function previsualizar(
        CotizarHojaRutaService $servicio
    ): void {
        $this->cotizacion = null;
        $this->errorCotizacion = null;
        $this->mensajeCobro = null;
        $this->reciboGeneradoId = null;

        $this->validate([
            'periodoObjetivo' => [
                'required',
                'date_format:Y-m',
            ],
        ]);

        if ($this->afiliadoSeleccionadoId === null) {
            $this->errorCotizacion =
                'Primero debe seleccionar un afiliado.';

            return;
        }

        $inicioControl = config('hojas_ruta.inicio_control');

        if (
            ! is_string($inicioControl)
            || ! preg_match(
                '/^\d{4}-(0[1-9]|1[0-2])$/',
                $inicioControl
            )
        ) {
            $this->errorCotizacion =
                'Debe configurarse el mes oficial de inicio '
                .'del control de hojas de ruta.';

            return;
        }

        try {
            $this->cotizacion = $servicio->cotizar(
                $this->teamId(),
                $this->afiliadoSeleccionadoId,
                $inicioControl,
                $this->periodoObjetivo,
                CarbonImmutable::now('America/La_Paz')
            );
        } catch (
            DomainException
            | InvalidArgumentException
            | ModelNotFoundException $e
        ) {
            $this->errorCotizacion = $e->getMessage();
        }
    }
    public function procesarCobro(
        CobrarHojaRutaService $servicio
    ): void {

        Gate::authorize('cobrar-hojas-ruta');

        $this->errorCotizacion = null;
        $this->mensajeCobro = null;

        if (! config('hojas_ruta.cobros_habilitados', false)) {

            $this->errorCotizacion =
                'Los cobros todavía no están habilitados.';

            return;
        }

        $this->validate([
            'periodoObjetivo' => [
                'required',
                'date_format:Y-m',
            ],
        ]);

        if (
            $this->afiliadoSeleccionadoId === null
            || $this->cotizacion === null
        ) {

            $this->errorCotizacion =
                'Primero debe calcular las mensualidades pendientes.';

            return;
        }

        try {

            $recibo = $servicio->cobrar(
                $this->afiliadoSeleccionadoId,
                $this->periodoObjetivo,
                (string) $this->cotizacion['total']
            );

            $this->mensajeCobro =
                'Cobro registrado correctamente. '
                . 'Recibo N.º ' . $recibo->numero_recibo
                . ' por Bs ' . $recibo->total;
                $this->reciboGeneradoId = $recibo->id;

            // La cotización anterior ya no es válida.
            $this->cotizacion = null;

        } catch (
            DomainException
            | InvalidArgumentException
            | ModelNotFoundException $e
        ) {

            $this->errorCotizacion = $e->getMessage();

            // Obligar a recalcular antes de intentar nuevamente.
            $this->cotizacion = null;
        }
    }
}