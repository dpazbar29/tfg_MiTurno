<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Empleado;
use Illuminate\Database\Seeder;

class EmpleadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Empleado::create([
            'usuario_id' => 2,
            'especialidades' => 'Cortes y tintes',
            'fecha_contratacion' => '2026-04-28',
        ]);

        Empleado::create([
            'usuario_id' => 3,
            'especialidades' => 'Degradados y barbas',
            'fecha_contratacion' => '2026-04-28',
        ]);
    }
}
