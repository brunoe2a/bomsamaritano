<?php

namespace App\Http\Controllers;

use App\Models\Responsavel;
use App\Http\Requests\StoreResponsavelRequest;
use App\Http\Requests\UpdateResponsavelRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ResponsavelController extends Controller
{
    public function index(Request $request)
    {
        $query = Responsavel::withCount('alunos');

        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                    ->orWhere('cpf', 'like', "%{$busca}%")
                    ->orWhere('telefone', 'like', "%{$busca}%")
                    ->orWhere('whatsapp', 'like', "%{$busca}%");
            });
        }

        $responsaveis = $query->orderBy('nome')->paginate(15)->withQueryString();

        return Inertia::render('Responsaveis/Index', [
            'responsaveis' => $responsaveis,
            'filtros' => $request->only(['busca']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Responsaveis/Create');
    }

    public function store(StoreResponsavelRequest $request)
    {
        $responsavel = Responsavel::create($request->validated());

        return redirect()->route('responsaveis.show', $responsavel)
            ->with('success', 'Responsável cadastrado com sucesso!');
    }

    public function show(Responsavel $responsavel)
    {
        $responsavel->load(['alunos' => function ($q) {
            $q->orderBy('nome')->with(['matriculas.turma.curso']);
        }]);

        return Inertia::render('Responsaveis/Show', [
            'responsavel' => $responsavel,
        ]);
    }

    public function edit(Responsavel $responsavel)
    {
        return Inertia::render('Responsaveis/Edit', [
            'responsavel' => $responsavel,
        ]);
    }

    public function update(UpdateResponsavelRequest $request, Responsavel $responsavel)
    {
        $responsavel->update($request->validated());

        return redirect()->route('responsaveis.show', $responsavel)
            ->with('success', 'Responsável atualizado com sucesso!');
    }

    public function destroy(Responsavel $responsavel)
    {
        if ($responsavel->alunos()->exists()) {
            return back()->with('error', 'Não é possível excluir: este responsável possui alunos vinculados.');
        }

        $responsavel->delete();

        return redirect()->route('responsaveis.index')
            ->with('success', 'Responsável removido com sucesso!');
    }

    public function search(Request $request)
    {
        $busca = $request->get('q', '');

        $query = Responsavel::query()
            ->select('id', 'nome', 'cpf', 'telefone', 'whatsapp')
            ->withCount('alunos');

        if ($busca !== '') {
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                    ->orWhere('cpf', 'like', "%{$busca}%");
            });
        }

        return response()->json($query->orderBy('nome')->limit(20)->get());
    }
}
