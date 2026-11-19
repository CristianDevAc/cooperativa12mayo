<?php

namespace Database\Seeders;

use App\Models\ConceptoEgreso;
use App\Models\Team;
use Illuminate\Database\Seeder;

class ConceptosEgresoSeeder extends Seeder
{
    public function run(): void
    {
        $conceptos = [

            [
                'codigo' => 'SUELDOS',
                'nombre' => 'Sueldos',
                'grupo_reporte' => 'ORDINARIO',
                'descripcion' => 'Pago de remuneraciones al personal.',
            ],

            [
                'codigo' => 'GASTOS_GENERALES',
                'nombre' => 'Gastos Generales',
                'grupo_reporte' => 'ORDINARIO',
                'descripcion' => 'Gastos administrativos y operativos generales.',
            ],

            [
                'codigo' => 'SERVICIOS_BASICOS',
                'nombre' => 'Servicios Básicos',
                'grupo_reporte' => 'ORDINARIO',
                'descripcion' => 'Pagos de agua, luz, internet y otros servicios.',
            ],

            [
                'codigo' => 'COMISIONES',
                'nombre' => 'Comisiones',
                'grupo_reporte' => 'OTROS',
                'descripcion' => 'Pagos de comisiones autorizadas.',
            ],

            [
                'codigo' => 'APOYO_ECONOMICO',
                'nombre' => 'Apoyo Económico',
                'grupo_reporte' => 'OTROS',
                'descripcion' => 'Apoyos económicos autorizados por la cooperativa.',
            ],

            [
                'codigo' => 'OTROS_EGRESOS',
                'nombre' => 'Otros Egresos',
                'grupo_reporte' => 'OTROS',
                'descripcion' => 'Gastos que no corresponden a las otras categorías.',
            ],
        ];

        foreach (Team::query()->pluck('id') as $teamId) {

            foreach ($conceptos as $concepto) {

                ConceptoEgreso::firstOrCreate(
                    [
                        'team_id' => $teamId,
                        'codigo' => $concepto['codigo'],
                    ],
                    [
                        'nombre' => $concepto['nombre'],
                        'grupo_reporte' => $concepto['grupo_reporte'],
                        'descripcion' => $concepto['descripcion'],
                        'activo' => false,
                    ]
                );
            }
        }
    }
}