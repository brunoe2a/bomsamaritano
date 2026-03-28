<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoFilialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $u = \App\Models\Unidade::firstOrCreate(['nome' => 'Filial Centro']);
        
        $catR = \App\Models\FinanceiroCategoria::firstOrCreate(['nome' => 'Doações', 'tipo' => 'receita']);
        $catE = \App\Models\FinanceiroCategoria::firstOrCreate(['nome' => 'Eventos', 'tipo' => 'receita']);
        $catM = \App\Models\FinanceiroCategoria::firstOrCreate(['nome' => 'Manutenção', 'tipo' => 'despesa']);
        $catS = \App\Models\FinanceiroCategoria::firstOrCreate(['nome' => 'Salários', 'tipo' => 'despesa']);

        $user = \App\Models\User::first();

        // Dados para o mês atual
        \App\Models\FinanceiroLancamento::create([
            'unidade_id' => $u->id,
            'categoria_id' => $catR->id,
            'user_id' => $user->id ?? 1,
            'tipo' => 'entrada',
            'valor' => 7500.00,
            'data' => now()->format('Y-m-d'),
            'descricao' => 'Doações Mensais Filial'
        ]);

        \App\Models\FinanceiroLancamento::create([
            'unidade_id' => $u->id,
            'categoria_id' => $catE->id,
            'user_id' => $user->id ?? 1,
            'tipo' => 'entrada',
            'valor' => 3200.00,
            'data' => now()->format('Y-m-d'),
            'descricao' => 'Evento Beneficente Filial'
        ]);

        \App\Models\FinanceiroLancamento::create([
            'unidade_id' => $u->id,
            'categoria_id' => $catM->id,
            'user_id' => $user->id ?? 1,
            'tipo' => 'saida',
            'valor' => 4150.00,
            'data' => now()->format('Y-m-d'),
            'descricao' => 'Aluguel e Manutenção Filial'
        ]);

        \App\Models\FinanceiroLancamento::create([
            'unidade_id' => $u->id,
            'categoria_id' => $catS->id,
            'user_id' => $user->id ?? 1,
            'tipo' => 'saida',
            'valor' => 2800.00,
            'data' => now()->format('Y-m-d'),
            'descricao' => 'Folha de Pagamento Filial'
        ]);
    }
}
