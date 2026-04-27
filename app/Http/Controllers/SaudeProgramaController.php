<?php

namespace App\Http\Controllers;

use App\Models\SaudeArea;
use App\Models\SaudePrograma;
use App\Http\Requests\StoreSaudeProgramaRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SaudeProgramaController extends Controller
{
    public function index(Request $request)
    {
        $query = SaudePrograma::with('area')->withCount(['convocacoes', 'atendimentos']);

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }
        if ($request->filled('busca')) {
            $query->where('nome', 'like', "%{$request->busca}%");
        }

        return Inertia::render('Saude/Programas/Index', [
            'programas' => $query->orderBy('nome')->paginate(15)->withQueryString(),
            'filtros' => $request->only(['area_id', 'busca']),
            'areas' => SaudeArea::ativos()->orderBy('nome')->get(['id', 'nome']),
        ]);
    }

    public function store(StoreSaudeProgramaRequest $request)
    {
        $data = $request->validated();
        $data['area_id'] = $this->resolveAreaId($data['area_id'] ?? null, $data['area'] ?? null);
        unset($data['area']);

        SaudePrograma::create($data);

        return back()->with('success', 'Programa cadastrado com sucesso!');
    }

    public function update(StoreSaudeProgramaRequest $request, SaudePrograma $programa)
    {
        $data = $request->validated();
        $data['area_id'] = $this->resolveAreaId($data['area_id'] ?? null, $data['area'] ?? null);
        unset($data['area']);

        $programa->update($data);

        return back()->with('success', 'Programa atualizado com sucesso!');
    }

    public function destroy(SaudePrograma $programa)
    {
        if ($programa->atendimentos()->exists() || $programa->convocacoes()->exists()) {
            return back()->with('error', 'Não é possível excluir: programa possui atendimentos ou convocações vinculadas.');
        }

        $programa->delete();

        return back()->with('success', 'Programa removido com sucesso!');
    }

    private function resolveAreaId($areaId, $areaNome): int
    {
        if ($areaId) {
            return (int) $areaId;
        }

        return SaudeArea::firstOrCreate(['nome' => trim($areaNome)], ['status' => 'ativo'])->id;
    }
}
