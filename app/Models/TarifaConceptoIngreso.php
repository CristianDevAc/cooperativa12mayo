<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TarifaConceptoIngreso extends Model
{
    protected $table = 'tarifas_conceptos_ingreso';

    protected $fillable = [
        'team_id',
        'concepto_ingreso_id',
        'vigente_desde',
        'importe',
        'observaciones',
        'creado_por',
    ];

    protected function casts(): array
    {
        return [
            'vigente_desde' => 'date',
            'importe' => 'decimal:2',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function concepto(): BelongsTo
    {
        return $this->belongsTo(
            ConceptoIngreso::class,
            'concepto_ingreso_id'
        );
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'creado_por'
        );
    }
}