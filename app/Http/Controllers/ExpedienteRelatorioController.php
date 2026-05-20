<?php

namespace App\Http\Controllers;

use App\Models\ExpedienteEscalado;
use App\Models\Professor;
use App\Models\Unidade;
use App\Models\Voluntario;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpedienteRelatorioController extends Controller
{
    public function index(Request $request)
    {
        [$ranking, $filtros] = $this->gerarRanking($request);

        return Inertia::render('Expedientes/Relatorio', [
            'ranking' => $ranking,
            'filtros' => $filtros,
            'unidades' => Unidade::all(),
        ]);
    }

    public function pdf(Request $request)
    {
        [$ranking, $filtros] = $this->gerarRanking($request);

        $unidadeNome = null;
        if (! empty($filtros['unidade_id'])) {
            $unidadeNome = Unidade::find($filtros['unidade_id'])?->nome;
        }

        $pdf = Pdf::loadView('pdf.relatorio-assiduidade', [
            'ranking' => $ranking,
            'filtros' => $filtros,
            'unidadeNome' => $unidadeNome,
        ])->setPaper('a4', 'portrait');

        $nomeArquivo = 'relatorio-assiduidade-'.$filtros['data_inicio'].'-a-'.$filtros['data_fim'].'.pdf';

        return $pdf->stream($nomeArquivo);
    }

    private function gerarRanking(Request $request): array
    {
        $unidadeId = $request->input('unidade_id');
        $tipo = $request->input('tipo', 'todos'); // todos | professor | voluntario
        $minEscalas = (int) $request->input('min_escalas', 1);

        try {
            $dataInicio = $request->filled('data_inicio')
                ? Carbon::createFromFormat('Y-m-d', $request->string('data_inicio'))->startOfDay()
                : Carbon::now()->startOfMonth();
            $dataFim = $request->filled('data_fim')
                ? Carbon::createFromFormat('Y-m-d', $request->string('data_fim'))->endOfDay()
                : Carbon::now()->endOfDay();
        } catch (\Exception $e) {
            $dataInicio = Carbon::now()->startOfMonth();
            $dataFim = Carbon::now()->endOfDay();
        }

        $escalados = ExpedienteEscalado::whereHas('expediente', function ($q) use ($dataInicio, $dataFim, $unidadeId) {
            $q->whereBetween('data', [$dataInicio, $dataFim]);
            if ($unidadeId) {
                $q->where('unidade_id', $unidadeId);
            }
        })
            ->with(['expediente:id,data,unidade_id,descricao', 'expediente.unidade:id,nome', 'escalavel'])
            ->get();

        if ($tipo === 'professor') {
            $escalados = $escalados->where('escalavel_type', Professor::class);
        } elseif ($tipo === 'voluntario') {
            $escalados = $escalados->where('escalavel_type', Voluntario::class);
        }

        $ranking = $escalados
            ->groupBy(fn ($e) => $e->escalavel_type.'#'.$e->escalavel_id)
            ->map(function ($grupo) {
                $primeiro = $grupo->first();
                $total = $grupo->count();
                $presencas = $grupo->where('presente', true)->count();
                $faltas = $total - $presencas;

                return [
                    'tipo' => $primeiro->escalavel_type === Professor::class ? 'Professor' : 'Voluntário',
                    'tipo_chave' => $primeiro->escalavel_type === Professor::class ? 'professor' : 'voluntario',
                    'id' => $primeiro->escalavel_id,
                    'nome' => $primeiro->escalavel?->nome ?? '(removido)',
                    'total_escalas' => $total,
                    'presencas' => $presencas,
                    'faltas' => $faltas,
                    'percentual' => $total > 0 ? round(($presencas / $total) * 100, 1) : 0,
                    'detalhes' => $grupo->sortBy(fn ($e) => $e->expediente->data)->map(fn ($e) => [
                        'data' => $e->expediente->data->format('Y-m-d'),
                        'unidade' => $e->expediente->unidade?->nome,
                        'descricao' => $e->expediente->descricao,
                        'presente' => (bool) $e->presente,
                        'justificativa' => $e->justificativa,
                    ])->values(),
                ];
            })
            ->filter(fn ($r) => $r['total_escalas'] >= $minEscalas)
            ->sortBy('percentual')
            ->values()
            ->all();

        $filtros = [
            'unidade_id' => $unidadeId,
            'tipo' => $tipo,
            'min_escalas' => $minEscalas,
            'data_inicio' => $dataInicio->format('Y-m-d'),
            'data_fim' => $dataFim->format('Y-m-d'),
        ];

        return [$ranking, $filtros];
    }
}
