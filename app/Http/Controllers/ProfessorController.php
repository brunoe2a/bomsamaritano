<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use App\Models\Unidade;
use App\Models\Especialidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProfessorController extends Controller
{
    public function index(Request $request)
    {
        $query = Professor::with(['turmas', 'unidades', 'especialidades'])->withCount('turmas');

        if ($request->filled('busca')) {
            $query->where('nome', 'like', "%{$request->busca}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tipo_vinculo')) {
            $query->where('tipo_vinculo', $request->tipo_vinculo);
        }

        if ($request->filled('unidade_id')) {
            $query->whereHas('unidades', function ($q) use ($request) {
                $q->where('unidades.id', $request->unidade_id);
            });
        }

        $professores = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Professores/Index', [
            'professores' => $professores,
            'filtros' => $request->only(['busca', 'status', 'tipo_vinculo', 'unidade_id']),
            'unidades' => Unidade::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Professores/Create', [
            'unidades' => Unidade::all(),
            'especialidades' => Especialidade::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'foto' => 'nullable|image|max:2048',
            'cpf' => 'nullable|string|max:14|unique:professores,cpf',
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
            'especialidade' => 'nullable|array',
            'tipo_vinculo' => 'required|in:voluntario,contratado',
            'data_inicio' => 'nullable|date',
            'status' => 'required|in:ativo,inativo',
            'unidades' => 'required|array|min:1',
            'unidades.*' => 'exists:unidades,id',
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('professores/fotos', 'public');
        }
        
        $unidades = $validated['unidades'];
        unset($validated['unidades']);

        $especialidadesIds = $this->resolveEspecialidades($validated['especialidade'] ?? []);
        $validated['especialidade'] = Especialidade::whereIn('id', $especialidadesIds)->pluck('nome')->toArray();

        $professor = Professor::create($validated);
        $professor->unidades()->sync($unidades);
        $professor->especialidades()->sync($especialidadesIds);

        return redirect()->route('professores.index')
            ->with('success', 'Professor cadastrado com sucesso!');
    }

    public function show(Professor $professor)
    {
        $professor->load(['turmas.curso', 'turmas.matriculas', 'unidades', 'especialidades']);

        $aulasMes = $professor->chamadas()
            ->whereMonth('data', now()->month)
            ->whereYear('data', now()->year)
            ->count();

        return Inertia::render('Professores/Show', [
            'professor' => $professor,
            'aulasMes' => $aulasMes,
        ]);
    }

    public function edit(Professor $professor)
    {
        return Inertia::render('Professores/Edit', [
            'professor' => $professor->load(['unidades', 'especialidades']),
            'unidades' => Unidade::all(),
            'especialidades' => Especialidade::all(),
        ]);
    }

    public function update(Request $request, Professor $professor)
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
            'especialidade' => 'nullable|array',
            'tipo_vinculo' => 'required|in:voluntario,contratado',
            'data_inicio' => 'nullable|date',
            'status' => 'required|in:ativo,inativo',
            'unidades' => 'required|array|min:1',
            'unidades.*' => 'exists:unidades,id',
        ]);

        if ($request->hasFile('foto')) {
            if ($professor->foto) {
                Storage::disk('public')->delete($professor->foto);
            }
            $validated['foto'] = $request->file('foto')->store('professores/fotos', 'public');
        } else {
            unset($validated['foto']);
        }

        $unidades = $validated['unidades'];
        unset($validated['unidades']);

        $especialidadesIds = $this->resolveEspecialidades($validated['especialidade'] ?? []);
        $validated['especialidade'] = Especialidade::whereIn('id', $especialidadesIds)->pluck('nome')->toArray();

        $professor->update($validated);
        $professor->unidades()->sync($unidades);
        $professor->especialidades()->sync($especialidadesIds);

        return redirect()->route('professores.show', $professor)
            ->with('success', 'Professor atualizado com sucesso!');
    }

    public function destroy(Professor $professor)
    {
        if ($professor->foto) {
            Storage::disk('public')->delete($professor->foto);
        }
        $professor->delete();

        return redirect()->route('professores.index')
            ->with('success', 'Professor removido com sucesso!');
    }

    private function resolveEspecialidades($especialidadesInput)
    {
        if (empty($especialidadesInput)) return [];
        
        $ids = [];
        foreach ($especialidadesInput as $value) {
            if (is_numeric($value)) {
                $ids[] = $value;
            } else {
                $especialidade = Especialidade::firstOrCreate(['nome' => $value]);
                $ids[] = $especialidade->id;
            }
        }
        return $ids;
    }
}
