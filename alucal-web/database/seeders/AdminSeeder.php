<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) env('ALUCAL_ADMIN_EMAIL', 'admin@alucal.local');
        $password = (string) env('ALUCAL_ADMIN_PASSWORD', 'alucal');

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Administrador',
                'nombre' => 'Administrador',
                'rol' => 'admin',
                'activo' => true,
                'password' => Hash::make($password),
            ]
        );

        $this->command?->info("Usuario administrador: {$email}");
    }
}
