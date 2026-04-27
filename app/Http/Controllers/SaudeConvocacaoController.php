<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\SaudeAtendimento;
use App\Models\SaudeConvocacao;
use App\Models\SaudePrograma;
use App\Models\Unidade;
use App\Http\Requests\StoreSaudeConvocacaoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SaudeConvocacaoController extends Controller
{
    public function index(Request $request)
    {
        $query = SaudeConvocacao::with('programa.area')
            ->withCount(['alunos', 'atendimentos']);

        if ($request->filled('busca')) {
            $query->where('titulo', 'like', "%{$request->busca}%");
        }
        if ($request->filled('programa_id')) {
            $query->where('programa_id', $request->programa_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return Inertia::render('Saude/Convocacoes/Index', [
            'convocacoes' => $query->latest('data')->paginate(15)->withQueryString(),
            'filtros' => $request->only(['busca', 'programa_id', 'status']),
            'programas' => SaudePrograma::ativos()->with('area:id,nome')->select('id', 'nome', 'area_id')->orderBy('nome')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Saude/Convocacoes/Create', [
            'programas' => SaudePrograma::ativos()->with('area:id,nome')->select('id', 'nome', 'area_id')->orderBy('nome')->get(),
            'unidades' => Unidade::select('id', 'nome')->orderBy('nome')->get(),
            'alunos' => Aluno::ativos()
                ->select('id', 'nome', 'ano_escolar', 'responsavel_id')
                ->with('responsavel:id,nome')
                ->orderBy('nome')
                ->get(),
        ]);
    }

    public function store(StoreSaudeConvocacaoRequest $request)
    {
        $convocacao = DB::transaction(function () use ($request) {
            $data = $request->safe()->except(['alunos_ids']);
            $data['user_id'] = Auth::id();
            $convocacao = SaudeConvocacao::create($data);

            if ($request->filled('alunos_ids')) {
                $convocacao->alunos()->attach($request->input('alunos_ids'));
            }

            return $convocacao;
        });

        return redirect()->route('saude.convocacoes.show', $convocacao)
            ->with('success', 'Convocação criada com sucesso!');
    }

    public function show(SaudeConvocacao $convocacao)
    {
        $convocacao->load([
            'programa.area',
            'unidade',
            'user',
            'alunos' => fn ($q) => $q->orderBy('nome')->with('responsavel:id,nome,telefone,whatsapp'),
        ]);

        return Inertia::render('Saude/Convocacoes/Show', [
            'convocacao' => $convocacao,
        ]);
    }

    public function edit(SaudeConvocacao $convocacao)
    {
        $convocacao->load(['alunos:id']);

        return Inertia::render('Saude/Convocacoes/Edit', [
            'convocacao' => $convocacao,
            'programas' => SaudePrograma::ativos()->with('area:id,nome')->select('id', 'nome', 'area_id')->orderBy('nome')->get(),
            'unidades' => Unidade::select('id', 'nome')->orderBy('nome')->get(),
            'alunos' => Aluno::ativos()
                ->select('id', 'nome', 'ano_escolar', 'responsavel_id')
                ->with('responsavel:id,nome')
                ->orderBy('nome')
                ->get(),
        ]);
    }

    public function update(StoreSaudeConvocacaoRequest $request, SaudeConvocacao $convocacao)
    {
        DB::transaction(function () use ($request, $convocacao) {
            $convocacao->update($request->safe()->except(['alunos_ids']));

            if ($request->has('alunos_ids')) {
                $convocacao->alunos()->sync($request->input('alunos_ids', []));
            }
        });

        return redirect()->route('saude.convocacoes.show', $convocacao)
            ->with('success', 'Convocação atualizada com sucesso!');
    }

    public function destroy(SaudeConvocacao $convocacao)
    {
        $convocacao->delete();

        return redirect()->route('saude.convocacoes.index')
            ->with('success', 'Convocação removida com sucesso!');
    }

    /**
     * Marca presença de cada aluno e gera atendimentos automaticamente para os presentes.
     * Quando todos forem registrados, marca a convocação como "realizada".
     */
    public function registrarPresenca(Request $request, SaudeConvocacao $convocacao)
    {
        $request->validate([
            'presencas' => 'required|array',
            'presencas.*.aluno_id' => 'required|exists:alunos,id',
            'presencas.*.presente' => 'required|boolean',
            'presencas.*.observacao' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request, $convocacao) {
            foreach ($request->input('presencas') as $p) {
                $convocacao->alunos()->updateExistingPivot($p['aluno_id'], [
                    'presente' => (bool) $p['presente'],
                    'observacao' => $p['observacao'] ?? null,
                ]);

                if ((bool) $p['presente']) {
                    SaudeAtendimento::firstOrCreate(
                        [
                            'aluno_id' => $p['aluno_id'],
                            'convocacao_id' => $convocacao->id,
                        ],
                        [
                            'programa_id' => $convocacao->programa_id,
                            'data_atendimento' => $convocacao->data,
                            'profissional' => $convocacao->profissional,
                            'observacoes' => $p['observacao'] ?? null,
                            'user_id' => Auth::id(),
                        ],
                    );
                } else {
                    SaudeAtendimento::where('convocacao_id', $convocacao->id)
                        ->where('aluno_id', $p['aluno_id'])
                        ->delete();
                }
            }

            $convocacao->update(['status' => 'realizada']);
        });

        return back()->with('success', 'Presenças registradas com sucesso!');
    }
}
