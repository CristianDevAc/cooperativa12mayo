<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NumeradorRecibo extends Model
{
    protected $table = 'numeradores_recibo';

    protected $fillable = [
        'team_id',
        'tipo',
        'ultimo_numero',
    ];

    protected function casts(): array
    {
        return [
            'ultimo_numero' => 'integer',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}