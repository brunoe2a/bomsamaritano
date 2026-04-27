<?php

namespace App\Http\Controllers;

use App\Models\SaudeArea;
use Illuminate\Http\Request;

class SaudeAreaController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255|unique:saude_areas,nome',
            'status' => 'nullable|in:ativo,inativo',
        ]);

        $area = SaudeArea::create($data);

        return response()->json($area);
    }

    public function update(Request $request, SaudeArea $area)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255|unique:saude_areas,nome,' . $area->id,
            'status' => 'nullable|in:ativo,inativo',
        ]);

        $area->update($data);

        return response()->json($area);
    }

    public function destroy(SaudeArea $area)
    {
        if ($area->programas()->exists()) {
            return back()->with('error', 'Não é possível excluir: área possui programas vinculados.');
        }

        $area->delete();

        return back()->with('success', 'Área removida com sucesso!');
    }
}
