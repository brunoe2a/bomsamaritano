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
            // Aceita area_id (FK) OU area (nome livre — backend faz firstOrCreate)
            'area_id' => 'nullable|exists:saude_areas,id',
            'area' => 'nullable|string|max:255|required_without:area_id',
            'descricao' => 'nullable|string',
            'cor' => 'nullable|string|max:7',
            'status' => 'nullable|in:ativo,inativo',
        ];
    }

    public function messages(): array
    {
        return [
            'area.required_without' => 'A área é obrigatória.',
        ];
    }
}
