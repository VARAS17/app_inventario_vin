<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Personal;

class PersonalSeeder extends Seeder
{
    public function run(): void
    {
        $personal = [
            [
                'nombre' => 'Jhon',
                'apellido' => 'Moya',
                'cargo' => 'TI de VIN',
                'grado_academico' => 'Bachiller',
                'foto_perfil' => null
            ],
            [
                'nombre' => 'Erika',
                'apellido' => 'Diaz',
                'cargo' => 'Admin de VIN',
                'grado_academico' => 'Técnica',
                'foto_perfil' => null
            ],
            [
                'nombre' => 'Margot',
                'apellido' => 'Sanchez',
                'cargo' => 'Locadora',
                'grado_academico' => 'Bachiller',
                'foto_perfil' => null
            ],
            [
                'nombre' => 'Jose',
                'apellido' => 'Varas',
                'cargo' => 'Practicante',
                'grado_academico' => 'Bachiller',
                'foto_perfil' => null
            ],
            [
                'nombre' => 'Luis',
                'apellido' => 'Celi',
                'cargo' => 'Mesa de parte VIN',
                'grado_academico' => 'Técnico',
                'foto_perfil' => null
            ],
            [
                'nombre' => 'Marleny',
                'apellido' => 'Paredes',
                'cargo' => 'Secretaria VIN',
                'grado_academico' => 'Licenciada',
                'foto_perfil' => null
            ],
            [
                'nombre' => 'Victor',
                'apellido' => 'Castro',
                'cargo' => 'Imagen VIN',
                'grado_academico' => 'Bachiller',
                'foto_perfil' => null
            ],
            [
                'nombre' => 'Guillermo',
                'apellido' => 'Paredes',
                'cargo' => 'Asesor VIN',
                'grado_academico' => 'Licenciado',
                'foto_perfil' => null
            ],
            [
                'nombre' => 'Victor',
                'apellido' => 'Lau',
                'cargo' => 'Vicerrector de Investigación',
                'grado_academico' => 'Doctor',
                'foto_perfil' => null
            ],
        ];

        foreach ($personal as $data) {
            Personal::create($data);
        }
    }
}