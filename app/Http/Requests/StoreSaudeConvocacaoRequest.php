<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaudeConvocacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'programa_id' => 'required|exists:saude_programas,id',
            'titulo' => 'required|string|max:255',
            'data' => 'required|date',
            'hora' => 'nullable|date_format:H:i',
            'local' => 'nullable|string|max:255',
            'profissional' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
            'status' => 'nullable|in:planejada,realizada,cancelada',
            'unidade_id' => 'nullable|exists:unidades,id',
            'alunos_ids' => 'nullable|array',
            'alunos_ids.*' => 'exists:alunos,id',
        ];
    }
}
