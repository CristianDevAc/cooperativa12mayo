<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConceptoEgreso extends Model
{
    protected $table = 'conceptos_egreso';

    protected $fillable = [
        'team_id',
        'codigo',
        'nombre',
        'grupo_reporte',
        'activo',
        'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}