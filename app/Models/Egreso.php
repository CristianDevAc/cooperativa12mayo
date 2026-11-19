<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Egreso extends Model
{
    protected $table = 'egresos';

    protected $fillable = [
        'team_id',
        'concepto_egreso_id',
        'numero_comprobante',
        'operacion_uuid',
        'fecha_pago',
        'concepto_codigo',
        'concepto_nombre',
        'grupo_reporte',
        'beneficiario',
        'detalle',
        'importe',
        'medio_pago',
        'referencia_pago',
        'tipo_documento',
        'numero_documento',
        'observaciones',
        'estado',
        'registrado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_pago' => 'datetime',
            'importe' => 'decimal:2',
            'anulado_en' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function concepto(): BelongsTo
    {
        return $this->belongsTo(
            ConceptoEgreso::class,
            'concepto_egreso_id'
        );
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'registrado_por'
        );
    }
    public function anuladoPor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'anulado_por'
        );
    }
}