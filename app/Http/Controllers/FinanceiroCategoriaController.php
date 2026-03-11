<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FinanceiroCategoriaController extends Controller
{
    public function index()
    {
        return response()->json(\App\Models\FinanceiroCategoria::orderBy('nome')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'tipo' => 'required|in:receita,despesa',
            'descricao' => 'nullable|string',
        ]);

        \App\Models\FinanceiroCategoria::create($validated);

        return redirect()->back()->with('success', 'Categoria criada com sucesso!');
    }

    public function update(Request $request, \App\Models\FinanceiroCategoria $financeiroCategoria)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'tipo' => 'required|in:receita,despesa',
            'descricao' => 'nullable|string',
        ]);

        $financeiroCategoria->update($validated);

        return redirect()->back()->with('success', 'Categoria atualizada com sucesso!');
    }

    public function destroy(\App\Models\FinanceiroCategoria $financeiroCategoria)
    {
        if ($financeiroCategoria->lancamentos()->exists()) {
            return redirect()->back()->with('error', 'Esta categoria não pode ser removida pois possui lançamentos vinculados.');
        }

        $financeiroCategoria->delete();

        return redirect()->back()->with('success', 'Categoria removida com sucesso!');
    }
}
