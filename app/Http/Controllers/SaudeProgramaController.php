<?php

namespace App\Http\Controllers;

use App\Models\SaudePrograma;
use App\Http\Requests\StoreSaudeProgramaRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SaudeProgramaController extends Controller
{
    public function index(Request $request)
    {
        $query = SaudePrograma::withCount(['convocacoes', 'atendimentos']);

        if ($request->filled('area')) {
            $query->where('area', $request->area);
        }
        if ($request->filled('busca')) {
            $query->where('nome', 'like', "%{$request->busca}%");
        }

        return Inertia::render('Saude/Programas/Index', [
            'programas' => $query->orderBy('nome')->paginate(15)->withQueryString(),
            'filtros' => $request->only(['area', 'busca']),
        ]);
    }

    public function store(StoreSaudeProgramaRequest $request)
    {
        SaudePrograma::create($request->validated());

        return back()->with('success', 'Programa cadastrado com sucesso!');
    }

    public function update(StoreSaudeProgramaRequest $request, SaudePrograma $programa)
    {
        $programa->update($request->validated());

        return back()->with('success', 'Programa atualizado com sucesso!');
    }

    public function destroy(SaudePrograma $programa)
    {
        if ($programa->atendimentos()->exists() || $programa->convocacoes()->exists()) {
            return back()->with('error', 'Não é possível excluir: programa possui atendimentos ou convocações vinculadas.');
        }

        $programa->delete();

        return back()->with('success', 'Programa removido com sucesso!');
    }
}
