<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Usuário administrador inicial
    |--------------------------------------------------------------------------
    |
    | Credenciais usadas pelo AdminSeeder para criar o primeiro acesso ao
    | sistema. Nunca fixe valores aqui — defina no .env local ou nas variáveis
    | de ambiente do EasyPanel. Sem ADMIN_PASSWORD, o seeder gera uma senha
    | aleatória e a exibe uma única vez no terminal.
    |
    */

    'name' => env('ADMIN_NAME', 'Administrador'),

    'email' => env('ADMIN_EMAIL'),

    'password' => env('ADMIN_PASSWORD'),

];
