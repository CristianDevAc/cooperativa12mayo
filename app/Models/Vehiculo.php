<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Vehiculo extends Model
{
    protected $table = 'vehiculos';

    protected $fillable = [
        'team_id',
        'placa',
        'marca',
        'modelo',
        'tipo',
        'anio',
        'color',
        'activo',
        'foto_path',
    ];

    protected function casts(): array
    {
        return [
            'anio' => 'integer',
            'activo' => 'boolean',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function historial(): HasMany
    {
        return $this->hasMany(HistorialVehiculo::class);
    }

    public function asignacionVigente(): HasOne
    {
        return $this->hasOne(HistorialVehiculo::class)
            ->whereNull('fecha_fin');
    }
}