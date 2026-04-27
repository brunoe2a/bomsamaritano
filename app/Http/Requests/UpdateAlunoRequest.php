<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAlunoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'sometimes|required|string|max:255',
            'data_nascimento' => 'sometimes|required|date|before:today',
            'ano_escolar' => 'nullable|string|max:50',
            'foto' => 'nullable|image|max:2048',
            'status' => 'nullable|in:ativo,inativo,trancado,concluido',
            'observacoes' => 'nullable|string',
            'turmas_ids' => 'nullable|array',
            'turmas_ids.*' => 'exists:turmas,id',

            'responsavel_id' => 'sometimes|nullable|exists:responsaveis,id',

            'responsavel.nome' => 'sometimes|required|string|max:255',
            'responsavel.endereco_rua' => 'nullable|string|max:255',
            'responsavel.endereco_numero' => 'nullable|string|max:20',
            'responsavel.endereco_complemento' => 'nullable|string|max:255',
            'responsavel.endereco_bairro' => 'nullable|string|max:255',
            'responsavel.endereco_cidade' => 'nullable|string|max:255',
            'responsavel.endereco_estado' => 'nullable|string|max:2',
            'responsavel.endereco_cep' => 'nullable|string|max:10',
            'responsavel.telefone' => 'nullable|string|max:20',
            'responsavel.whatsapp' => 'nullable|string|max:20',
            'responsavel.cpf' => 'nullable|string|max:14',
            'responsavel.renda_familiar' => 'nullable|in:menos_1_salario,ate_2_salarios,acima_3_salarios',
            'responsavel.veiculo_proprio' => 'nullable|boolean',
            'responsavel.casa_propria' => 'nullable|boolean',
            'responsavel.cadastro_cras' => 'nullable|boolean',
            'responsavel.auxilio_governo' => 'nullable|boolean',
            'responsavel.desempregado' => 'nullable|boolean',
            'responsavel.autorizacao_sozinho' => 'nullable|boolean',
            'responsavel.autorizacao_imagem' => 'nullable|boolean',
        ];
    }
}
