<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConceptoIngreso extends Model
{
    protected $table = 'conceptos_ingreso';

    protected $fillable = [
        'team_id',
        'codigo',
        'nombre',
        'forma_importe',
        'periodicidad',
        'naturaleza',
        'requiere_afiliado',
        'activo',
        'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'requiere_afiliado' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}