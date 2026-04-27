<?php

namespace App\Http\Controllers\Whatsapp;

use App\Http\Controllers\Controller;
use App\Models\WhatsappInstance;
use App\Services\EvolutionApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Inertia\Inertia;

class WhatsappInstanceController extends Controller
{
    public function __construct(protected EvolutionApiService $evolution) {}

    public function index()
    {
        $instances = WhatsappInstance::orderBy('nome')->get();

        return Inertia::render('Whatsapp/Instancias/Index', [
            'instancias' => $instances,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:120'],
            'numero' => ['nullable', 'string', 'max:30'],
        ]);

        $instanceName = Str::slug($data['nome'], '_') . '_' . Str::lower(Str::random(6));

        $resp = $this->evolution->createInstance($instanceName, $data['numero'] ?? null);

        $qr = data_get($resp, 'qrcode.base64') ?? data_get($resp, 'qrcode.code') ?? null;

        $instance = WhatsappInstance::create([
            'nome' => $data['nome'],
            'instance_name' => $instanceName,
            'numero' => $data['numero'] ?? null,
            'status' => 'connecting',
            'qr_code' => $qr,
            'last_status_at' => Carbon::now(),
        ]);

        return back()->with('success', "Instância \"{$instance->nome}\" criada. Escaneie o QR code para conectar.");
    }

    public function qrCode(WhatsappInstance $instancia)
    {
        $resp = $this->evolution->connectInstance($instancia->instance_name);
        $qr = data_get($resp, 'base64') ?? data_get($resp, 'qrcode.base64') ?? data_get($resp, 'code') ?? null;

        $instancia->update([
            'qr_code' => $qr,
            'status' => 'connecting',
            'last_status_at' => Carbon::now(),
        ]);

        return response()->json([
            'qr_code' => $qr,
            'status' => $instancia->status,
        ]);
    }

    public function status(WhatsappInstance $instancia)
    {
        $resp = $this->evolution->instanceStatus($instancia->instance_name);
        $state = data_get($resp, 'instance.state') ?? data_get($resp, 'state') ?? null;

        $status = match ($state) {
            'open', 'connected' => 'connected',
            'connecting' => 'connecting',
            default => 'disconnected',
        };

        $instancia->update([
            'status' => $status,
            'last_status_at' => Carbon::now(),
            'qr_code' => $status === 'connected' ? null : $instancia->qr_code,
        ]);

        return response()->json([
            'status' => $status,
            'raw' => $state,
        ]);
    }

    public function disconnect(WhatsappInstance $instancia)
    {
        $this->evolution->logoutInstance($instancia->instance_name);
        $instancia->update(['status' => 'disconnected', 'qr_code' => null, 'last_status_at' => Carbon::now()]);

        return back()->with('success', 'Instância desconectada.');
    }

    public function destroy(WhatsappInstance $instancia)
    {
        $this->evolution->deleteInstance($instancia->instance_name);
        $instancia->delete();

        return back()->with('success', 'Instância removida.');
    }
}
