<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Seed de produção: cria o usuário admin se não existir.
     * Seguro para rodar múltiplas vezes (idempotente).
     *
     * As credenciais NUNCA são fixadas em código — vêm de ADMIN_EMAIL e
     * ADMIN_PASSWORD (.env local / variáveis do EasyPanel). Sem ADMIN_PASSWORD,
     * uma senha aleatória é gerada e exibida uma única vez no terminal.
     */
    public function run(): void
    {
        // Garante que roles/permissions existem
        $this->call(RolePermissionSeeder::class);

        $email = config('admin.email');

        if (blank($email)) {
            $this->command->error('❌ ADMIN_EMAIL não definido. Configure a variável antes de rodar o AdminSeeder.');

            return;
        }

        $senha = config('admin.password');
        $senhaGerada = blank($senha);

        if ($senhaGerada) {
            $senha = Str::password(16);
        }

        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => config('admin.name'),
                'password' => Hash::make($senha),
                'email_verified_at' => now(),
            ]
        );

        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $this->command->info("✅ Usuário admin criado/verificado: {$email}");

        if ($senhaGerada && $admin->wasRecentlyCreated) {
            $this->command->warn("🔑 Senha gerada (anote agora, não será exibida de novo): {$senha}");
        }
    }
}
