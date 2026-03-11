<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CursoController extends Controller
{
    public function index(Request $request)
    {
        $query = Curso::withCount(['turmas']);

        if ($request->filled('busca')) {
            $query->where('nome', 'like', "%{$request->busca}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $cursos = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Cursos/Index', [
            'cursos' => $cursos,
            'filtros' => $request->only(['busca', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Cursos/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'carga_horaria' => 'nullable|integer|min:1',
            'dias_semana' => 'nullable|array',
            'periodo' => 'required|in:manha,tarde,noite',
            'max_alunos' => 'required|integer|min:1',
            'status' => 'required|in:ativo,inativo',
        ]);

        Curso::create($validated);

        return redirect()->route('cursos.index')
            ->with('success', 'Curso criado com sucesso!');
    }

    public function edit(Curso $curso)
    {
        return Inertia::render('Cursos/Edit', [
            'curso' => $curso,
        ]);
    }

    public function update(Request $request, Curso $curso)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'carga_horaria' => 'nullable|integer|min:1',
            'dias_semana' => 'nullable|array',
            'periodo' => 'required|in:manha,tarde,noite',
            'max_alunos' => 'required|integer|min:1',
            'status' => 'required|in:ativo,inativo',
        ]);

        $curso->update($validated);

        return redirect()->route('cursos.index')
            ->with('success', 'Curso atualizado com sucesso!');
    }

    public function destroy(Curso $curso)
    {
        $curso->delete();

        return redirect()->route('cursos.index')
            ->with('success', 'Curso removido com sucesso!');
    }
}
