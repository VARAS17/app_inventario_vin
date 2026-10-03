<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\utiles;
use App\Models\movimientos_utiles;
use Illuminate\Support\Facades\DB;

class UtilesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $utilesData = [
            // Papelería y Carpetas
            [
                'nombre'       => 'Papel Bond A4',
                'marca'        => '---',
                'unidad'       => 'Paquetes',
                'stock_actual' => 0,
                'stock_minimo' => 5,
            ],

            [
                'nombre'       => 'Archivador A4',
                'marca'        => 'Reyser',
                'unidad'       => 'Unidad',
                'stock_actual' => 0,
                'stock_minimo' => 5,
            ],
            [
                'nombre'       => 'Folder Manila A4',
                'marca'        => 'Vinifan',
                'unidad'       => 'Unidades',
                'stock_actual' => 0,
                'stock_minimo' => 5,
            ],
            [
                'nombre'       => 'Sobre Manila A4',
                'marca'        => 'Vinifan',
                'unidad'       => 'Paquetes',
                'stock_actual' => 0,
                'stock_minimo' => 5,
            ],

            // Bolígrafos y Escritura
            [
                'nombre'       => 'Lapicero Azul',
                'marca'        => 'Faber-Castell',
                'unidad'       => 'Unidad',
                'stock_actual' => 0,
                'stock_minimo' => 5,
            ],
            [
                'nombre'       => 'Lapicero Negro',
                'marca'        => 'Faber-Castell',
                'unidad'       => 'Unidad',
                'stock_actual' => 0,
                'stock_minimo' => 5,
            ],
            [
                'nombre'       => 'Lapicero Rojo',
                'marca'        => 'Faber-Castell',
                'unidad'       => 'Unidad',
                'stock_actual' => 0,
                'stock_minimo' => 5,
            ],
            [
                'nombre'       => 'Resaltador Amarillo',
                'marca'        => 'Faber-Castell',
                'unidad'       => 'Unidad',
                'stock_actual' => 0,
                'stock_minimo' => 5,
            ],
            [
                'nombre'       => 'Resaltador Verde',
                'marca'        => 'Faber-Castell',
                'unidad'       => 'Unidad',
                'stock_actual' => 0,
                'stock_minimo' => 5,
            ],
            [
                'nombre'       => 'Corrector Líquido',
                'marca'        => 'Artesco',
                'unidad'       => 'Unidad',
                'stock_actual' => 0,
                'stock_minimo' => 5,
            ],

            // Sujeción y Oficina
            [
                'nombre'       => 'Clips Metálicos',
                'marca'        => 'Artesco',
                'unidad'       => 'Cajas',
                'stock_actual' => 0,
                'stock_minimo' => 5,
            ],
            [
                'nombre'       => 'Clips Mariposa N° 2',
                'marca'        => 'Artesco',
                'unidad'       => 'Cajas',
                'stock_actual' => 15,
                'stock_minimo' => 4,
            ],
            [
                'nombre'       => 'Grapas 26/6 Cobreadas',
                'marca'        => 'Artesco',
                'unidad'       => 'Cajas',
                'stock_actual' => 18,
                'stock_minimo' => 5,
            ],
            [
                'nombre'       => 'Engrapador Metálico',
                'marca'        => 'Rapid',
                'unidad'       => 'Unidad',
                'stock_actual' => 6,
                'stock_minimo' => 2,
            ],
            [
                'nombre'       => 'Perforador de 2 Huecos',
                'marca'        => 'Artesco',
                'unidad'       => 'Unidad',
                'stock_actual' => 4,
                'stock_minimo' => 2,
            ],

            // Adhesivos y Varios
            [
                'nombre'       => 'Notas Adhesivas Post-it 3x3 Amarillo',
                'marca'        => '3M',
                'unidad'       => 'Paquetes',
                'stock_actual' => 20,
                'stock_minimo' => 5,
            ],
            [
                'nombre'       => 'Cinta de Embalaje Transparente 2" x 50yd',
                'marca'        => 'Shurtape',
                'unidad'       => 'Unidad',
                'stock_actual' => 8,
                'stock_minimo' => 3,
            ],
            [
                'nombre'       => 'Cinta Masking Tape 1" x 40yd',
                'marca'        => 'Shurtape',
                'unidad'       => 'Unidad',
                'stock_actual' => 10,
                'stock_minimo' => 3,
            ],
            [
                'nombre'       => 'Tijera de Oficina 8" Mango Plástico',
                'marca'        => 'Maped',
                'unidad'       => 'Unidad',
                'stock_actual' => 5,
                'stock_minimo' => 2,
            ],
        ];

        DB::beginTransaction();

        foreach ($utilesData as $item) {
            $util = utiles::create($item);

            // Registrar saldo inicial en Kardex si tiene stock
            if ($util->stock_actual > 0) {
                movimientos_utiles::create([
                    'util_id'         => $util->id,
                    'tipo'            => 'Saldo Inicial',
                    'cantidad'        => $util->stock_actual,
                    'stock_anterior'  => 0,
                    'stock_nuevo'     => $util->stock_actual,
                    'referencia_tipo' => 'Inventario Inicial',
                    'referencia_id'   => null,
                    'descripcion'     => 'Carga inicial del catálogo de oficina',
                    'fecha'           => now()->toDateString(),
                ]);
            }
        }

        DB::commit();
    }
}