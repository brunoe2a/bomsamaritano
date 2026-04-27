<?php

namespace App\Http\Controllers;

use App\Models\SaudeAtendimento;
use App\Models\SaudeConvocacao;
use App\Models\SaudePrograma;
use Inertia\Inertia;

class SaudeController extends Controller
{
    public function index()
    {
        return Inertia::render('Saude/Index', [
            'totais' => [
                'programas_ativos' => SaudePrograma::ativos()->count(),
                'convocacoes_planejadas' => SaudeConvocacao::where('status', 'planejada')->count(),
                'atendimentos_total' => SaudeAtendimento::count(),
                'atendimentos_mes' => SaudeAtendimento::whereMonth('data_atendimento', now()->month)
                    ->whereYear('data_atendimento', now()->year)
                    ->count(),
            ],
            'proximas_convocacoes' => SaudeConvocacao::with(['programa:id,nome,area_id', 'programa.area:id,nome'])
                ->withCount('alunos')
                ->where('status', 'planejada')
                ->where('data', '>=', now()->toDateString())
                ->orderBy('data')
                ->limit(5)
                ->get(),
            'ultimos_atendimentos' => SaudeAtendimento::with(['aluno:id,nome', 'programa:id,nome,area_id', 'programa.area:id,nome'])
                ->latest('data_atendimento')
                ->limit(10)
                ->get(),
        ]);
    }
}
