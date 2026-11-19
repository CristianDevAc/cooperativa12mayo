<?php

namespace App\Livewire\Afiliados;

use App\Models\Afiliado;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use App\Services\Afiliados\GestionInactividadAfiliadoService;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Gate;

class Index extends Component
{
    use WithPagination;

    // Búsqueda y filtros
    public string $buscar = '';
    public string $grupoFiltro = '';
    public string $estadoFiltro = '';

    // Formulario
    public string $grupoId = '';
    public string $numeroSocio = '';
    public string $nombreCompleto = '';
    public string $ci = '';
    public string $telefono = '';
    public string $direccion = '';
    public string $fechaIngreso = '';
    public string $fechaNacimiento = '';
    public string $estado = 'ACTIVO';
    public string $observaciones = '';

    public ?int $editandoId = null;
    public bool $mostrarFormulario = false;

    // Inactivación y reactivación
    public bool $mostrarCambioEstado = false;
    public ?int $cambioEstadoId = null;
    public string $cambioEstadoNombre = '';
    public string $cambioEstadoAccion = '';
    public string $motivoInactividad = '';
    

    private function teamId(): int
    {
        $usuario = Auth::user();

        if (! $usuario instanceof User || ! $usuario->current_team_id) {
            abort(403, 'No existe un equipo activo.');
        }

        return (int) $usuario->current_team_id;
    }

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function updatedGrupoFiltro(): void
    {
        $this->resetPage();
    }

    public function updatedEstadoFiltro(): void
    {
        $this->resetPage();
    }

    public function nuevo(): void
    {
        Gate::authorize('gestionar-padron');
        $this->limpiarFormulario();

        $this->fechaIngreso = now('America/La_Paz')->toDateString();

        $this->mostrarFormulario = true;
    }

    public function editar(int $id): void
    {
        Gate::authorize('gestionar-padron');
        $afiliado = Afiliado::query()
            ->where('team_id', $this->teamId())
            ->findOrFail($id);

        $this->editandoId = $afiliado->id;
        $this->grupoId = (string) ($afiliado->grupo_id ?? '');
        $this->numeroSocio = $afiliado->numero_socio ?? '';
        $this->nombreCompleto = $afiliado->nombre_completo;
        $this->ci = $afiliado->ci ?? '';
        $this->telefono = $afiliado->telefono ?? '';
        $this->direccion = $afiliado->direccion ?? '';
        $this->fechaIngreso = $afiliado->fecha_ingreso?->format('Y-m-d') ?? '';
        $this->fechaNacimiento = $afiliado->fecha_nacimiento?->format('Y-m-d') ?? '';
        $this->estado = $afiliado->estado;
        $this->observaciones = $afiliado->observaciones ?? '';

        $this->mostrarFormulario = true;
        $this->resetValidation();
    }

    public function guardar(): void
    {
        Gate::authorize('gestionar-padron');
        $teamId = $this->teamId();

        $this->validate([
            'grupoId' => [
                'nullable',
                'integer',
                Rule::exists('grupos', 'id')->where('team_id', $teamId),
            ],
            'numeroSocio' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('afiliados', 'numero_socio')
                    ->where('team_id', $teamId)
                    ->ignore($this->editandoId),
            ],
            'nombreCompleto' => ['required', 'string', 'max:180'],
            'ci' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('afiliados', 'ci')
                    ->where('team_id', $teamId)
                    ->ignore($this->editandoId),
            ],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string'],
            'fechaNacimiento' => [
                'nullable',
                'date',
                'before_or_equal:' . now('America/La_Paz')->toDateString(),
            ],
            'estado' => [
                'required',
                Rule::in([
                    'ACTIVO',
                    'INACTIVO',
                    'BLOQUEADO',
                    'RETIRADO',
                ]),
            ],
            'observaciones' => ['nullable', 'string'],
        ]);

        $datos = [
            'team_id' => $teamId,
            'grupo_id' => $this->grupoId === '' ? null : (int) $this->grupoId,
            'numero_socio' => trim($this->numeroSocio) ?: null,
            'nombre_completo' => trim($this->nombreCompleto),
            'ci' => trim($this->ci) ?: null,
            'telefono' => trim($this->telefono) ?: null,
            'direccion' => trim($this->direccion) ?: null,
            'fecha_nacimiento' => $this->fechaNacimiento ?: null,
            'estado' => $this->estado,
            'observaciones' => trim($this->observaciones) ?: null,
        ];

        if ($this->editandoId !== null) {

        DB::transaction(function () use ($teamId, $datos) {

            $afiliado = Afiliado::query()
                ->where('team_id', $teamId)
                ->lockForUpdate()
                ->findOrFail($this->editandoId);

            /*
            * No permitir entrar o salir del estado INACTIVO
            * mediante el formulario general.
            */
            $cambiaInactividad =
                ($afiliado->estado === 'INACTIVO'
                    || $this->estado === 'INACTIVO')
                && $afiliado->estado !== $this->estado;

            if ($cambiaInactividad) {
                throw ValidationException::withMessages([
                    'estado' => 'Utilice los botones Inactivar o Reactivar.',
                ]);
            }

            $afiliado->update($datos);

        }, 3);

        session()->flash(
            'mensaje',
            'Afiliado actualizado correctamente.'
        );

    } else {

        if ($this->estado === 'INACTIVO') {
            throw ValidationException::withMessages([
                'estado' => 'Primero registre al afiliado y después solicite su inactivación.',
            ]);
        }

        Afiliado::create([
            ...$datos,
            'fecha_ingreso' => now('America/La_Paz')->toDateString(),
        ]);

        session()->flash(
            'mensaje',
            'Afiliado registrado correctamente.'
        );
    }

        $this->limpiarFormulario();
    }

    public function cancelar(): void
    {
        $this->limpiarFormulario();
    }

    public function solicitarInactivacion(int $id): void
    {
        Gate::authorize('gestionar-padron');
        $this->abrirCambioEstado($id, 'INACTIVAR', 'ACTIVO');
    }

    public function solicitarReactivacion(int $id): void
    {
        Gate::authorize('gestionar-padron');
        $this->abrirCambioEstado($id, 'REACTIVAR', 'INACTIVO');
    }

    private function abrirCambioEstado(
        int $id,
        string $accion,
        string $estadoEsperado
    ): void {

        $afiliado = Afiliado::query()
            ->where('team_id', $this->teamId())
            ->where('estado', $estadoEsperado)
            ->findOrFail($id);

        $this->limpiarFormulario();

        $this->cambioEstadoId = $afiliado->id;
        $this->cambioEstadoNombre = $afiliado->nombre_completo;
        $this->cambioEstadoAccion = $accion;
        $this->motivoInactividad = '';

        $this->mostrarCambioEstado = true;

        $this->resetValidation();
    }

    public function confirmarCambioEstado(
        GestionInactividadAfiliadoService $servicio
    ): void {
        Gate::authorize('gestionar-padron');

        $teamId = $this->teamId();
        $usuario = Auth::user();

        abort_unless($usuario instanceof User, 403);

        abort_unless(
            $this->cambioEstadoId !== null
            && in_array(
                $this->cambioEstadoAccion,
                ['INACTIVAR', 'REACTIVAR'],
                true
            ),
            422
        );

        if ($this->cambioEstadoAccion === 'INACTIVAR') {

            $this->validate([
                'motivoInactividad' => [
                    'required',
                    'string',
                    'min:5',
                    'max:500',
                ],
            ]);
        }

        try {

            if ($this->cambioEstadoAccion === 'INACTIVAR') {

                $servicio->inactivar(
                    $teamId,
                    $this->cambioEstadoId,
                    $usuario->id,
                    trim($this->motivoInactividad)
                );

                $mensaje = 'Afiliado inactivado correctamente.';

            } else {

                $servicio->reactivar(
                    $teamId,
                    $this->cambioEstadoId,
                    $usuario->id
                );

                $mensaje = 'Afiliado reactivado correctamente.';
            }

            $this->cerrarCambioEstado();

            $this->resetPage();

            session()->flash('mensaje', $mensaje);

        } catch (DomainException $e) {

            $this->addError(
                'cambioEstado',
                $e->getMessage()
            );
        }
    }

    public function cerrarCambioEstado(): void
    {
        $this->mostrarCambioEstado = false;
        $this->cambioEstadoId = null;
        $this->cambioEstadoNombre = '';
        $this->cambioEstadoAccion = '';
        $this->motivoInactividad = '';

        $this->resetValidation();
    }
    private function limpiarFormulario(): void
    {
        $this->grupoId = '';
        $this->numeroSocio = '';
        $this->nombreCompleto = '';
        $this->ci = '';
        $this->telefono = '';
        $this->direccion = '';
        $this->fechaIngreso = '';
        $this->fechaNacimiento = '';
        $this->estado = 'ACTIVO';
        $this->observaciones = '';

        $this->editandoId = null;
        $this->mostrarFormulario = false;

        $this->resetValidation();
    }

    public function render()
    {
        $teamId = $this->teamId();
        $buscar = trim($this->buscar);

        $consulta = Afiliado::query()
            ->with(['grupo', 'asignacionVigente.vehiculo'])
            ->where('team_id', $teamId)
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($subquery) use ($buscar) {
                    $subquery
                        ->where('nombre_completo', 'ilike', "%{$buscar}%")
                        ->orWhere('numero_socio', 'ilike', "%{$buscar}%")
                        ->orWhere('ci', 'ilike', "%{$buscar}%")
                        ->orWhereHas(
                            'asignacionVigente.vehiculo',
                            function ($vehiculo) use ($buscar) {
                                $placaBuscar = preg_replace('/[\s-]+/', '', $buscar);

                                $vehiculo->where('placa', 'ilike', "%{$placaBuscar}%");
                            }
                        );
                });
            })
            ->when(
                $this->grupoFiltro !== '',
                fn ($query) => $query->where('grupo_id', $this->grupoFiltro)
            )
            ->when(
                $this->estadoFiltro !== '',
                fn ($query) => $query->where('estado', $this->estadoFiltro)
            );

        return view('livewire.afiliados.index', [
            'afiliados' => $consulta
                ->orderBy('nombre_completo')
                ->paginate(10),

            'grupos' => Grupo::query()
                ->where('team_id', $teamId)
                ->orderBy('codigo')
                ->get(),

            'totalAfiliados' => Afiliado::query()
                ->where('team_id', $teamId)
                ->count(),

            'totalActivos' => Afiliado::query()
                ->where('team_id', $teamId)
                ->where('estado', 'ACTIVO')
                ->count(),

            'totalBloqueados' => Afiliado::query()
                ->where('team_id', $teamId)
                ->where('estado', 'BLOQUEADO')
                ->count(),
            'totalInactivos' => Afiliado::query()
                ->where('team_id', $teamId)
                ->where('estado', 'INACTIVO')
                ->count(),
        ]);
    }
}