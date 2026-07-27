<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'nombre' => 'Administrador',
            'apellido' => 'Dirección',
            'cargo' => 'Jefe de Oficina',
            'email' => 'admin@universidad.edu',
            'password' => Hash::make('admin123'), // Esta será tu contraseña
        ]);

        $this->call([
        PersonalSeeder::class,
        tecno::class,
        DebajaSeeder::class,
    ]);
    }
}