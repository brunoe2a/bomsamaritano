<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Matricula;
use App\Models\Turma;
use App\Models\Curso;
use App\Models\Professor;
use App\Models\Voluntario;
use App\Models\Chamada;
use App\Models\ChamadaAluno;
use App\Models\Unidade;
use App\Models\WhatsappInstance;
use App\Models\WhatsappTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TurmaController extends Controller
{
    public function index(Request $request)
    {
        $query = Turma::with(['curso', 'professores', 'unidade'])
            ->withCount(['matriculas as alunos_count' => fn ($q) => $q->where('status', 'ativa')]);

        if ($request->filled('busca')) {
            $query->where('nome', 'like', "%{$request->busca}%");
        }

        if ($request->filled('curso_id')) {
            $query->where('curso_id', $request->curso_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('ano_letivo')) {
            $query->where('ano_letivo', $request->ano_letivo);
        }

        if ($request->filled('unidade_id')) {
            $query->where('unidade_id', $request->unidade_id);
        }

        $turmas = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Turmas/Index', [
            'turmas' => $turmas,
            'filtros' => $request->only(['busca', 'curso_id', 'status', 'unidade_id']),
            'cursos' => Curso::ativos()->get(),
            'unidades' => Unidade::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Turmas/Create', [
            'cursos' => Curso::ativos()->get(),
            'professores' => Professor::ativos()->select('id', 'nome')->get(),
            'voluntarios' => Voluntario::ativos()->select('id', 'nome')->get(),
            'unidades' => Unidade::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'curso_id' => 'required|exists:cursos,id',
            'professores_ids' => 'nullable|array',
            'professores_ids.*' => 'exists:professores,id',
            'voluntarios_ids' => 'nullable|array',
            'voluntarios_ids.*' => 'exists:voluntarios,id',
            'horario_inicio' => 'nullable',
            'horario_fim' => 'nullable',
            'dias_semana' => 'nullable|array',
            'periodo' => 'required|in:segunda_sexta,sabados',
            'capacidade_maxima' => 'required|integer|min:1',
            'ano_letivo' => 'required|integer|min:2020|max:2030',
            'status' => 'required|in:planejada,em_andamento,encerrada',
            'unidade_id' => 'required|exists:unidades,id',
        ]);

        $turma = Turma::create($validated);
        
        $turma->professores()->sync($request->professores_ids ?? []);
        $turma->voluntarios()->sync($request->voluntarios_ids ?? []);

        return redirect()->route('turmas.index')
            ->with('success', 'Turma criada com sucesso!');
    }

    public function show(Turma $turma)
    {
        $turma->load([
            'curso',
            'professores',
            'voluntarios',
            'matriculas' => fn ($q) => $q->where('status', 'ativa')->with('aluno'),
        ]);

        $chamadas = Chamada::where('turma_id', $turma->id)
            ->with(['presencas.aluno.responsavel:id,nome,whatsapp,telefone'])
            ->latest('data')
            ->paginate(10);

        // Alunos ativos que não estão matriculados nesta turma
        $alunosMatriculadosIds = $turma->matriculas->pluck('aluno_id');
        $alunosDisponiveis = Aluno::where('status', 'ativo')
            ->whereNotIn('id', $alunosMatriculadosIds)
            ->select('id', 'nome', 'ano_escolar')
            ->orderBy('nome')
            ->get();

        return Inertia::render('Turmas/Show', [
            'turma' => $turma->load('unidade'),
            'chamadas' => $chamadas,
            'alunosDisponiveis' => $alunosDisponiveis,
            'whatsappInstancias' => WhatsappInstance::where('status', 'connected')->get(['id', 'nome']),
            'whatsappTemplates' => WhatsappTemplate::ativos()->get(['id', 'nome', 'conteudo']),
        ]);
    }

    public function edit(Turma $turma)
    {
        $turma->load(['professores', 'voluntarios', 'unidade']);

        return Inertia::render('Turmas/Edit', [
            'turma' => $turma,
            'cursos' => Curso::ativos()->get(),
            'professores' => Professor::ativos()->select('id', 'nome')->get(),
            'voluntarios' => Voluntario::ativos()->select('id', 'nome')->get(),
            'unidades' => Unidade::all(),
        ]);
    }

    public function update(Request $request, Turma $turma)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'curso_id' => 'required|exists:cursos,id',
            'professores_ids' => 'nullable|array',
            'professores_ids.*' => 'exists:professores,id',
            'voluntarios_ids' => 'nullable|array',
            'voluntarios_ids.*' => 'exists:voluntarios,id',
            'horario_inicio' => 'nullable',
            'horario_fim' => 'nullable',
            'dias_semana' => 'nullable|array',
            'periodo' => 'required|in:segunda_sexta,sabados',
            'capacidade_maxima' => 'required|integer|min:1',
            'ano_letivo' => 'required|integer|min:2020|max:2030',
            'status' => 'required|in:planejada,em_andamento,encerrada',
            'unidade_id' => 'required|exists:unidades,id',
        ]);

        $turma->update($validated);
        
        $turma->professores()->sync($request->professores_ids ?? []);
        $turma->voluntarios()->sync($request->voluntarios_ids ?? []);

        return redirect()->route('turmas.show', $turma)
            ->with('success', 'Turma atualizada com sucesso!');
    }

    public function destroy(Turma $turma)
    {
        $turma->delete();

        return redirect()->route('turmas.index')
            ->with('success', 'Turma removida com sucesso!');
    }

    // =================== CHAMADA ===================

    public function chamada(Request $request, Turma $turma)
    {
        $turma->load(['curso', 'professores']);

        $alunos = $turma->alunosAtivos()
            ->select('alunos.id', 'alunos.nome', 'alunos.foto')
            ->get();

        try {
            $data = $request->filled('data')
                ? Carbon::createFromFormat('Y-m-d', $request->string('data'))->startOfDay()
                : Carbon::today();
        } catch (\Exception $e) {
            $data = Carbon::today();
        }

        $chamadaExistente = Chamada::where('turma_id', $turma->id)
            ->where('data', $data)
            ->with('presencas')
            ->first();

        return Inertia::render('Turmas/Chamada', [
            'turma' => $turma,
            'alunos' => $alunos,
            'chamadaExistente' => $chamadaExistente,
            'data' => $data->format('Y-m-d'),
        ]);
    }

    public function registrarChamada(Request $request, Turma $turma)
    {
        $validated = $request->validate([
            'data' => 'required|date',
            'presencas' => 'required|array',
            'presencas.*.aluno_id' => 'required|exists:alunos,id',
            'presencas.*.presente' => 'required|boolean',
            'presencas.*.observacao' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $turma) {
            $chamada = Chamada::updateOrCreate(
                [
                    'turma_id' => $turma->id,
                    'data' => $validated['data'],
                ],
                [
                    'professor_id' => $turma->professores()->first()?->id,
                    'observacoes' => $validated['observacoes'] ?? null,
                ]
            );

            $chamada->presencas()->delete();

            foreach ($validated['presencas'] as $presenca) {
                ChamadaAluno::create([
                    'chamada_id' => $chamada->id,
                    'aluno_id' => $presenca['aluno_id'],
                    'presente' => $presenca['presente'],
                    'observacao' => $presenca['observacao'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('turmas.chamada', ['turma' => $turma->id, 'data' => $validated['data']])
            ->with('success', 'Chamada registrada com sucesso!');
    }

    // =================== FICHA DE CHAMADA PDF ===================

    public function fichaChamadaPdf(Request $request, Turma $turma)
    {
        $turma->load(['curso', 'professores', 'voluntarios', 'unidade']);

        try {
            $ref = $request->filled('mes')
                ? Carbon::createFromFormat('Y-m', $request->string('mes'))
                : Carbon::now();
        } catch (\Exception $e) {
            $ref = Carbon::now();
        }

        $inicio = $ref->copy()->startOfMonth();
        $fim = $ref->copy()->endOfMonth();

        // Mapeia dias_semana da turma (ex: ["segunda","quarta"]) para inteiros do Carbon
        $mapaDias = [
            'domingo' => 0, 'segunda' => 1, 'terca' => 2, 'terça' => 2,
            'quarta' => 3, 'quinta' => 4, 'sexta' => 5, 'sabado' => 6, 'sábado' => 6,
        ];

        $diasSemanaTurma = collect($turma->dias_semana ?? [])
            ->map(fn ($d) => $mapaDias[mb_strtolower($d)] ?? null)
            ->filter(fn ($v) => $v !== null)
            ->values()
            ->all();

        $datas = [];
        $cursor = $inicio->copy();
        while ($cursor->lte($fim)) {
            if (empty($diasSemanaTurma) || in_array($cursor->dayOfWeek, $diasSemanaTurma, true)) {
                $datas[] = $cursor->copy();
            }
            $cursor->addDay();
        }

        $alunos = $turma->alunosAtivos()
            ->select('alunos.id', 'alunos.nome')
            ->orderBy('alunos.nome')
            ->get();

        $pdf = Pdf::loadView('pdf.ficha-chamada', [
            'turma' => $turma,
            'alunos' => $alunos,
            'datas' => $datas,
            'mes' => $ref->month,
            'ano' => $ref->year,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream("ficha-chamada-{$turma->id}-{$ref->format('Y-m')}.pdf");
    }

    // =================== MATRÍCULA ===================

    public function matricular(Request $request, Turma $turma)
    {
        $validated = $request->validate([
            'aluno_id' => 'required|exists:alunos,id',
        ]);

        // Verificar se já está matriculado
        $existe = Matricula::where('turma_id', $turma->id)
            ->where('aluno_id', $validated['aluno_id'])
            ->where('status', 'ativa')
            ->exists();

        if ($existe) {
            return redirect()->route('turmas.show', $turma)
                ->with('error', 'Este aluno já está matriculado nesta turma.');
        }

        // Verificar capacidade
        $matriculasAtivas = Matricula::where('turma_id', $turma->id)->where('status', 'ativa')->count();
        if ($matriculasAtivas >= $turma->capacidade_maxima) {
            return redirect()->route('turmas.show', $turma)
                ->with('error', 'A turma está lotada. Capacidade máxima atingida.');
        }

        Matricula::create([
            'aluno_id' => $validated['aluno_id'],
            'turma_id' => $turma->id,
            'tipo' => 'nova',
            'ano_letivo' => (int) date('Y'),
            'status' => 'ativa',
            'data_matricula' => now(),
        ]);

        $aluno = Aluno::find($validated['aluno_id']);

        return redirect()->route('turmas.show', $turma)
            ->with('success', "Aluno \"{$aluno->nome}\" matriculado com sucesso!");
    }

    public function desmatricular(Turma $turma, Matricula $matricula)
    {
        if ($matricula->turma_id !== $turma->id) {
            abort(403);
        }

        $nomeAluno = $matricula->aluno->nome ?? 'Aluno';
        $matricula->update(['status' => 'trancada']);

        return redirect()->route('turmas.show', $turma)
            ->with('success', "Matrícula de \"{$nomeAluno}\" cancelada.");
    }
}
