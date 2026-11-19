<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReciboHojaRutaDetalle extends Model
{
    protected $table = 'recibo_hoja_ruta_detalles';

    protected $fillable = [
        'team_id',
        'recibo_hoja_ruta_id',
        'afiliado_id',
        'vehiculo_id',
        'tarifa_hoja_ruta_id',
        'periodo',
        'placa',
        'importe',
        'vigente',
    ];

    protected function casts(): array
    {
        return [
            'periodo' => 'date',
            'importe' => 'decimal:2',
            'vigente' => 'boolean',
        ];
    }

    public function recibo(): BelongsTo
    {
        return $this->belongsTo(
            ReciboHojaRuta::class,
            'recibo_hoja_ruta_id'
        );
    }

    public function afiliado(): BelongsTo
    {
        return $this->belongsTo(Afiliado::class);
    }

    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class);
    }

    public function tarifa(): BelongsTo
    {
        return $this->belongsTo(
            TarifaHojaRuta::class,
            'tarifa_hoja_ruta_id'
        );
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}