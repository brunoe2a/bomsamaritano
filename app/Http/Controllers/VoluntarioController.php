<?php

namespace App\Http\Controllers;

use App\Models\Voluntario;
use App\Models\Unidade;
use App\Models\Habilidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class VoluntarioController extends Controller
{
    public function index(Request $request)
    {
        $query = Voluntario::with(['unidades', 'habilidades']);

        if ($request->filled('busca')) {
            $query->where('nome', 'like', "%{$request->busca}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('area_atuacao')) {
            $query->where('area_atuacao', 'like', "%{$request->area_atuacao}%");
        }

        if ($request->filled('unidade_id')) {
            $query->whereHas('unidades', function ($q) use ($request) {
                $q->where('unidades.id', $request->unidade_id);
            });
        }

        $voluntarios = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Voluntarios/Index', [
            'voluntarios' => $voluntarios,
            'filtros' => $request->only(['busca', 'status', 'unidade_id']),
            'unidades' => Unidade::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Voluntarios/Create', [
            'areasAtuacao' => \App\Models\AreaAtuacao::orderBy('nome')->get(),
            'habilidades' => Habilidade::all(),
            'unidades' => Unidade::all(),
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
            'habilidades' => 'nullable|array',
            'data_inicio' => 'nullable|date',
            'status' => 'required|in:ativo,inativo',
            'unidades' => 'required|array|min:1',
            'unidades.*' => 'exists:unidades,id',
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('voluntarios/fotos');
        }

        $unidades = $validated['unidades'];
        unset($validated['unidades']);

        $habilidadesIds = $this->resolveHabilidades($validated['habilidades'] ?? []);
        $validated['habilidade'] = implode(', ', Habilidade::whereIn('id', $habilidadesIds)->pluck('nome')->toArray());
        unset($validated['habilidades']);

        $voluntario = Voluntario::create($validated);
        $voluntario->unidades()->sync($unidades);
        $voluntario->habilidades()->sync($habilidadesIds);

        return redirect()->route('voluntarios.index')
            ->with('success', 'Voluntário cadastrado com sucesso!');
    }

    public function show(Voluntario $voluntario)
    {
        return Inertia::render('Voluntarios/Show', [
            'voluntario' => $voluntario->load(['unidades', 'habilidades']),
        ]);
    }

    public function edit(Voluntario $voluntario)
    {
        return Inertia::render('Voluntarios/Edit', [
            'voluntario' => $voluntario->load(['unidades', 'habilidades']),
            'areasAtuacao' => \App\Models\AreaAtuacao::orderBy('nome')->get(),
            'habilidades' => Habilidade::all(),
            'unidades' => Unidade::all(),
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
            'habilidades' => 'nullable|array',
            'data_inicio' => 'nullable|date',
            'status' => 'required|in:ativo,inativo',
            'unidades' => 'required|array|min:1',
            'unidades.*' => 'exists:unidades,id',
        ]);

        if ($request->hasFile('foto')) {
            if ($voluntario->foto) {
                Storage::delete($voluntario->foto);
            }
            $validated['foto'] = $request->file('foto')->store('voluntarios/fotos');
        } else {
            unset($validated['foto']);
        }

        $unidades = $validated['unidades'];
        unset($validated['unidades']);

        $habilidadesIds = $this->resolveHabilidades($validated['habilidades'] ?? []);
        $validated['habilidade'] = implode(', ', Habilidade::whereIn('id', $habilidadesIds)->pluck('nome')->toArray());
        unset($validated['habilidades']);

        $voluntario->update($validated);
        $voluntario->unidades()->sync($unidades);
        $voluntario->habilidades()->sync($habilidadesIds);

        return redirect()->route('voluntarios.show', $voluntario)
            ->with('success', 'Voluntário atualizado com sucesso!');
    }

    public function destroy(Voluntario $voluntario)
    {
        if ($voluntario->foto) {
            Storage::delete($voluntario->foto);
        }
        $voluntario->delete();

        return redirect()->route('voluntarios.index')
            ->with('success', 'Voluntário removido com sucesso!');
    }

    private function resolveHabilidades($habilidadesInput)
    {
        if (empty($habilidadesInput)) return [];
        
        $ids = [];
        foreach ($habilidadesInput as $value) {
            if (is_numeric($value)) {
                $ids[] = $value;
            } else {
                $habilidade = Habilidade::firstOrCreate(['nome' => $value]);
                $ids[] = $habilidade->id;
            }
        }
        return $ids;
    }
}
