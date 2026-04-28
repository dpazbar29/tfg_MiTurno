<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'nombre' => 'Administrador',
            'apellidos' => 'Mi Turno',
            'email' => 'adminmiturno@gmail.com',
            'password' => Hash::make('adminmiturno01'),
            'rol' => 'admin',
            'telefono' => '666333999',
        ]);

        User::create([
            'nombre' => 'Empleado',
            'apellidos' => 'Numero Uno',
            'email' => 'empleadouno@gmail.com',
            'password' => Hash::make('empleadouno01'),
            'rol' => 'empleado',
            'telefono' => '333666999',
        ]);

        User::create([
            'nombre' => 'Empleado',
            'apellidos' => 'Numero Dos',
            'email' => 'empleadodos@gmail.com',
            'password' => Hash::make('empleadodos01'),
            'rol' => 'empleado',
            'telefono' => '999333666',
        ]);

        User::create([
            'nombre' => 'Daniel',
            'apellidos' => 'Paz Barroso',
            'email' => 'danielpazbar@gmail.com',
            'password' => Hash::make('danielpazbar01'),
            'rol' => 'cliente',
            'telefono' => '111222333',
        ]);
    }
}
