<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\HistorialVehiculo;

class ReciboOtroIngreso extends Model
{
    protected $table = 'recibos_otros_ingresos';

    protected $fillable = [
        'team_id',
        'concepto_ingreso_id',
        'afiliado_id',
        'tarifa_concepto_ingreso_id',
        'numero_recibo',
        'fecha_cobro',
        'periodo',
        'concepto_codigo',
        'concepto_nombre',
        'forma_importe',
        'periodicidad',
        'naturaleza',
        'detalle',
        'importe',
        'estado',
        'registrado_por',
        'operacion_uuid',
        'historial_vehiculo_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_cobro' => 'datetime',
            'periodo' => 'date',
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

    public function afiliado(): BelongsTo
    {
        return $this->belongsTo(Afiliado::class);
    }

    public function tarifa(): BelongsTo
    {
        return $this->belongsTo(
            TarifaConceptoIngreso::class,
            'tarifa_concepto_ingreso_id'
        );
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'registrado_por'
        );
    }
    public function movimientoVehiculo(): BelongsTo
    {
        return $this->belongsTo(
            HistorialVehiculo::class,
            'historial_vehiculo_id'
        );
    }
}