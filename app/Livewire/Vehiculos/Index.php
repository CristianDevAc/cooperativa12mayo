<?php

namespace App\Livewire\Vehiculos;

use App\Models\Afiliado;
use App\Models\HistorialVehiculo;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Gate;

class Index extends Component
{
    use WithPagination;
    use WithFileUploads;

    public string $buscar = '';

    public string $afiliadoId = '';
    public string $placa = '';
    public string $marca = '';
    public string $modelo = '';
    public string $tipo = '';
    public string $anio = '';
    public string $color = '';
    public string $observaciones = '';

    public bool $mostrarFormulario = false;
    public ?int $historialAfiliadoId = null;
    public $foto = null;

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

    public function nuevo(): void
    {
        Gate::authorize('gestionar-padron');

        $this->limpiarFormulario();
        $this->mostrarFormulario = true;
    }

    public function cancelar(): void
    {
        $this->limpiarFormulario();
    }

    public function guardar(): void
    {
        Gate::authorize('gestionar-padron');
        $teamId = $this->teamId();

        $this->validate([
            'afiliadoId' => [
                'required',
                'integer',
                Rule::exists('afiliados', 'id')->where('team_id', $teamId),
            ],
            'placa' => ['required', 'string', 'max:20'],
            'marca' => ['nullable', 'string', 'max:80'],
            'modelo' => ['nullable', 'string', 'max:80'],
            'tipo' => ['nullable', 'string', 'max:50'],
            'anio' => ['nullable', 'integer', 'between:1950,2100'],
            'color' => ['nullable', 'string', 'max:50'],
            'observaciones' => ['nullable', 'string', 'max:1000'],

            // Fotografía: JPG, PNG o WebP, máximo 2 MB
            'foto' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        $placaNormalizada = preg_replace(
            '/[\s-]+/',
            '',
            strtoupper(trim($this->placa))
        );

        if ($placaNormalizada === '') {
            throw ValidationException::withMessages([
                'placa' => 'Ingrese una placa válida.',
            ]);
        }

        // Guardar la imagen en almacenamiento privado.
        $nuevaFoto = null;

        if ($this->foto) {
            $nuevaFoto = $this->foto->store('vehiculos', 'local');

            if ($nuevaFoto === false) {
                throw ValidationException::withMessages([
                    'foto' => 'No se pudo guardar la fotografía.',
                ]);
            }
        }

        try {
            DB::transaction(function () use (
                $teamId,
                $placaNormalizada,
                $nuevaFoto
            ) {
                $afiliado = Afiliado::query()
                    ->where('team_id', $teamId)
                    ->lockForUpdate()
                    ->findOrFail((int) $this->afiliadoId);

                $vehiculo = Vehiculo::query()
                    ->where('team_id', $teamId)
                    ->where('placa', $placaNormalizada)
                    ->lockForUpdate()
                    ->first();

                if (! $vehiculo) {
                    $vehiculo = Vehiculo::create([
                        'team_id' => $teamId,
                        'placa' => $placaNormalizada,
                        'marca' => trim($this->marca) ?: null,
                        'modelo' => trim($this->modelo) ?: null,
                        'tipo' => trim($this->tipo) ?: null,
                        'anio' => $this->anio !== ''
                            ? (int) $this->anio
                            : null,
                        'color' => trim($this->color) ?: null,
                        'activo' => true,
                        'foto_path' => $nuevaFoto,
                    ]);
                } elseif ($nuevaFoto !== null) {
                    $vehiculo->update([
                        'foto_path' => $nuevaFoto,
                    ]);
                }

                if (! $vehiculo->activo) {
                    throw ValidationException::withMessages([
                        'placa' => 'Este vehículo se encuentra inactivo.',
                    ]);
                }

                $anterior = HistorialVehiculo::query()
                    ->where('team_id', $teamId)
                    ->where('afiliado_id', $afiliado->id)
                    ->whereNull('fecha_fin')
                    ->lockForUpdate()
                    ->first();

                if ($anterior?->vehiculo_id === $vehiculo->id) {
                    throw ValidationException::withMessages([
                        'placa' => 'Este vehículo ya está asignado al afiliado.',
                    ]);
                }

                $asignadoAOtro = HistorialVehiculo::query()
                    ->where('team_id', $teamId)
                    ->where('vehiculo_id', $vehiculo->id)
                    ->whereNull('fecha_fin')
                    ->exists();

                if ($asignadoAOtro) {
                    throw ValidationException::withMessages([
                        'placa' => 'El vehículo está vigente para otro afiliado.',
                    ]);
                }

                $momento = now();

                if ($anterior) {
                    $anterior->update([
                        'fecha_fin' => $momento,
                    ]);
                }

                HistorialVehiculo::create([
                    'team_id' => $teamId,
                    'afiliado_id' => $afiliado->id,
                    'vehiculo_id' => $vehiculo->id,
                    'fecha_inicio' => $momento,
                    'fecha_fin' => null,
                    'motivo' => $anterior
                        ? 'CAMBIO_HERRAMIENTA'
                        : 'ALTA',
                    'observaciones' => trim($this->observaciones) ?: null,
                ]);

            }, attempts: 3);

        } catch (\Throwable $e) {
            // Si falló la operación, retirar únicamente
            // el archivo recién subido.
            if ($nuevaFoto !== null) {
                Storage::disk('local')->delete($nuevaFoto);
            }

            throw $e;
        }

        session()->flash(
            'mensaje',
            'Vehículo y fotografía registrados correctamente.'
        );

        $this->limpiarFormulario();
    }

    public function verHistorial(int $afiliadoId): void
    {
        Afiliado::query()
            ->where('team_id', $this->teamId())
            ->findOrFail($afiliadoId);

        $this->historialAfiliadoId = $afiliadoId;
    }

    public function cerrarHistorial(): void
    {
        $this->historialAfiliadoId = null;
    }

    private function limpiarFormulario(): void
    {
        $this->afiliadoId = '';
        $this->placa = '';
        $this->marca = '';
        $this->modelo = '';
        $this->tipo = '';
        $this->anio = '';
        $this->color = '';
        $this->observaciones = '';
        $this->mostrarFormulario = false;
        $this->foto = null;
        $this->resetValidation();
    }

    public function render()
    {
        $teamId = $this->teamId();
        $buscar = trim($this->buscar);

        $afiliados = Afiliado::query()
            ->where('team_id', $teamId)
            ->with(['grupo', 'asignacionVigente.vehiculo'])
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($subquery) use ($buscar) {
                    $subquery
                        ->where('nombre_completo', 'ilike', "%{$buscar}%")
                        ->orWhere('ci', 'ilike', "%{$buscar}%")
                        ->orWhereHas(
                            'asignacionVigente.vehiculo',
                            fn ($vehiculo) => $vehiculo
                                ->where('placa', 'ilike', "%{$buscar}%")
                        );
                });
            })
            ->orderBy('nombre_completo')
            ->paginate(10);

        $historial = collect();
        $afiliadoHistorial = null;

        if ($this->historialAfiliadoId !== null) {
            $afiliadoHistorial = Afiliado::query()
                ->where('team_id', $teamId)
                ->find($this->historialAfiliadoId);

            $historial = HistorialVehiculo::query()
                ->with('vehiculo')
                ->where('team_id', $teamId)
                ->where('afiliado_id', $this->historialAfiliadoId)
                ->orderByDesc('fecha_inicio')
                ->get();
        }

        return view('livewire.vehiculos.index', [
            'afiliados' => $afiliados,
            'listaAfiliados' => Afiliado::query()
                ->where('team_id', $teamId)
                ->orderBy('nombre_completo')
                ->get(),
            'historial' => $historial,
            'afiliadoHistorial' => $afiliadoHistorial,
        ]);
    }
}