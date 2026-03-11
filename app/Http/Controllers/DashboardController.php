<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Chamada;
use App\Models\Curso;
use App\Models\FinanceiroLancamento;
use App\Models\Turma;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $hoje = Carbon::today();
        $diaSemana = $hoje->dayOfWeekIso;

        $totalAlunosAtivos = Aluno::ativos()->count();

        $alunosPorCurso = Curso::ativos()
            ->withCount(['turmas as alunos_count' => function ($query) {
                $query->join('matriculas', 'turmas.id', '=', 'matriculas.turma_id')
                    ->where('matriculas.status', 'ativa');
            }])
            ->get()
            ->map(fn ($curso) => [
                'nome' => $curso->nome,
                'total' => $curso->alunos_count,
            ]);

        $turmasHoje = Turma::emAndamento()
            ->with(['curso', 'professores'])
            ->whereJsonContains('dias_semana', (string) $diaSemana)
            ->get();

        $chamadasPendentes = $turmasHoje->filter(function ($turma) use ($hoje) {
            return ! Chamada::where('turma_id', $turma->id)
                ->where('data', $hoje)
                ->exists();
        })->count();

        $aniversariantes = Aluno::ativos()
            ->aniversariantesSemana()
            ->select('id', 'nome', 'data_nascimento', 'foto')
            ->limit(10)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'nome' => $a->nome,
                'data_nascimento' => $a->data_nascimento->format('d/m'),
                'idade' => $a->idade,
                'foto' => $a->foto,
            ]);

        $mesAtual = Carbon::now()->month;
        $anoAtual = Carbon::now()->year;

        $saldoFinanceiro = FinanceiroLancamento::whereYear('data', $anoAtual)
            ->selectRaw("
                SUM(CASE WHEN tipo = 'entrada' THEN valor ELSE 0 END) as total_entradas,
                SUM(CASE WHEN tipo = 'saida' THEN valor ELSE 0 END) as total_saidas
            ")
            ->first();

        $ultimasMovimentacoes = FinanceiroLancamento::with('categoria')
            ->latest('data')
            ->limit(5)
            ->get()
            ->map(fn ($l) => [
                'id' => $l->id,
                'descricao' => $l->descricao,
                'valor' => $l->valor,
                'tipo' => $l->tipo,
                'data' => $l->data->format('d/m/Y'),
                'categoria' => $l->categoria?->nome,
            ]);

        $novosCadastrosMes = Aluno::whereMonth('created_at', $mesAtual)
            ->whereYear('created_at', $anoAtual)
            ->count();

        // Gráfico de frequência mensal (últimos 6 meses)
        $frequenciaMensal = collect(range(5, 0))->map(function ($i) {
            $data = Carbon::now()->subMonths($i);
            $mes = $data->format('M/Y');

            $totalPresencas = \App\Models\ChamadaAluno::whereHas('chamada', function ($q) use ($data) {
                $q->whereMonth('data', $data->month)->whereYear('data', $data->year);
            })->where('presente', true)->count();

            $totalRegistros = \App\Models\ChamadaAluno::whereHas('chamada', function ($q) use ($data) {
                $q->whereMonth('data', $data->month)->whereYear('data', $data->year);
            })->count();

            return [
                'mes' => $mes,
                'percentual' => $totalRegistros > 0 ? round(($totalPresencas / $totalRegistros) * 100, 1) : 0,
            ];
        });

        // Gráfico financeiro mensal (últimos 6 meses)
        $financeiroMensal = collect(range(5, 0))->map(function ($i) {
            $data = Carbon::now()->subMonths($i);
            $meses = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

            $entradas = FinanceiroLancamento::where('tipo', 'entrada')
                ->whereMonth('data', $data->month)
                ->whereYear('data', $data->year)
                ->sum('valor');

            $saidas = FinanceiroLancamento::where('tipo', 'saida')
                ->whereMonth('data', $data->month)
                ->whereYear('data', $data->year)
                ->sum('valor');

            return [
                'mes' => $meses[$data->month] . '/' . $data->format('y'),
                'entradas' => (float) $entradas,
                'saidas' => (float) $saidas,
            ];
        });

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalAlunosAtivos' => $totalAlunosAtivos,
                'alunosPorCurso' => $alunosPorCurso,
                'turmasHoje' => $turmasHoje->count(),
                'chamadasPendentes' => $chamadasPendentes,
                'novosCadastrosMes' => $novosCadastrosMes,
                'saldo' => [
                    'entradas' => (float) ($saldoFinanceiro->total_entradas ?? 0),
                    'saidas' => (float) ($saldoFinanceiro->total_saidas ?? 0),
                    'atual' => (float) (($saldoFinanceiro->total_entradas ?? 0) - ($saldoFinanceiro->total_saidas ?? 0)),
                ],
            ],
            'aniversariantes' => $aniversariantes,
            'ultimasMovimentacoes' => $ultimasMovimentacoes,
            'frequenciaMensal' => $frequenciaMensal,
            'financeiroMensal' => $financeiroMensal,
        ]);
    }
}
