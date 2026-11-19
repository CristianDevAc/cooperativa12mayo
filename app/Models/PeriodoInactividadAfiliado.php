<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodoInactividadAfiliado extends Model
{
    protected $table = 'periodos_inactividad_afiliados';

    protected $fillable = [
        'team_id',
        'afiliado_id',
        'periodo_desde',
        'periodo_hasta',
        'motivo',
        'registrado_por',
        'reactivado_por',
    ];

    protected function casts(): array
    {
        return [
            'periodo_desde' => 'date:Y-m-d',
            'periodo_hasta' => 'date:Y-m-d',
        ];
    }

    public function afiliado(): BelongsTo
    {
        return $this->belongsTo(Afiliado::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function reactivadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reactivado_por');
    }
}