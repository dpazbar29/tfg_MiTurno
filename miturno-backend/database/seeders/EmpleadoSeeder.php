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
        $empleadoUnoUser = \App\Models\User::where('email', 'empleadouno@gmail.com')->first();
        $empleadoDosUser = \App\Models\User::where('email', 'empleadodos@gmail.com')->first();

        if ($empleadoUnoUser) {
            Empleado::updateOrCreate(
                ['usuario_id' => $empleadoUnoUser->id],
                [
                    'especialidades' => 'Cortes y tintes',
                    'fecha_contratacion' => '2026-04-28',
                    'activo' => true,
                ]
            );
        }

        if ($empleadoDosUser) {
            Empleado::updateOrCreate(
                ['usuario_id' => $empleadoDosUser->id],
                [
                    'especialidades' => 'Degradados y barbas',
                    'fecha_contratacion' => '2026-04-28',
                    'activo' => true,
                ]
            );
        }
    }
}
