<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateResponsavelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $responsavelId = $this->route('responsavel')?->id ?? $this->route('responsaveis')?->id;

        return [
            'nome' => 'required|string|max:255',
            'endereco_rua' => 'nullable|string|max:255',
            'endereco_numero' => 'nullable|string|max:20',
            'endereco_complemento' => 'nullable|string|max:255',
            'endereco_bairro' => 'nullable|string|max:255',
            'endereco_cidade' => 'nullable|string|max:255',
            'endereco_estado' => 'nullable|string|max:2',
            'endereco_cep' => 'nullable|string|max:10',
            'telefone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'cpf' => ['nullable', 'string', 'max:14', Rule::unique('responsaveis', 'cpf')->ignore($responsavelId)],
            'renda_familiar' => 'nullable|in:menos_1_salario,ate_2_salarios,acima_3_salarios',
            'veiculo_proprio' => 'nullable|boolean',
            'casa_propria' => 'nullable|boolean',
            'cadastro_cras' => 'nullable|boolean',
            'auxilio_governo' => 'nullable|boolean',
            'desempregado' => 'nullable|boolean',
            'autorizacao_sozinho' => 'nullable|boolean',
            'autorizacao_imagem' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do responsável é obrigatório.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
        ];
    }
}
