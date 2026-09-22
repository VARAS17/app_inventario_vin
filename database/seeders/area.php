<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class area extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = [
            ['id' => 1, 'nombre' => 'ALMACEN'],
            ['id' => 2, 'nombre' => 'DESPACHO VICERRECTORAL'],
            ['id' => 3, 'nombre' => 'IMAGEN'],
            ['id' => 4, 'nombre' => 'TECNOLOGIAS DE LA INFORMACION'],
            ['id' => 5, 'nombre' => 'ADMINISTRACION'],
            ['id' => 6, 'nombre' => 'MESA DE PARTES'],
            ['id' => 7, 'nombre' => 'SECRETARIA'],
        ];

        foreach ($areas as $a) {
            DB::table('areas')->updateOrInsert(
                ['id' => $a['id']],
                [
                    'nombre'     => $a['nombre'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}