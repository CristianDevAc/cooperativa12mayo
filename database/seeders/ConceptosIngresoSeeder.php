<?php

namespace Database\Seeders;

use App\Models\ConceptoIngreso;
use App\Models\Team;
use Illuminate\Database\Seeder;

class ConceptosIngresoSeeder extends Seeder
{
    public function run(): void
    {
        $conceptos = [
            'FONDO_SOCIAL' => 'Fondo Social',
            'CUOTA_INGRESO' => 'Cuota de Ingreso',
            'CAMBIO_HERRAMIENTA' => 'Cambio de Herramienta',
            'REINCORPORACION' => 'Reincorporaciones',
            'CERTIFICACION' => 'Certificaciones',
            'LOGOTIPO' => 'Logotipos',
            'OTROS_INGRESOS' => 'Otros Ingresos',
        ];

        foreach (Team::query()->pluck('id') as $teamId) {

            foreach ($conceptos as $codigo => $nombre) {

                ConceptoIngreso::firstOrCreate(
                    [
                        'team_id' => (int) $teamId,
                        'codigo' => $codigo,
                    ],
                    [
                        'nombre' => $nombre,
                        'activo' => false,
                    ]
                );
            }
        }
    }
}