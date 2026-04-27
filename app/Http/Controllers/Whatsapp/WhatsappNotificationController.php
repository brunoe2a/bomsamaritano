<?php

namespace App\Http\Controllers\Whatsapp;

use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsappMessage;
use App\Models\Aluno;
use App\Models\WhatsappInstance;
use App\Models\WhatsappNotification;
use App\Models\WhatsappTemplate;
use App\Services\WhatsappTemplateRenderer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class WhatsappNotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = WhatsappNotification::with(['instance', 'template', 'aluno', 'responsavel']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function ($q) use ($busca) {
                $q->where('numero', 'like', "%{$busca}%")
                  ->orWhere('mensagem', 'like', "%{$busca}%");
            });
        }

        return Inertia::render('Whatsapp/Notificacoes/Index', [
            'notificacoes' => $query->latest()->paginate(20)->withQueryString(),
            'instancias' => WhatsappInstance::where('status', 'connected')->get(['id', 'nome', 'instance_name']),
            'templates' => WhatsappTemplate::ativos()->get(['id', 'nome', 'conteudo']),
            'filtros' => $request->only(['status', 'busca']),
            'estatisticas' => [
                'total' => WhatsappNotification::count(),
                'pendente' => WhatsappNotification::whereIn('status', ['pendente', 'validando', 'enviando'])->count(),
                'enviado' => WhatsappNotification::where('status', 'enviado')->count(),
                'falhou' => WhatsappNotification::whereIn('status', ['falhou', 'numero_invalido'])->count(),
            ],
        ]);
    }

    public function dispatch(Request $request, WhatsappTemplateRenderer $renderer)
    {
        $data = $request->validate([
            'whatsapp_instance_id' => ['required', 'exists:whatsapp_instances,id'],
            'whatsapp_template_id' => ['required', 'exists:whatsapp_templates,id'],
            'destinatarios' => ['required', 'array', 'min:1'],
            'destinatarios.*.aluno_id' => ['nullable', 'integer'],
            'destinatarios.*.numero' => ['nullable', 'string'],
        ]);

        $template = WhatsappTemplate::findOrFail($data['whatsapp_template_id']);
        $instanceId = $data['whatsapp_instance_id'];

        $criadas = 0;

        DB::transaction(function () use ($data, $template, $instanceId, $renderer, &$criadas) {
            $offsetSegundos = 0;

            foreach ($data['destinatarios'] as $dest) {
                $aluno = ! empty($dest['aluno_id']) ? Aluno::with('responsavel')->find($dest['aluno_id']) : null;
                $responsavel = $aluno?->responsavel;
                $numero = $dest['numero'] ?? $responsavel?->whatsapp ?? $responsavel?->telefone;

                if (! $numero) {
                    continue;
                }

                $mensagem = $renderer->render($template->conteudo, $aluno, $responsavel);

                $notif = WhatsappNotification::create([
                    'whatsapp_instance_id' => $instanceId,
                    'whatsapp_template_id' => $template->id,
                    'aluno_id' => $aluno?->id,
                    'responsavel_id' => $responsavel?->id,
                    'user_id' => Auth::id(),
                    'numero' => $numero,
                    'mensagem' => $mensagem,
                    'status' => 'pendente',
                ]);

                $offsetSegundos += random_int(60, 180);
                SendWhatsappMessage::dispatch($notif->id)->delay(now()->addSeconds($offsetSegundos));

                $criadas++;
            }
        });

        return back()->with('success', "{$criadas} notificação(ões) enfileirada(s) com delay aleatório de 60s a 180s entre cada envio.");
    }

    public function reenviar(WhatsappNotification $notificacao)
    {
        $notificacao->update(['status' => 'pendente', 'erro' => null]);
        SendWhatsappMessage::dispatch($notificacao->id)->delay(now()->addSeconds(random_int(60, 180)));

        return back()->with('success', 'Notificação reenfileirada para reenvio.');
    }

    public function destroy(WhatsappNotification $notificacao)
    {
        $notificacao->delete();
        return back()->with('success', 'Notificação removida.');
    }

    public function alunos(Request $request)
    {
        $query = Aluno::with('responsavel:id,nome,whatsapp,telefone')
            ->select('id', 'nome', 'responsavel_id')
            ->ativos();

        if ($request->filled('busca')) {
            $query->where('nome', 'like', "%{$request->busca}%");
        }

        return response()->json($query->orderBy('nome')->limit(50)->get());
    }
}
