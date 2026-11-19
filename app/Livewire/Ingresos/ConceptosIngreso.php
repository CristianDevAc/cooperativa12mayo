<?php

namespace App\Livewire\Ingresos;

use App\Models\ConceptoIngreso;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use App\Models\TarifaConceptoIngreso;
use DomainException;
use Illuminate\Support\Facades\DB;

class ConceptosIngreso extends Component
{
    #[Locked]
    public ?int $editandoId = null;

    public bool $mostrarFormulario = false;

    public string $formaImporte = '';
    public string $periodicidad = '';
    public string $naturaleza = '';
    public string $requiereAfiliado = '';
    public string $descripcion = '';

    public string $mensaje = '';
    public string $errorActivacion = '';

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
        Gate::authorize('administrar-conceptos-ingreso');

        $concepto = ConceptoIngreso::query()
            ->where('team_id', $this->teamId())
            ->findOrFail($id);

        $this->editandoId = $concepto->id;

        $this->formaImporte = $concepto->forma_importe ?? '';
        $this->periodicidad = $concepto->periodicidad ?? '';
        $this->naturaleza = $concepto->naturaleza ?? '';

        $this->requiereAfiliado =
            $concepto->requiere_afiliado === null
                ? ''
                : ($concepto->requiere_afiliado ? 'SI' : 'NO');

        $this->descripcion = $concepto->descripcion ?? '';

        $this->mostrarFormulario = true;
        $this->mensaje = '';

        $this->resetValidation();
    }

    public function guardar(): void
    {
        Gate::authorize('administrar-conceptos-ingreso');

        $this->validate([
            'formaImporte' => [
                'nullable',
                Rule::in(['FIJO', 'VARIABLE']),
            ],
            'periodicidad' => [
                'nullable',
                Rule::in([
                    'UNICO',
                    'MENSUAL',
                    'ANUAL',
                    'POR_EVENTO',
                ]),
            ],
            'naturaleza' => [
                'nullable',
                Rule::in([
                    'OPERATIVO',
                    'EXTRAORDINARIO',
                    'PATRIMONIAL',
                ]),
            ],
            'requiereAfiliado' => [
                'nullable',
                Rule::in(['SI', 'NO']),
            ],
            'descripcion' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        if ($this->editandoId === null) {
            return;
        }

        $concepto = ConceptoIngreso::query()
            ->where('team_id', $this->teamId())
            ->findOrFail($this->editandoId);
        if ($concepto->activo) {
            $this->addError(
                'formaImporte',
                'Primero deshabilite este concepto antes de modificar su configuración.'
            );

            return;
        }

        $concepto->update([
            'forma_importe' => $this->formaImporte ?: null,
            'periodicidad' => $this->periodicidad ?: null,
            'naturaleza' => $this->naturaleza ?: null,

            'requiere_afiliado' => match ($this->requiereAfiliado) {
                'SI' => true,
                'NO' => false,
                default => null,
            },

            'descripcion' => trim($this->descripcion) ?: null,
        ]);

        // No se cambia "activo":
        // todavía no habilitamos cobros.

        $this->cancelar();

        $this->mensaje =
            'La configuración del concepto fue guardada correctamente.';
    }

    public function cancelar(): void
    {
        $this->editandoId = null;
        $this->mostrarFormulario = false;

        $this->formaImporte = '';
        $this->periodicidad = '';
        $this->naturaleza = '';
        $this->requiereAfiliado = '';
        $this->descripcion = '';

        $this->resetValidation();
    }

    public function cambiarActivacion(int $id): void
    {
        Gate::authorize('administrar-conceptos-ingreso');

        $this->mensaje = '';
        $this->errorActivacion = '';

        $teamId = $this->teamId();

        try {
            $activado = DB::transaction(function () use ($teamId, $id) {

                $concepto = ConceptoIngreso::query()
                    ->where('team_id', $teamId)
                    ->lockForUpdate()
                    ->findOrFail($id);

                /*
                * Primera etapa:
                * únicamente Certificaciones.
                */
                $reglas = match ($concepto->codigo) {
                    'CERTIFICACION' => [
                        'formas' => ['FIJO'],
                        'periodicidades' => ['POR_EVENTO'],
                        'naturalezas' => ['OPERATIVO'],
                        'afiliado_obligatorio' => true,
                    ],

                    'CUOTA_INGRESO' => [
                        'formas' => ['FIJO', 'VARIABLE'],
                        'periodicidades' => ['UNICO'],
                        'naturalezas' => ['OPERATIVO'],
                        'afiliado_obligatorio' => true,
                    ],

                    'LOGOTIPO' => [
                        'formas' => ['FIJO', 'VARIABLE'],
                        'periodicidades' => ['POR_EVENTO'],
                        'naturalezas' => ['OPERATIVO', 'EXTRAORDINARIO'],
                        'afiliado_obligatorio' => false,
                    ],

                    'OTROS_INGRESOS' => [
                        'formas' => ['VARIABLE'],
                        'periodicidades' => ['POR_EVENTO'],
                        'naturalezas' => ['OPERATIVO', 'EXTRAORDINARIO'],
                        'afiliado_obligatorio' => false,
                    ],
                    'REINCORPORACION' => [
                        'formas' => ['FIJO', 'VARIABLE'],
                        'periodicidades' => ['POR_EVENTO'],
                        'naturalezas' => ['OPERATIVO'],
                        'afiliado_obligatorio' => true,
                    ],
                    'CAMBIO_HERRAMIENTA' => [
                        'formas' => ['FIJO', 'VARIABLE'],
                        'periodicidades' => ['POR_EVENTO'],
                        'naturalezas' => ['EXTRAORDINARIO'],
                        'afiliado_obligatorio' => true,
                    ],

                    default => null,
                };

                if ($reglas === null) {
                    throw new DomainException(
                        'Este concepto todavía no está autorizado para habilitarse.'
                    );
                }

                /*
                * Si está activo, se permite deshabilitarlo.
                * Esto no modifica recibos ya registrados.
                */
                if ($concepto->activo) {

                    $concepto->update([
                        'activo' => false,
                    ]);

                    return false;
                }

                /*
                * Comprobar la configuración obligatoria.
                */
                if (
                    ! in_array(
                        $concepto->forma_importe,
                        $reglas['formas'],
                        true
                    )
                    || ! in_array(
                        $concepto->periodicidad,
                        $reglas['periodicidades'],
                        true
                    )
                    || ! in_array(
                        $concepto->naturaleza,
                        $reglas['naturalezas'],
                        true
                    )
                    || $concepto->requiere_afiliado === null
                    || (
                        $reglas['afiliado_obligatorio']
                        && ! (bool) $concepto->requiere_afiliado
                    )
                ) {
                    throw new DomainException(
                        'La configuración de '
                        . $concepto->nombre
                        . ' no es válida. Revise importe, periodicidad, '
                        . 'naturaleza y afiliado obligatorio.'
                    );
                }

                /*
                * Comprobar que exista una tarifa aplicable
                * a la fecha actual.
                */
                if ($concepto->forma_importe === 'FIJO') {

                    $hoy = now('America/La_Paz')->toDateString();

                    $tarifa = TarifaConceptoIngreso::query()
                        ->where('team_id', $teamId)
                        ->where('concepto_ingreso_id', $concepto->id)
                        ->whereDate('vigente_desde', '<=', $hoy)
                        ->orderByDesc('vigente_desde')
                        ->first();

                    if (
                        $tarifa === null
                        || (float) $tarifa->importe <= 0
                    ) {
                        throw new DomainException(
                            $concepto->nombre
                            . ' no tiene una tarifa vigente válida.'
                        );
                    }
                }

                /*
                * Habilitar únicamente el concepto.
                * No activa el interruptor global de cobros.
                */
                $concepto->update([
                    'activo' => true,
                ]);

                return true;

            }, 3);

            $this->mensaje = $activado
                ? 'Certificaciones fue habilitado correctamente.'
                : 'Certificaciones fue deshabilitado correctamente.';

        } catch (DomainException $e) {

            $this->errorActivacion = $e->getMessage();
        }
    }
    public function render()
    {
        Gate::authorize('administrar-conceptos-ingreso');

        $conceptos = ConceptoIngreso::query()
            ->where('team_id', $this->teamId())
            ->orderBy('nombre')
            ->get();

        return view(
            'livewire.ingresos.conceptos-ingreso',
            ['conceptos' => $conceptos]
        );
    }
}