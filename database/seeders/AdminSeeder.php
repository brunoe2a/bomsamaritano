<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Seed de produção: cria o usuário admin se não existir.
     * Seguro para rodar múltiplas vezes (idempotente).
     */
    public function run(): void
    {
        // Garante que roles/permissions existem
        $this->call(RolePermissionSeeder::class);

        $admin = User::firstOrCreate(
            ['email' => 'bruno.e2a@gmail.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('joao0316'),
                'email_verified_at' => now(),
            ]
        );

        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $this->command->info('✅ Usuário admin criado/verificado: bruno.e2a@gmail.com');
    }
}
