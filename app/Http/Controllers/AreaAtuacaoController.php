<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AreaAtuacaoController extends Controller
{
    public function index()
    {
        return response()->json(\App\Models\AreaAtuacao::orderBy('nome')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255|unique:area_atuacaos,nome',
        ]);

        $area = \App\Models\AreaAtuacao::create($validated);

        return redirect()->back()->with('success', 'Área de atuação criada com sucesso!');
    }

    public function update(Request $request, \App\Models\AreaAtuacao $areaAtuacao)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255|unique:area_atuacaos,nome,' . $areaAtuacao->id,
        ]);

        $areaAtuacao->update($validated);

        return redirect()->back()->with('success', 'Área de atuação atualizada com sucesso!');
    }

    public function destroy(\App\Models\AreaAtuacao $areaAtuacao)
    {
        $areaAtuacao->delete();

        return redirect()->back()->with('success', 'Área de atuação removida com sucesso!');
    }
}
