<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;


class Afiliado extends Model
{
    protected $table = 'afiliados';

    protected $fillable = [
        'team_id',
        'grupo_id',
        'numero_socio',
        'nombre_completo',
        'ci',
        'telefono',
        'direccion',
        'fecha_ingreso',
        'estado',
        'observaciones',
        'fecha_nacimiento',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'fecha_ingreso' => 'date',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }
    public function historialVehiculos(): HasMany
    {
        return $this->hasMany(HistorialVehiculo::class);
    }

    public function asignacionVigente(): HasOne
    {
        return $this->hasOne(HistorialVehiculo::class)
            ->whereNull('fecha_fin');
    }
    public function recibosHojaRuta(): HasMany
    {
        return $this->hasMany(
            ReciboHojaRuta::class,
            'afiliado_id'
        );
    }

    public function mensualidadesPagadas(): HasMany
    {
        return $this->hasMany(
            ReciboHojaRutaDetalle::class,
            'afiliado_id'
        )->where('vigente', true);
    }
    public function periodosInactividad(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PeriodoInactividadAfiliado::class);
    }
}