<?php

namespace App\Http\Controllers\Whatsapp;

use App\Http\Controllers\Controller;
use App\Models\WhatsappTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WhatsappTemplateController extends Controller
{
    public function index()
    {
        return Inertia::render('Whatsapp/Templates/Index', [
            'templates' => WhatsappTemplate::orderBy('nome')->get(),
            'variaveis_disponiveis' => WhatsappTemplate::VARIAVEIS_DISPONIVEIS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        WhatsappTemplate::create($data);

        return back()->with('success', 'Mensagem cadastrada com sucesso!');
    }

    public function update(Request $request, WhatsappTemplate $template)
    {
        $data = $this->validateData($request);
        $template->update($data);

        return back()->with('success', 'Mensagem atualizada com sucesso!');
    }

    public function destroy(WhatsappTemplate $template)
    {
        $template->delete();

        return back()->with('success', 'Mensagem removida.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'nome' => ['required', 'string', 'max:120'],
            'conteudo' => ['required', 'string'],
            'variaveis' => ['nullable', 'array'],
            'status' => ['nullable', 'in:ativo,inativo'],
        ]);
    }
}
