<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
Use app\Models\Debaja;

class DebajaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('debajas')->insert([
            ['id' => 1, 'nombre' => 'Placa madre', 'tipo_inventario' => 'Tecnología', 'motivo' => 'Dar de Baja', 'fecha_baja' => '2026-07-24 11:42:55', 'personal_id' => null, 'detalles' => '{"marca":"F","serie":"","lugar":"Sala Reuniones\\/ropero 1"}', 'imagen' => null, 'created_at' => '2026-07-24 11:42:55', 'updated_at' => '2026-07-24 11:42:55'],
            ['id' => 2, 'nombre' => 'Laptop', 'tipo_inventario' => 'Tecnología', 'motivo' => 'Dar de Baja', 'fecha_baja' => '2026-07-24 12:40:58', 'personal_id' => null, 'detalles' => '{"marca":"Thinkepat","serie":"","lugar":"Almacen"}', 'imagen' => null, 'created_at' => '2026-07-24 12:40:58', 'updated_at' => '2026-07-24 12:40:58'],
        ]);
    }
}