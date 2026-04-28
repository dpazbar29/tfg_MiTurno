<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Servicio;
use Illuminate\Database\Seeder;

class ServicioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Servicio::create([
            'nombre' => 'Corte básico',
            'descripcion' => 'Servicio básico de corte de pelo',
            'duracion_minutos' => 30,
            'precio' => 12.50,
        ]);

        Servicio::create([
            'nombre' => 'Corte y lavado',
            'descripcion' => 'Corte de pelo y lavado final',
            'duracion_minutos' => 30,
            'precio' => 18.00,
        ]);

        Servicio::create([
            'nombre' => 'Tinte',
            'descripcion' => 'Aplicación de color',
            'duracion_minutos' => 30,
            'precio' => 30.00,
        ]);

        Servicio::create([
            'nombre' => 'Tratamiento capilar',
            'descripcion' => 'Tratamiento de hidratación y reparación',
            'duracion_minutos' => 30,
            'precio' => 20.00,
        ]);
    }
}
