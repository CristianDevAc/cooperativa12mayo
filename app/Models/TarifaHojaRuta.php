<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TarifaHojaRuta extends Model
{
    protected $table = 'tarifas_hoja_ruta';

    protected $fillable = [
        'team_id',
        'vigente_desde',
        'importe_base',
        'observaciones',
        'creado_por',
        'importe_multa',
    ];

    protected function casts(): array
    {
        return [
            'vigente_desde' => 'date',
            'importe_base' => 'decimal:2',
            'importe_multa' => 'decimal:2',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}