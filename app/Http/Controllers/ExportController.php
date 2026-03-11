<?php

namespace App\Http\Controllers;

use App\Exports\AlunosExport;
use App\Exports\FinanceiroExport;
use App\Models\Aluno;
use App\Models\Chamada;
use App\Models\FinanceiroLancamento;
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
            'alunos_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function financeiroExcel(Request $request)
    {
        return Excel::download(
            new FinanceiroExport($request->tipo, $request->mes, $request->ano),
            'financeiro_' . now()->format('Y-m-d') . '.xlsx'
        );
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
        $mes = $request->mes ?? now()->month;
        $ano = $request->ano ?? now()->year;

        $lancamentos = FinanceiroLancamento::with(['categoria', 'doador'])
            ->whereMonth('data', $mes)
            ->whereYear('data', $ano)
            ->orderBy('data')
            ->get();

        $totais = [
            'entradas' => $lancamentos->where('tipo', 'entrada')->sum('valor'),
            'saidas' => $lancamentos->where('tipo', 'saida')->sum('valor'),
        ];
        $totais['saldo'] = $totais['entradas'] - $totais['saidas'];

        $pdf = Pdf::loadView('pdf.relatorio-financeiro', [
            'lancamentos' => $lancamentos,
            'totais' => $totais,
            'mes' => $mes,
            'ano' => $ano,
        ]);

        return $pdf->download("financeiro_{$mes}_{$ano}.pdf");
    }
}
