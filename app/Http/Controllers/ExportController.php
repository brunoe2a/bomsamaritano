<?php

namespace App\Http\Controllers;

use App\Exports\AlunosExport;
use App\Exports\FinanceiroExport;
use App\Models\Aluno;
use App\Models\Chamada;
use App\Models\FinanceiroLancamento;
use App\Models\SaudeConvocacao;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    // ============ EXCEL ============

    public function alunosExcel(Request $request)
    {
        return Excel::download(
            new AlunosExport($request->status, $request->curso_id),
            'alunos_'.now()->format('Y-m-d').'.xlsx'
        );
    }

    public function financeiroExcel(Request $request)
    {
        $filtros = $this->filtrosFinanceiro($request);

        $sufixo = match ($filtros['tipo'] ?? null) {
            'entrada' => 'entradas',
            'saida' => 'saidas',
            default => 'geral',
        };

        return Excel::download(
            new FinanceiroExport($filtros),
            "financeiro_{$sufixo}_".now()->format('Y-m-d').'.xlsx'
        );
    }

    /**
     * Filtros compartilhados pelas exportações do financeiro (Excel e PDF),
     * espelhando os filtros da listagem em Financeiro/Index.
     */
    private function filtrosFinanceiro(Request $request): array
    {
        return $request->validate([
            'tipo' => 'nullable|in:entrada,saida',
            'categoria_id' => 'nullable|exists:financeiro_categorias,id',
            'unidade_id' => 'nullable|exists:unidades,id',
            'busca' => 'nullable|string|max:255',
            'mes' => 'nullable|integer|min:1|max:12',
            'ano' => 'nullable|integer|min:2000|max:2100',
        ]);
    }

    // ============ PDF ============

    public function fichaAluno(Aluno $aluno)
    {
        $aluno->load(['responsavel', 'matriculas.turma.curso', 'matriculas.turma.professores']);

        $pdf = Pdf::loadView('pdf.ficha-aluno', [
            'aluno' => $aluno,
        ]);

        return $pdf->download("ficha_aluno_{$aluno->id}.pdf");
    }

    public function relatorioFrequencia(Request $request)
    {
        $request->validate([
            'turma_id' => 'required|exists:turmas,id',
            'mes' => 'nullable|integer|min:1|max:12',
            'ano' => 'nullable|integer',
        ]);

        $mes = $request->mes ?? now()->month;
        $ano = $request->ano ?? now()->year;

        $chamadas = Chamada::with(['presencas.aluno', 'turma.curso'])
            ->where('turma_id', $request->turma_id)
            ->whereMonth('data', $mes)
            ->whereYear('data', $ano)
            ->orderBy('data')
            ->get();

        $turma = $chamadas->first()?->turma;

        $pdf = Pdf::loadView('pdf.relatorio-frequencia', [
            'chamadas' => $chamadas,
            'turma' => $turma,
            'mes' => $mes,
            'ano' => $ano,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("frequencia_{$turma?->nome}_{$mes}_{$ano}.pdf");
    }

    public function relatorioFinanceiro(Request $request)
    {
        $filtros = $this->filtrosFinanceiro($request);

        $tipo = $filtros['tipo'] ?? null;
        $mes = $filtros['mes'] ?? now()->month;
        $ano = $filtros['ano'] ?? now()->year;

        $lancamentos = FinanceiroLancamento::with(['categoria', 'doador'])
            ->whereMonth('data', $mes)
            ->whereYear('data', $ano)
            ->when($tipo, fn ($q) => $q->where('tipo', $tipo))
            ->when($filtros['categoria_id'] ?? null, fn ($q, $id) => $q->where('categoria_id', $id))
            ->when($filtros['unidade_id'] ?? null, fn ($q, $id) => $q->where('unidade_id', $id))
            ->when($filtros['busca'] ?? null, fn ($q, $busca) => $q->where('descricao', 'like', "%{$busca}%"))
            ->orderBy('data')
            ->get();

        $totais = [
            'entradas' => (float) $lancamentos->where('tipo', 'entrada')->sum('valor'),
            'saidas' => (float) $lancamentos->where('tipo', 'saida')->sum('valor'),
        ];
        $totais['saldo'] = $totais['entradas'] - $totais['saidas'];

        $pdf = Pdf::loadView('pdf.relatorio-financeiro', [
            'lancamentos' => $lancamentos,
            'totais' => $totais,
            'tipo' => $tipo,
            'mes' => $mes,
            'ano' => $ano,
        ]);

        $sufixo = match ($tipo) {
            'entrada' => 'entradas',
            'saida' => 'saidas',
            default => 'geral',
        };

        return $pdf->download("financeiro_{$sufixo}_{$mes}_{$ano}.pdf");
    }

    public function saudeConvocacao(SaudeConvocacao $convocacao)
    {
        $convocacao->load([
            'programa',
            'unidade',
            'alunos' => fn ($q) => $q->orderBy('nome')->with('responsavel:id,nome,telefone,whatsapp'),
        ]);

        $pdf = Pdf::loadView('pdf.saude-convocacao', [
            'convocacao' => $convocacao,
        ]);

        return $pdf->download("convocacao_saude_{$convocacao->id}.pdf");
    }
}
