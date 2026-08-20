<?php

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Support\Facades\Hash;

test('cria o admin com as credenciais vindas do ambiente', function () {
    config(['admin.email' => 'gestor@exemplo.org', 'admin.password' => 'senha-do-ambiente', 'admin.name' => 'Gestor']);

    $this->seed(AdminSeeder::class);

    $admin = User::where('email', 'gestor@exemplo.org')->sole();

    expect($admin->name)->toBe('Gestor')
        ->and($admin->hasRole('admin'))->toBeTrue()
        ->and(Hash::check('senha-do-ambiente', $admin->password))->toBeTrue();
});

test('não cria admin quando ADMIN_EMAIL não está definido', function () {
    config(['admin.email' => null]);

    $this->seed(AdminSeeder::class);

    expect(User::count())->toBe(0);
});

test('gera senha aleatória quando ADMIN_PASSWORD não está definido', function () {
    config(['admin.email' => 'gestor@exemplo.org', 'admin.password' => null]);

    $this->seed(AdminSeeder::class);

    $admin = User::where('email', 'gestor@exemplo.org')->sole();

    // Nenhuma senha previsível: o seeder não pode ter senha fixa em código
    foreach (['password', 'admin', '123456', 'bomsamaritano'] as $tentativa) {
        expect(Hash::check($tentativa, $admin->password))->toBeFalse();
    }

    expect($admin->password)->not->toBeEmpty();
});

test('rodar duas vezes não duplica nem troca a senha do admin', function () {
    config(['admin.email' => 'gestor@exemplo.org', 'admin.password' => 'senha-do-ambiente']);

    $this->seed(AdminSeeder::class);
    $hashOriginal = User::where('email', 'gestor@exemplo.org')->sole()->password;

    $this->seed(AdminSeeder::class);

    expect(User::where('email', 'gestor@exemplo.org')->count())->toBe(1)
        ->and(User::where('email', 'gestor@exemplo.org')->sole()->password)->toBe($hashOriginal);
});
