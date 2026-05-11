<?php

namespace App\Http\Controllers;

use App\Models\Expediente;
use App\Models\ExpedienteEscalado;
use App\Models\Professor;
use App\Models\Unidade;
use App\Models\Voluntario;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ExpedienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Expediente::with('unidade')
            ->withCount(['escalados', 'escalados as faltas_count' => fn ($q) => $q->where('presente', false)]);

        if ($request->filled('unidade_id')) {
            $query->where('unidade_id', $request->unidade_id);
        }

        if ($request->filled('mes')) {
            try {
                $ref = Carbon::createFromFormat('Y-m', $request->string('mes'));
                $query->whereYear('data', $ref->year)->whereMonth('data', $ref->month);
            } catch (\Exception $e) {
                // ignora filtro inválido
            }
        }

        $expedientes = $query->orderByDesc('data')->paginate(15)->withQueryString();

        return Inertia::render('Expedientes/Index', [
            'expedientes' => $expedientes,
            'filtros' => $request->only(['unidade_id', 'mes']),
            'unidades' => Unidade::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Expedientes/Create', [
            'unidades' => Unidade::all(),
            'professores' => Professor::ativos()->select('id', 'nome')->orderBy('nome')->get(),
            'voluntarios' => Voluntario::ativos()->select('id', 'nome')->orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'unidade_id' => 'required|exists:unidades,id',
            'data' => 'required|date',
            'descricao' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
            'professores_ids' => 'nullable|array',
            'professores_ids.*' => 'exists:professores,id',
            'voluntarios_ids' => 'nullable|array',
            'voluntarios_ids.*' => 'exists:voluntarios,id',
        ]);

        $expediente = DB::transaction(function () use ($validated, $request) {
            $expediente = Expediente::create([
                'unidade_id' => $validated['unidade_id'],
                'data' => $validated['data'],
                'descricao' => $validated['descricao'] ?? null,
                'observacoes' => $validated['observacoes'] ?? null,
                'created_by' => $request->user()?->id,
            ]);

            foreach ($validated['professores_ids'] ?? [] as $id) {
                $expediente->escalados()->create([
                    'escalavel_type' => Professor::class,
                    'escalavel_id' => $id,
                    'presente' => true,
                ]);
            }

            foreach ($validated['voluntarios_ids'] ?? [] as $id) {
                $expediente->escalados()->create([
                    'escalavel_type' => Voluntario::class,
                    'escalavel_id' => $id,
                    'presente' => true,
                ]);
            }

            return $expediente;
        });

        return redirect()->route('expedientes.show', $expediente)
            ->with('success', 'Expediente criado com sucesso!');
    }

    public function show(Expediente $expediente)
    {
        $expediente->load(['unidade', 'escalados.escalavel']);

        return Inertia::render('Expedientes/Show', [
            'expediente' => $expediente,
            'professores' => Professor::ativos()->select('id', 'nome')->orderBy('nome')->get(),
            'voluntarios' => Voluntario::ativos()->select('id', 'nome')->orderBy('nome')->get(),
        ]);
    }

    public function update(Request $request, Expediente $expediente)
    {
        $validated = $request->validate([
            'descricao' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
            'presencas' => 'nullable|array',
            'presencas.*.id' => 'required|exists:expediente_escalados,id',
            'presencas.*.presente' => 'required|boolean',
            'presencas.*.justificativa' => 'nullable|string|max:255',
            'add_professores_ids' => 'nullable|array',
            'add_professores_ids.*' => 'exists:professores,id',
            'add_voluntarios_ids' => 'nullable|array',
            'add_voluntarios_ids.*' => 'exists:voluntarios,id',
        ]);

        DB::transaction(function () use ($validated, $expediente) {
            $expediente->update([
                'descricao' => $validated['descricao'] ?? null,
                'observacoes' => $validated['observacoes'] ?? null,
            ]);

            foreach ($validated['presencas'] ?? [] as $p) {
                ExpedienteEscalado::where('id', $p['id'])
                    ->where('expediente_id', $expediente->id)
                    ->update([
                        'presente' => $p['presente'],
                        'justificativa' => $p['justificativa'] ?? null,
                    ]);
            }

            foreach ($validated['add_professores_ids'] ?? [] as $id) {
                $expediente->escalados()->firstOrCreate([
                    'escalavel_type' => Professor::class,
                    'escalavel_id' => $id,
                ], ['presente' => true]);
            }

            foreach ($validated['add_voluntarios_ids'] ?? [] as $id) {
                $expediente->escalados()->firstOrCreate([
                    'escalavel_type' => Voluntario::class,
                    'escalavel_id' => $id,
                ], ['presente' => true]);
            }
        });

        return redirect()->route('expedientes.show', $expediente)
            ->with('success', 'Expediente atualizado!');
    }

    public function destroy(Expediente $expediente)
    {
        $expediente->delete();

        return redirect()->route('expedientes.index')
            ->with('success', 'Expediente removido.');
    }

    public function removerEscalado(Expediente $expediente, ExpedienteEscalado $escalado)
    {
        if ($escalado->expediente_id !== $expediente->id) {
            abort(403);
        }

        $escalado->delete();

        return redirect()->route('expedientes.show', $expediente)
            ->with('success', 'Escalado removido.');
    }

    public function escalaPdf(Expediente $expediente)
    {
        $expediente->load(['unidade', 'escalados.escalavel']);

        $pdf = Pdf::loadView('pdf.expediente-escala', [
            'expediente' => $expediente,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("escala-{$expediente->data->format('Y-m-d')}.pdf");
    }
}
