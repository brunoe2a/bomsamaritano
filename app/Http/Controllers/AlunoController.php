<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Responsavel;
use App\Models\Curso;
use App\Models\Turma;
use App\Models\SaudePrograma;
use App\Http\Requests\StoreAlunoRequest;
use App\Http\Requests\UpdateAlunoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AlunoController extends Controller
{
    public function index(Request $request)
    {
        $query = Aluno::with(['responsavel', 'matriculas.turma.curso']);

        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                    ->orWhereHas('responsavel', fn ($r) => $r->where('nome', 'like', "%{$busca}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('ano_escolar')) {
            $query->where('ano_escolar', $request->ano_escolar);
        }

        if ($request->filled('curso_id')) {
            $query->whereHas('matriculas.turma', fn ($q) => $q->where('curso_id', $request->curso_id));
        }

        if ($request->filled('turma_id')) {
            $query->whereHas('matriculas', fn ($q) => $q->where('turma_id', $request->turma_id));
        }

        $alunos = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Alunos/Index', [
            'alunos' => $alunos,
            'filtros' => $request->only(['busca', 'status', 'ano_escolar', 'curso_id', 'turma_id']),
            'cursos' => Curso::ativos()->select('id', 'nome')->get(),
            'turmas' => Turma::emAndamento()->select('id', 'nome', 'curso_id')->get(),
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Alunos/Create', [
            'cursos' => Curso::ativos()->with(['turmas' => fn ($q) => $q->emAndamento()])->get(),
            'responsaveis' => Responsavel::query()
                ->select('id', 'nome', 'cpf', 'telefone', 'whatsapp')
                ->withCount('alunos')
                ->orderBy('nome')
                ->get(),
            'responsavel_pre_selecionado' => $request->filled('responsavel_id')
                ? Responsavel::find($request->integer('responsavel_id'))
                : null,
        ]);
    }

    public function store(StoreAlunoRequest $request)
    {
        DB::transaction(function () use ($request) {
            if ($request->input('responsavel_modo') === 'existente') {
                $responsavelId = (int) $request->input('responsavel_id');
            } else {
                $responsavel = Responsavel::create($request->validated()['responsavel']);
                $responsavelId = $responsavel->id;
            }

            $alunoData = $request->safe()->except(['responsavel', 'responsavel_modo', 'responsavel_id', 'turmas_ids', 'foto']);
            $alunoData['responsavel_id'] = $responsavelId;

            if ($request->hasFile('foto')) {
                $alunoData['foto'] = $request->file('foto')->store('alunos/fotos');
            }

            $aluno = Aluno::create($alunoData);

            if ($request->filled('turmas_ids')) {
                foreach ($request->turmas_ids as $turmaId) {
                    $aluno->matriculas()->create([
                        'turma_id' => $turmaId,
                        'tipo' => 'nova',
                        'ano_letivo' => date('Y'),
                        'status' => 'ativa',
                        'data_matricula' => now(),
                    ]);
                }
            }
        });

        return redirect()->route('alunos.index')
            ->with('success', 'Aluno cadastrado com sucesso!');
    }

    public function show(Aluno $aluno)
    {
        $aluno->load([
            'responsavel',
            'matriculas.turma.curso',
            'matriculas.turma.professores',
            'atendimentosSaude' => fn ($q) => $q->latest('data_atendimento')->with(['programa:id,nome,area', 'convocacao:id,titulo']),
        ]);

        // Frequência do aluno
        $frequencia = DB::table('chamada_aluno')
            ->where('aluno_id', $aluno->id)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN presente = 1 THEN 1 ELSE 0 END) as presencas
            ')
            ->first();

        $percentualFrequencia = $frequencia->total > 0
            ? round(($frequencia->presencas / $frequencia->total) * 100, 1)
            : 0;

        return Inertia::render('Alunos/Show', [
            'aluno' => $aluno,
            'frequencia' => [
                'total' => $frequencia->total ?? 0,
                'presencas' => $frequencia->presencas ?? 0,
                'percentual' => $percentualFrequencia,
            ],
            'programas_saude' => SaudePrograma::ativos()->select('id', 'nome', 'area')->orderBy('nome')->get(),
        ]);
    }

    public function edit(Aluno $aluno)
    {
        $aluno->load(['responsavel', 'matriculas.turma']);

        return Inertia::render('Alunos/Edit', [
            'aluno' => $aluno,
            'cursos' => Curso::ativos()->with(['turmas' => fn ($q) => $q->emAndamento()])->get(),
            'responsaveis' => Responsavel::query()
                ->select('id', 'nome', 'cpf', 'telefone', 'whatsapp')
                ->withCount('alunos')
                ->orderBy('nome')
                ->get(),
        ]);
    }

    public function update(UpdateAlunoRequest $request, Aluno $aluno)
    {
        DB::transaction(function () use ($request, $aluno) {
            if ($request->filled('responsavel_id') && (int) $request->input('responsavel_id') !== $aluno->responsavel_id) {
                $aluno->responsavel_id = (int) $request->input('responsavel_id');
            } elseif ($request->has('responsavel')) {
                $aluno->responsavel->update($request->validated()['responsavel']);
            }

            $alunoData = $request->safe()->except(['responsavel', 'responsavel_id', 'turmas_ids', 'foto']);

            if ($request->hasFile('foto')) {
                if ($aluno->foto) {
                    Storage::delete($aluno->foto);
                }
                $alunoData['foto'] = $request->file('foto')->store('alunos/fotos');
            }

            $aluno->update($alunoData);
        });

        return redirect()->route('alunos.show', $aluno)
            ->with('success', 'Aluno atualizado com sucesso!');
    }

    public function destroy(Aluno $aluno)
    {
        if ($aluno->foto) {
            Storage::delete($aluno->foto);
        }

        $aluno->delete();

        return redirect()->route('alunos.index')
            ->with('success', 'Aluno removido com sucesso!');
    }
}
