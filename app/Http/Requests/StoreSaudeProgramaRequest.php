<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaudeProgramaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'area' => 'required|in:odontologia,psicologia,medica,nutricao,fonoaudiologia,geral',
            'descricao' => 'nullable|string',
            'cor' => 'nullable|string|max:7',
            'status' => 'nullable|in:ativo,inativo',
        ];
    }
}
