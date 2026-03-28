<?php

namespace App\Http\Controllers;

use App\Models\Doador;
use App\Models\FinanceiroCategoria;
use App\Models\FinanceiroLancamento;
use App\Models\Unidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class FinanceiroController extends Controller
{
    public function index(Request $request)
    {
        $query = FinanceiroLancamento::with(['categoria', 'doador', 'usuario', 'unidade']);

        if ($request->filled('busca')) {
            $query->where('descricao', 'like', "%{$request->busca}%");
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        if ($request->filled('mes')) {
            $query->whereMonth('data', $request->mes);
        }

        if ($request->filled('ano')) {
            $query->whereYear('data', $request->ano);
        }

        if ($request->filled('unidade_id')) {
            $query->where('unidade_id', $request->unidade_id);
        }

        $lancamentos = $query->latest('data')->paginate(20)->withQueryString();

        // Resumo financeiro do mês atual
        $mesAtual = $request->mes ?? now()->month;
        $anoAtual = $request->ano ?? now()->year;

        $resumo = FinanceiroLancamento::whereMonth('data', $mesAtual)
            ->whereYear('data', $anoAtual)
            ->selectRaw("
                SUM(CASE WHEN tipo = 'entrada' THEN valor ELSE 0 END) as entradas,
                SUM(CASE WHEN tipo = 'saida' THEN valor ELSE 0 END) as saidas
            ")
            ->first();

        return Inertia::render('Financeiro/Index', [
            'lancamentos' => $lancamentos,
            'filtros' => $request->only(['busca', 'tipo', 'categoria_id', 'mes', 'ano', 'unidade_id']),
            'categorias' => FinanceiroCategoria::all(),
            'unidades' => Unidade::all(),
            'resumo' => [
                'entradas' => (float) ($resumo->entradas ?? 0),
                'saidas' => (float) ($resumo->saidas ?? 0),
                'saldo' => (float) (($resumo->entradas ?? 0) - ($resumo->saidas ?? 0)),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Financeiro/Create', [
            'categorias' => FinanceiroCategoria::all(),
            'doadores' => Doador::select('id', 'nome', 'tipo')->get(),
            'unidades' => Unidade::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo' => 'required|in:entrada,saida',
            'categoria_id' => 'required|exists:financeiro_categorias,id',
            'doador_id' => 'nullable|exists:doadores,id',
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0.01',
            'data_lancamento' => 'required|date',
            'comprovante' => 'nullable|file|max:5120',
            'unidade_id' => 'required|exists:unidades,id',
            'observacoes' => 'nullable|string',
        ]);

        $validated['data'] = $validated['data_lancamento'];
        $validated['user_id'] = auth()->id();

        if ($request->hasFile('comprovante')) {
            $validated['comprovante'] = $request->file('comprovante')->store('financeiro/comprovantes', 'public');
        }

        FinanceiroLancamento::create($validated);

        return redirect()->route('financeiro.index')
            ->with('success', 'Lançamento registrado com sucesso!');
    }

    public function show(FinanceiroLancamento $financeiro)
    {
        return Inertia::render('Financeiro/Show', [
            'lancamento' => $financeiro->load(['categoria', 'doador', 'unidade', 'usuario']),
        ]);
    }

    public function edit(FinanceiroLancamento $financeiro)
    {
        return Inertia::render('Financeiro/Edit', [
            'lancamento' => $financeiro->load(['categoria', 'doador', 'unidade']),
            'categorias' => FinanceiroCategoria::all(),
            'doadores' => Doador::select('id', 'nome', 'tipo')->get(),
            'unidades' => Unidade::all(),
        ]);
    }

    public function update(Request $request, FinanceiroLancamento $financeiro)
    {
        $validated = $request->validate([
            'tipo' => 'required|in:entrada,saida',
            'categoria_id' => 'required|exists:financeiro_categorias,id',
            'doador_id' => 'nullable|exists:doadores,id',
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0.01',
            'data_lancamento' => 'required|date',
            'comprovante' => 'nullable|file|max:5120',
            'unidade_id' => 'required|exists:unidades,id',
            'observacoes' => 'nullable|string',
        ]);

        $validated['data'] = $validated['data_lancamento'];

        if ($request->hasFile('comprovante')) {
            if ($financeiro->comprovante) {
                Storage::disk('public')->delete($financeiro->comprovante);
            }
            $validated['comprovante'] = $request->file('comprovante')->store('financeiro/comprovantes', 'public');
        }

        $financeiro->update($validated);

        return redirect()->route('financeiro.index')
            ->with('success', 'Lançamento atualizado com sucesso!');
    }

    public function destroy(FinanceiroLancamento $financeiro)
    {
        if ($financeiro->comprovante) {
            Storage::disk('public')->delete($financeiro->comprovante);
        }
        $financeiro->delete();

        return redirect()->route('financeiro.index')
            ->with('success', 'Lançamento removido com sucesso!');
    }

    public function dashboard(Request $request)
    {
        $mes = $request->mes ?? now()->month;
        $ano = $request->ano ?? now()->year;
        $unidadeId = $request->unidade_id;

        $query = FinanceiroLancamento::whereMonth('data', $mes)
            ->whereYear('data', $ano);

        if ($unidadeId) {
            $query->where('unidade_id', $unidadeId);
        }

        $resumo = (clone $query)->selectRaw("
            SUM(CASE WHEN tipo = 'entrada' THEN valor ELSE 0 END) as entradas,
            SUM(CASE WHEN tipo = 'saida' THEN valor ELSE 0 END) as saidas
        ")->first();

        // Evolução mensal (últimos 6 meses)
        $evolucao = FinanceiroLancamento::selectRaw("
            MONTH(data) as mes,
            YEAR(data) as ano,
            SUM(CASE WHEN tipo = 'entrada' THEN valor ELSE 0 END) as entradas,
            SUM(CASE WHEN tipo = 'saida' THEN valor ELSE 0 END) as saidas
        ")
        ->where('data', '>=', now()->subMonths(5)->startOfMonth())
        ->groupBy('ano', 'mes')
        ->orderBy('ano')
        ->orderBy('mes')
        ->get();

        // Distribuição por categoria (para gráficos de pizza)
        $distribuicaoCategorias = (clone $query)->join('financeiro_categorias', 'financeiro_lancamentos.categoria_id', '=', 'financeiro_categorias.id')
            ->selectRaw("
                financeiro_categorias.nome,
                financeiro_categorias.tipo as categoria_tipo,
                SUM(financeiro_lancamentos.valor) as total
            ")
            ->groupBy('financeiro_categorias.nome', 'categoria_tipo')
            ->get();

        $receitasPorCategoria = $distribuicaoCategorias->where('categoria_tipo', 'receita')->values();
        $despesasPorCategoria = $distribuicaoCategorias->where('categoria_tipo', 'despesa')->values();

        // Saldo por unidade (reutilizando a lógica anterior mas garantindo os tipos)
        $saldoUnidades = Unidade::with(['lancamentos' => function($q) use ($mes, $ano) {
            $q->whereMonth('data', $mes)->whereYear('data', $ano);
        }])->get()->map(function($unidade) {
            $entradas = $unidade->lancamentos->where('tipo', 'entrada')->sum('valor');
            $saidas = $unidade->lancamentos->where('tipo', 'saida')->sum('valor');
            return [
                'nome' => $unidade->nome,
                'entradas' => (float) $entradas,
                'saidas' => (float) $saidas,
                'saldo' => (float) ($entradas - $saidas)
            ];
        });

        return Inertia::render('Financeiro/Dashboard', [
            'filtros' => [
                'mes' => (int) $mes,
                'ano' => (int) $ano,
                'unidade_id' => $unidadeId,
                'periodo_de' => $request->periodo_de ?? '',
                'periodo_ate' => $request->periodo_ate ?? '',
            ],
            'kpis' => [
                'total_entradas' => (float) ($resumo->entradas ?? 0),
                'total_saidas' => (float) ($resumo->saidas ?? 0),
                'saldo_total' => (float) (($resumo->entradas ?? 0) - ($resumo->saidas ?? 0)),
            ],
            'grafico_evolucao' => [
                'labels' => $evolucao->map(fn($e) => date('M/Y', mktime(0, 0, 0, $e->mes, 1, $e->ano)))->toArray(),
                'entradas' => $evolucao->pluck('entradas')->map(fn($v) => (float)$v)->toArray(),
                'saidas' => $evolucao->pluck('saidas')->map(fn($v) => (float)$v)->toArray(),
                'saldos' => $evolucao->map(fn($e) => (float)($e->entradas - $e->saidas))->toArray(),
            ],
            'balanco_unidades' => $saldoUnidades->map(fn($s) => [
                'unidade' => $s['nome'],
                'entradas' => $s['entradas'],
                'saidas' => $s['saidas'],
                'saldo' => $s['saldo'],
            ]),
            'receitas_categoria' => [
                'labels' => $receitasPorCategoria->pluck('nome'),
                'series' => $receitasPorCategoria->pluck('total')->map(fn($v) => (float)$v),
            ],
            'despesas_categoria' => [
                'labels' => $despesasPorCategoria->pluck('nome'),
                'series' => $despesasPorCategoria->pluck('total')->map(fn($v) => (float)$v),
            ],
            'unidades' => Unidade::all(),
        ]);
    }

    // ============ DOADORES ============

    public function doadores(Request $request)
    {
        $query = Doador::withCount('lancamentos');

        if ($request->filled('busca')) {
            $query->where('nome', 'like', "%{$request->busca}%");
        }

        $doadores = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Financeiro/Doadores', [
            'doadores' => $doadores,
            'filtros' => $request->only(['busca']),
        ]);
    }

    public function storeDoador(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'tipo' => 'required|in:pessoa_fisica,pessoa_juridica',
            'cpf_cnpj' => 'nullable|string|max:20',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'endereco' => 'nullable|string|max:255',
        ]);

        Doador::create($validated);

        return redirect()->route('financeiro.doadores')
            ->with('success', 'Doador cadastrado com sucesso!');
    }

    public function destroyDoador(Doador $doador)
    {
        $doador->delete();

        return redirect()->route('financeiro.doadores')
            ->with('success', 'Doador removido com sucesso!');
    }
}
