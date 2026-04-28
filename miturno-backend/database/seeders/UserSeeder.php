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
        User::updateOrCreate(
            ['email' => 'adminmiturno@gmail.com'],
            [
                'nombre' => 'Administrador',
                'apellidos' => 'Mi Turno',
                'password' => Hash::make('adminmiturno01'),
                'rol' => 'admin',
                'telefono' => '666333999',
                'activo' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'empleadouno@gmail.com'],
            [
                'nombre' => 'Empleado',
                'apellidos' => 'Numero Uno',
                'password' => Hash::make('empleadouno01'),
                'rol' => 'empleado',
                'telefono' => '333666999',
                'activo' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'empleadodos@gmail.com'],
            [
                'nombre' => 'Empleado',
                'apellidos' => 'Numero Dos',
                'password' => Hash::make('empleadodos01'),
                'rol' => 'empleado',
                'telefono' => '999333666',
                'activo' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'danielpazbar@gmail.com'],
            [
                'nombre' => 'Daniel',
                'apellidos' => 'Paz Barroso',
                'password' => Hash::make('danielpazbar01'),
                'rol' => 'cliente',
                'telefono' => '111222333',
                'activo' => true,
            ]
        );
    }
}
