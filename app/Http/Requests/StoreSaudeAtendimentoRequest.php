<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaudeAtendimentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'aluno_id' => 'required|exists:alunos,id',
            'programa_id' => 'required|exists:saude_programas,id',
            'data_atendimento' => 'required|date',
            'profissional' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
            'convocacao_id' => 'nullable|exists:saude_convocacoes,id',
        ];
    }
}
