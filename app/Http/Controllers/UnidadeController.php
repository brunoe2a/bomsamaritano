<?php

namespace App\Http\Controllers;

use App\Models\Unidade;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnidadeController extends Controller
{
    public function index(Request $request)
    {
        $query = Unidade::query();

        if ($request->filled('busca')) {
            $query->where('nome', 'like', "%{$request->busca}%");
        }

        $unidades = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Unidades/Index', [
            'unidades' => $unidades,
            'filtros' => $request->only(['busca']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Unidades/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'endereco' => 'nullable|string|max:255',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'contato_responsavel' => 'nullable|string|max:255',
        ]);

        Unidade::create($validated);

        return redirect()->route('unidades.index')
            ->with('success', 'Unidade cadastrada com sucesso!');
    }

    public function edit(Unidade $unidade)
    {
        return Inertia::render('Unidades/Edit', [
            'unidade' => $unidade,
        ]);
    }

    public function update(Request $request, Unidade $unidade)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'endereco' => 'nullable|string|max:255',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'contato_responsavel' => 'nullable|string|max:255',
        ]);

        $unidade->update($validated);

        return redirect()->route('unidades.index')
            ->with('success', 'Unidade atualizada com sucesso!');
    }

    public function destroy(Unidade $unidade)
    {
        if ($unidade->id === 1) {
            return redirect()->back()->with('error', 'A unidade principal não pode ser excluída.');
        }

        if ($unidade->turmas()->exists() || $unidade->lancamentos()->exists()) {
            return redirect()->back()->with('error', 'Esta unidade possui turmas ou lançamentos vinculados e não pode ser excluída.');
        }

        $unidade->delete();

        return redirect()->route('unidades.index')
            ->with('success', 'Unidade removida com sucesso!');
    }
}
