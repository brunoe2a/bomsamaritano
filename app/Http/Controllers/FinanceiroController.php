<?php

namespace App\Http\Controllers;

use App\Models\Doador;
use App\Models\FinanceiroCategoria;
use App\Models\FinanceiroLancamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class FinanceiroController extends Controller
{
    public function index(Request $request)
    {
        $query = FinanceiroLancamento::with(['categoria', 'doador', 'usuario']);

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
            'filtros' => $request->only(['busca', 'tipo', 'categoria_id', 'mes', 'ano']),
            'categorias' => FinanceiroCategoria::all(),
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
            'data' => 'required|date',
            'comprovante' => 'nullable|file|max:5120',
            'observacoes' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();

        if ($request->hasFile('comprovante')) {
            $validated['comprovante'] = $request->file('comprovante')->store('financeiro/comprovantes', 'public');
        }

        FinanceiroLancamento::create($validated);

        return redirect()->route('financeiro.index')
            ->with('success', 'Lançamento registrado com sucesso!');
    }

    public function edit(FinanceiroLancamento $financeiro)
    {
        return Inertia::render('Financeiro/Edit', [
            'lancamento' => $financeiro->load(['categoria', 'doador']),
            'categorias' => FinanceiroCategoria::all(),
            'doadores' => Doador::select('id', 'nome', 'tipo')->get(),
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
            'data' => 'required|date',
            'comprovante' => 'nullable|file|max:5120',
            'observacoes' => 'nullable|string',
        ]);

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
