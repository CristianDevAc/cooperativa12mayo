<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudReincorporacion extends Model
{
    protected $table = 'solicitudes_reincorporacion';

    protected $fillable = [
        'team_id',
        'afiliado_id',
        'fecha_solicitud',
        'observaciones',
        'registrado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_solicitud' => 'datetime',
            'cerrada_en' => 'datetime',
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

    public function recibo(): BelongsTo
    {
        return $this->belongsTo(
            ReciboOtroIngreso::class,
            'recibo_otro_ingreso_id'
        );
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'registrado_por'
        );
    }
}