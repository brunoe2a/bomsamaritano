<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionApiService
{
    public function __construct(
        protected string $baseUrl,
        protected string $apiKey,
    ) {}

    protected function client(): PendingRequest
    {
        return Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withHeaders([
                'apikey' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->acceptJson();
    }

    public function createInstance(string $instanceName, ?string $numero = null): array
    {
        $payload = [
            'instanceName' => $instanceName,
            'qrcode' => true,
            'integration' => 'WHATSAPP-BAILEYS',
        ];
        if ($numero) {
            $payload['number'] = $this->normalizeNumber($numero);
        }

        $response = $this->client()->post('/instance/create', $payload);

        if ($response->failed()) {
            Log::warning('Evolution createInstance falhou', ['body' => $response->body()]);
        }

        return $response->json() ?? [];
    }

    public function connectInstance(string $instanceName): array
    {
        $response = $this->client()->get("/instance/connect/{$instanceName}");
        return $response->json() ?? [];
    }

    public function instanceStatus(string $instanceName): array
    {
        $response = $this->client()->get("/instance/connectionState/{$instanceName}");
        return $response->json() ?? [];
    }

    public function logoutInstance(string $instanceName): array
    {
        $response = $this->client()->delete("/instance/logout/{$instanceName}");
        return $response->json() ?? [];
    }

    public function deleteInstance(string $instanceName): array
    {
        $response = $this->client()->delete("/instance/delete/{$instanceName}");
        return $response->json() ?? [];
    }

    public function checkNumber(string $instanceName, string $numero): bool
    {
        $normalized = $this->normalizeNumber($numero);
        $response = $this->client()->post("/chat/whatsappNumbers/{$instanceName}", [
            'numbers' => [$normalized],
        ]);

        if ($response->failed()) {
            return false;
        }

        $data = $response->json();
        if (! is_array($data)) {
            return false;
        }

        foreach ($data as $item) {
            if (isset($item['exists']) && $item['exists'] === true) {
                return true;
            }
        }
        return false;
    }

    public function sendText(string $instanceName, string $numero, string $mensagem): array
    {
        $response = $this->client()->post("/message/sendText/{$instanceName}", [
            'number' => $this->normalizeNumber($numero),
            'text' => $mensagem,
        ]);

        return [
            'ok' => $response->successful(),
            'status' => $response->status(),
            'body' => $response->json(),
            'error' => $response->failed() ? $response->body() : null,
        ];
    }

    public function normalizeNumber(string $numero): string
    {
        $digits = preg_replace('/\D+/', '', $numero);

        if (strlen($digits) <= 11 && ! str_starts_with($digits, '55')) {
            $digits = '55' . $digits;
        }

        return $digits;
    }
}
