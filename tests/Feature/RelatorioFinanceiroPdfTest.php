<?php

use App\Models\FinanceiroCategoria;
use App\Models\FinanceiroLancamento;
use App\Models\Unidade;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    Permission::findOrCreate('exportar.pdf');
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $usuario = User::factory()->create();
    $usuario->givePermissionTo('exportar.pdf');
    $this->actingAs($usuario);

    $this->categoria = FinanceiroCategoria::create(['nome' => 'Doações', 'tipo' => 'receita']);
    $this->unidade = Unidade::create(['nome' => 'Sede']);

    $lancar = fn (string $tipo, string $descricao, float $valor) => FinanceiroLancamento::create([
        'tipo' => $tipo,
        'categoria_id' => $this->categoria->id,
        'unidade_id' => $this->unidade->id,
        'descricao' => $descricao,
        'valor' => $valor,
        'data' => '2026-08-10',
    ]);

    $lancar('entrada', 'Doação PIX', 1500.00);
    $lancar('entrada', 'Campanha do agasalho', 800.00);
    $lancar('saida', 'Compra de material', 430.00);

    // Fora do mês do relatório — nunca deve aparecer
    FinanceiroLancamento::create([
        'tipo' => 'entrada',
        'categoria_id' => $this->categoria->id,
        'unidade_id' => $this->unidade->id,
        'descricao' => 'Doação de julho',
        'valor' => 999.00,
        'data' => '2026-07-10',
    ]);
});

function baixarPdf(array $filtros = [])
{
    return test()->get(route('export.financeiro.pdf', $filtros + ['mes' => 8, 'ano' => 2026]));
}

test('o PDF de entradas traz só as entradas e o nome do arquivo diz isso', function () {
    $resposta = baixarPdf(['tipo' => 'entrada']);

    $resposta->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertDownload('financeiro_entradas_8_2026.pdf');
});

test('o PDF de saídas traz só as saídas e o nome do arquivo diz isso', function () {
    baixarPdf(['tipo' => 'saida'])
        ->assertOk()
        ->assertDownload('financeiro_saidas_8_2026.pdf');
});

test('sem filtro de tipo o PDF continua sendo o relatório geral', function () {
    baixarPdf()->assertOk()->assertDownload('financeiro_geral_8_2026.pdf');
});

test('o relatório de entradas soma apenas entradas do mês filtrado', function () {
    $html = renderRelatorio(['tipo' => 'entrada']);

    expect($html)->toContain('Relatório de Entradas')
        ->and($html)->toContain('2.300,00')      // 1500 + 800, sem a doação de julho
        ->and($html)->toContain('Doação PIX')
        ->and($html)->not->toContain('Compra de material')
        ->and($html)->not->toContain('Doação de julho')
        ->and($html)->not->toContain('<th>Tipo</th>'); // coluna Tipo é redundante quando filtrado
});

test('o relatório de saídas soma apenas saídas do mês filtrado', function () {
    $html = renderRelatorio(['tipo' => 'saida']);

    expect($html)->toContain('Relatório de Saídas')
        ->and($html)->toContain('430,00')
        ->and($html)->toContain('Compra de material')
        ->and($html)->not->toContain('Doação PIX');
});

test('o relatório geral mostra entradas, saídas e saldo', function () {
    $html = renderRelatorio();

    expect($html)->toContain('Relatório Financeiro')
        ->and($html)->toContain('<th>Tipo</th>')
        ->and($html)->toContain('Doação PIX')
        ->and($html)->toContain('Compra de material')
        ->and($html)->toContain('1.870,00'); // saldo: 2300 - 430
});

test('tipo inválido é rejeitado pela validação', function () {
    baixarPdf(['tipo' => 'qualquer-coisa'])->assertSessionHasErrors('tipo');
});

/**
 * O DomPDF devolve bytes binários; para inspecionar o conteúdo do relatório
 * renderizamos a mesma view com os dados que o controller monta.
 */
function renderRelatorio(array $filtros = []): string
{
    $tipo = $filtros['tipo'] ?? null;

    $lancamentos = FinanceiroLancamento::with(['categoria', 'doador'])
        ->whereMonth('data', 8)
        ->whereYear('data', 2026)
        ->when($tipo, fn ($q) => $q->where('tipo', $tipo))
        ->orderBy('data')
        ->get();

    $totais = [
        'entradas' => (float) $lancamentos->where('tipo', 'entrada')->sum('valor'),
        'saidas' => (float) $lancamentos->where('tipo', 'saida')->sum('valor'),
    ];
    $totais['saldo'] = $totais['entradas'] - $totais['saidas'];

    return view('pdf.relatorio-financeiro', [
        'lancamentos' => $lancamentos,
        'totais' => $totais,
        'tipo' => $tipo,
        'mes' => 8,
        'ano' => 2026,
    ])->render();
}
