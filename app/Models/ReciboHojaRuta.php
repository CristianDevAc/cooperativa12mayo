<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReciboHojaRuta extends Model
{
    protected $table = 'recibos_hoja_ruta';

    protected $fillable = [
        'team_id',
        'afiliado_id',
        'numero_recibo',
        'fecha_cobro',
        'subtotal',
        'multa_unica',
        'total',
        'estado',
        'registrado_por',
        'anulado_en',
        'anulado_por',
        'motivo_anulacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_cobro' => 'datetime',
            'subtotal' => 'decimal:2',
            'multa_unica' => 'decimal:2',
            'total' => 'decimal:2',
            'anulado_en' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function afiliado(): BelongsTo
    {
        return $this->belongsTo(Afiliado::class);
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

    public function detalles(): HasMany
    {
        return $this->hasMany(
            ReciboHojaRutaDetalle::class,
            'recibo_hoja_ruta_id'
        );
    }
}