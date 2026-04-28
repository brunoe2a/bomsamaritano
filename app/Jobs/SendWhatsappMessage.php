<?php

namespace App\Jobs;

use App\Models\WhatsappNotification;
use App\Services\EvolutionApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SendWhatsappMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(public int $notificationId) {}

    public function handle(EvolutionApiService $evolution): void
    {
        $notification = WhatsappNotification::with('instance')->find($this->notificationId);
        if (! $notification) {
            return;
        }
        if ($notification->status === 'enviado') {
            return;
        }
        $instance = $notification->instance;
        if (! $instance) {
            $notification->update(['status' => 'falhou', 'erro' => 'Instância não encontrada']);
            return;
        }

        $stateResp = $evolution->instanceStatus($instance->instance_name);
        $state = data_get($stateResp, 'instance.state') ?? data_get($stateResp, 'state');
        if ($state !== 'open' && $state !== 'connected') {
            $instance->update([
                'status' => $state === 'connecting' ? 'connecting' : 'disconnected',
                'last_status_at' => Carbon::now(),
            ]);
            $notification->update([
                'status' => 'falhou',
                'erro' => "Instância \"{$instance->nome}\" não está conectada (state={$state}). Reconecte via QR Code antes de reenviar.",
            ]);
            return;
        }

        if ($instance->status !== 'connected') {
            $instance->update(['status' => 'connected', 'last_status_at' => Carbon::now()]);
        }

        $notification->update(['status' => 'validando']);

        $valido = $evolution->checkNumber($instance->instance_name, $notification->numero);
        $notification->numero_valido = $valido;
        $notification->numero_normalizado = $evolution->normalizeNumber($notification->numero);

        if (! $valido) {
            $notification->status = 'numero_invalido';
            $notification->erro = 'Número não está registrado no WhatsApp';
            $notification->save();
            return;
        }

        $notification->status = 'enviando';
        $notification->save();

        $result = $evolution->sendText($instance->instance_name, $notification->numero, $notification->mensagem);

        if (! $result['ok']) {
            $notification->status = 'falhou';
            $notification->erro = is_string($result['error']) ? mb_substr($result['error'], 0, 1000) : 'Falha no envio';
            $notification->save();

            Log::warning('WhatsApp envio falhou', ['notification_id' => $notification->id, 'response' => $result]);
            return;
        }

        $body = $result['body'] ?? [];
        $messageId = data_get($body, 'key.id') ?? data_get($body, 'messageId') ?? null;

        $notification->status = 'enviado';
        $notification->evolution_message_id = $messageId;
        $notification->enviado_em = Carbon::now();
        $notification->erro = null;
        $notification->save();
    }

    public function failed(\Throwable $e): void
    {
        WhatsappNotification::where('id', $this->notificationId)
            ->update([
                'status' => 'falhou',
                'erro' => mb_substr($e->getMessage(), 0, 1000),
            ]);
    }
}
