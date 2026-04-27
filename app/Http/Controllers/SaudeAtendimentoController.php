<?php

namespace App\Http\Controllers;

use App\Models\SaudeAtendimento;
use App\Models\SaudePrograma;
use App\Http\Requests\StoreSaudeAtendimentoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SaudeAtendimentoController extends Controller
{
    public function index(Request $request)
    {
        $query = SaudeAtendimento::with(['aluno:id,nome', 'programa:id,nome,area']);

        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->whereHas('aluno', fn ($q) => $q->where('nome', 'like', "%{$busca}%"));
        }
        if ($request->filled('programa_id')) {
            $query->where('programa_id', $request->programa_id);
        }
        if ($request->filled('data_inicio')) {
            $query->whereDate('data_atendimento', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('data_atendimento', '<=', $request->data_fim);
        }

        return Inertia::render('Saude/Atendimentos/Index', [
            'atendimentos' => $query->latest('data_atendimento')->paginate(20)->withQueryString(),
            'filtros' => $request->only(['busca', 'programa_id', 'data_inicio', 'data_fim']),
            'programas' => SaudePrograma::ativos()->select('id', 'nome', 'area')->orderBy('nome')->get(),
        ]);
    }

    public function store(StoreSaudeAtendimentoRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        SaudeAtendimento::create($data);

        return back()->with('success', 'Atendimento registrado com sucesso!');
    }

    public function update(StoreSaudeAtendimentoRequest $request, SaudeAtendimento $atendimento)
    {
        $atendimento->update($request->validated());

        return back()->with('success', 'Atendimento atualizado com sucesso!');
    }

    public function destroy(SaudeAtendimento $atendimento)
    {
        $atendimento->delete();

        return back()->with('success', 'Atendimento removido com sucesso!');
    }
}
