<?php

namespace App\Http\Controllers;

use App\Models\Voluntario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class VoluntarioController extends Controller
{
    public function index(Request $request)
    {
        $query = Voluntario::query();

        if ($request->filled('busca')) {
            $query->where('nome', 'like', "%{$request->busca}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('area_atuacao')) {
            $query->where('area_atuacao', 'like', "%{$request->area_atuacao}%");
        }

        $voluntarios = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Voluntarios/Index', [
            'voluntarios' => $voluntarios,
            'filtros' => $request->only(['busca', 'status', 'area_atuacao']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Voluntarios/Create', [
            'areasAtuacao' => \App\Models\AreaAtuacao::orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'foto' => 'nullable|image|max:2048',
            'cpf' => 'nullable|string|max:14|unique:voluntarios,cpf',
            'rg' => 'nullable|string|max:20',
            'data_nascimento' => 'nullable|date|before:today',
            'telefone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'endereco_rua' => 'nullable|string|max:255',
            'endereco_numero' => 'nullable|string|max:20',
            'endereco_complemento' => 'nullable|string|max:255',
            'endereco_bairro' => 'nullable|string|max:255',
            'endereco_cidade' => 'nullable|string|max:255',
            'endereco_estado' => 'nullable|string|max:2',
            'endereco_cep' => 'nullable|string|max:10',
            'area_atuacao' => 'nullable|string|max:255',
            'habilidades' => 'nullable|string',
            'data_inicio' => 'nullable|date',
            'status' => 'required|in:ativo,inativo',
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('voluntarios/fotos', 'public');
        }

        Voluntario::create($validated);

        return redirect()->route('voluntarios.index')
            ->with('success', 'Voluntário cadastrado com sucesso!');
    }

    public function show(Voluntario $voluntario)
    {
        return Inertia::render('Voluntarios/Show', [
            'voluntario' => $voluntario,
        ]);
    }

    public function edit(Voluntario $voluntario)
    {
        return Inertia::render('Voluntarios/Edit', [
            'voluntario' => $voluntario,
            'areasAtuacao' => \App\Models\AreaAtuacao::orderBy('nome')->get(),
        ]);
    }

    public function update(Request $request, Voluntario $voluntario)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'foto' => 'nullable|image|max:2048',
            'cpf' => 'nullable|string|max:14',
            'rg' => 'nullable|string|max:20',
            'data_nascimento' => 'nullable|date|before:today',
            'telefone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'endereco_rua' => 'nullable|string|max:255',
            'endereco_numero' => 'nullable|string|max:20',
            'endereco_complemento' => 'nullable|string|max:255',
            'endereco_bairro' => 'nullable|string|max:255',
            'endereco_cidade' => 'nullable|string|max:255',
            'endereco_estado' => 'nullable|string|max:2',
            'endereco_cep' => 'nullable|string|max:10',
            'area_atuacao' => 'nullable|string|max:255',
            'habilidades' => 'nullable|string',
            'data_inicio' => 'nullable|date',
            'status' => 'required|in:ativo,inativo',
        ]);

        if ($request->hasFile('foto')) {
            if ($voluntario->foto) {
                Storage::disk('public')->delete($voluntario->foto);
            }
            $validated['foto'] = $request->file('foto')->store('voluntarios/fotos', 'public');
        } else {
            unset($validated['foto']);
        }

        $voluntario->update($validated);

        return redirect()->route('voluntarios.show', $voluntario)
            ->with('success', 'Voluntário atualizado com sucesso!');
    }

    public function destroy(Voluntario $voluntario)
    {
        if ($voluntario->foto) {
            Storage::disk('public')->delete($voluntario->foto);
        }
        $voluntario->delete();

        return redirect()->route('voluntarios.index')
            ->with('success', 'Voluntário removido com sucesso!');
    }
}
