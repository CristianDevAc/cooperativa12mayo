<?php

namespace App\Services\Egresos;

use App\Models\ConceptoEgreso;
use App\Models\Egreso;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RegistrarEgresoService
{
    /**
     * Registra un egreso pagado.
     *
     * $datos:
     * concepto_egreso_id
     * beneficiario
     * detalle
     * importe
     * medio_pago
     * referencia_pago
     * tipo_documento
     * numero_documento
     * observaciones
     * operacion_uuid
     */
    public function registrar(array $datos): Egreso
    {
        Gate::authorize('registrar-egresos');

        $usuario = Auth::user();

        abort_unless(
            $usuario instanceof User
            && $usuario->current_team_id !== null,
            403
        );

        if (! config('egresos.pagos_habilitados', false)) {
            throw new DomainException(
                'El registro definitivo de egresos no está habilitado.'
            );
        }

        $teamId = (int) $usuario->current_team_id;

        /*
         * Normalizar los campos recibidos.
         */
        $datos = [
            'concepto_egreso_id' => $datos['concepto_egreso_id'] ?? null,
            'beneficiario' => trim((string) ($datos['beneficiario'] ?? '')),
            'detalle' => trim((string) ($datos['detalle'] ?? '')),
            'importe' => trim((string) ($datos['importe'] ?? '')),
            'medio_pago' => $datos['medio_pago'] ?? null,
            'referencia_pago' => trim((string) ($datos['referencia_pago'] ?? '')),
            'tipo_documento' => $datos['tipo_documento'] ?: null,
            'numero_documento' => trim((string) ($datos['numero_documento'] ?? '')),
            'observaciones' => trim((string) ($datos['observaciones'] ?? '')),
            'operacion_uuid' => $datos['operacion_uuid'] ?? null,
        ];

        /*
         * Validaciones generales.
         */
        Validator::make($datos, [
            'concepto_egreso_id' => [
                'required',
                'integer',
                'min:1',
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
                'string',
            ],
            'medio_pago' => [
                'required',
                Rule::in([
                    'EFECTIVO',
                    'TRANSFERENCIA',
                    'DEPOSITO',
                    'OTRO',
                ]),
            ],
            'referencia_pago' => [
                'nullable',
                'string',
                'max:150',
            ],
            'tipo_documento' => [
                'nullable',
                Rule::in([
                    'FACTURA',
                    'RECIBO',
                    'OTRO',
                ]),
            ],
            'numero_documento' => [
                'nullable',
                'string',
                'max:100',
            ],
            'observaciones' => [
                'nullable',
                'string',
                'max:500',
            ],
            'operacion_uuid' => [
                'required',
                'uuid',
            ],
        ])->validate();

        /*
         * Validar el importe sin utilizar números float.
         */
        $importe = $this->normalizarImporte(
            $datos['importe']
        );

        /*
         * Para pagos bancarios exigimos una referencia.
         */
        if (
            in_array(
                $datos['medio_pago'],
                ['TRANSFERENCIA', 'DEPOSITO'],
                true
            )
            && $datos['referencia_pago'] === ''
        ) {
            throw new DomainException(
                'Debe indicar la referencia del pago bancario.'
            );
        }

        /*
         * Si se identifica un documento de respaldo,
         * debe registrarse también su número.
         */
        if (
            $datos['tipo_documento'] !== null
            && $datos['numero_documento'] === ''
        ) {
            throw new DomainException(
                'Debe indicar el número del documento de respaldo.'
            );
        }

        if (
            $datos['tipo_documento'] === null
            && $datos['numero_documento'] !== ''
        ) {
            throw new DomainException(
                'Seleccione el tipo de documento de respaldo.'
            );
        }

        return DB::transaction(function () use (
            $teamId,
            $usuario,
            $datos,
            $importe
        ) {

            /*
             * Numeración independiente de ingresos:
             * EGR-000001, EGR-000002...
             *
             * El bloqueo serializa el registro
             * de egresos de una misma cooperativa.
             */
            DB::table('numeradores_recibo')->insertOrIgnore([
                'team_id' => $teamId,
                'tipo' => 'EGRESO',
                'ultimo_numero' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $numerador = DB::table('numeradores_recibo')
                ->where('team_id', $teamId)
                ->where('tipo', 'EGRESO')
                ->lockForUpdate()
                ->first();

            if ($numerador === null) {
                throw new DomainException(
                    'No fue posible obtener el numerador de egresos.'
                );
            }

            /*
             * Protección contra solicitudes duplicadas.
             */
            $existente = Egreso::query()
                ->where('team_id', $teamId)
                ->where('operacion_uuid', $datos['operacion_uuid'])
                ->first();

            if ($existente !== null) {

                $coincide =
                    (int) $existente->concepto_egreso_id
                        === (int) $datos['concepto_egreso_id']
                    && (int) $existente->registrado_por
                        === (int) $usuario->id
                    && $existente->beneficiario
                        === $datos['beneficiario']
                    && $existente->detalle
                        === $datos['detalle']
                    && (string) $existente->importe
                        === $importe
                    && $existente->medio_pago
                        === $datos['medio_pago']
                    && (string) ($existente->referencia_pago ?? '')
                        === $datos['referencia_pago']
                    && $existente->tipo_documento
                        === $datos['tipo_documento']
                    && (string) ($existente->numero_documento ?? '')
                        === $datos['numero_documento']
                    && (string) ($existente->observaciones ?? '')
                        === $datos['observaciones'];

                if (! $coincide) {
                    throw new DomainException(
                        'Esta operación ya fue utilizada con otros datos.'
                    );
                }

                return $existente;
            }

            /*
             * El concepto debe pertenecer a la cooperativa
             * y encontrarse habilitado.
             */
            $concepto = ConceptoEgreso::query()
                ->where('team_id', $teamId)
                ->lockForUpdate()
                ->findOrFail(
                    (int) $datos['concepto_egreso_id']
                );

            if (! $concepto->activo) {
                throw new DomainException(
                    'Este concepto de egreso no está habilitado.'
                );
            }

            if (! in_array(
                $concepto->grupo_reporte,
                ['ORDINARIO', 'OTROS'],
                true
            )) {
                throw new DomainException(
                    'El concepto tiene una clasificación inválida.'
                );
            }

            $nuevoNumero =
                (int) $numerador->ultimo_numero + 1;

            /*
             * Registrar el egreso.
             */
            $egreso = Egreso::create([
                'team_id' => $teamId,
                'concepto_egreso_id' => $concepto->id,
                'numero_comprobante' => $nuevoNumero,
                'operacion_uuid' => $datos['operacion_uuid'],
                'fecha_pago' => now('America/La_Paz'),
                'concepto_codigo' => $concepto->codigo,
                'concepto_nombre' => $concepto->nombre,
                'grupo_reporte' => $concepto->grupo_reporte,
                'beneficiario' => $datos['beneficiario'],
                'detalle' => $datos['detalle'],
                'importe' => $importe,
                'medio_pago' => $datos['medio_pago'],
                'referencia_pago' =>
                    $datos['referencia_pago'] ?: null,
                'tipo_documento' => ($datos['tipo_documento'] ?? null) ?: null,
                'numero_documento' =>
                    $datos['numero_documento'] ?: null,
                'observaciones' =>
                    $datos['observaciones'] ?: null,
                'estado' => 'PAGADO',
                'registrado_por' => $usuario->id,
            ]);

            /*
             * Actualizar el correlativo.
             */
            DB::table('numeradores_recibo')
                ->where('team_id', $teamId)
                ->where('tipo', 'EGRESO')
                ->update([
                    'ultimo_numero' => $nuevoNumero,
                    'updated_at' => now(),
                ]);

            return $egreso;

        }, 3);
    }

    /**
     * Importe positivo con máximo dos decimales.
     */
    private function normalizarImporte(string $valor): string
    {
        $valor = trim($valor);

        if (! preg_match(
            '/^\d{1,10}(?:\.\d{1,2})?$/D',
            $valor
        )) {
            throw new DomainException(
                'El importe debe utilizar punto decimal '
                . 'y tener como máximo dos decimales.'
            );
        }

        $partes = explode('.', $valor, 2);

        $entero = ltrim($partes[0], '0');

        $entero = $entero === '' ? '0' : $entero;

        $decimales = str_pad(
            $partes[1] ?? '',
            2,
            '0'
        );

        if ($entero === '0' && $decimales === '00') {
            throw new DomainException(
                'El importe debe ser mayor que cero.'
            );
        }

        return $entero . '.' . $decimales;
    }
}