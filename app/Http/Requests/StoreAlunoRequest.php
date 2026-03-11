<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAlunoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'data_nascimento' => 'required|date|before:today',
            'ano_escolar' => 'nullable|string|max:50',
            'foto' => 'nullable|image|max:2048',
            'status' => 'nullable|in:ativo,inativo,trancado,concluido',
            'observacoes' => 'nullable|string',
            'turmas_ids' => 'nullable|array',
            'turmas_ids.*' => 'exists:turmas,id',

            // Responsável
            'responsavel.nome' => 'required|string|max:255',
            'responsavel.endereco_rua' => 'nullable|string|max:255',
            'responsavel.endereco_numero' => 'nullable|string|max:20',
            'responsavel.endereco_complemento' => 'nullable|string|max:255',
            'responsavel.endereco_bairro' => 'nullable|string|max:255',
            'responsavel.endereco_cidade' => 'nullable|string|max:255',
            'responsavel.endereco_estado' => 'nullable|string|max:2',
            'responsavel.endereco_cep' => 'nullable|string|max:10',
            'responsavel.telefone' => 'nullable|string|max:20',
            'responsavel.whatsapp' => 'nullable|string|max:20',
            'responsavel.cpf' => 'nullable|string|max:14|unique:responsaveis,cpf',
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

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do aluno é obrigatório.',
            'data_nascimento.required' => 'A data de nascimento é obrigatória.',
            'data_nascimento.before' => 'A data de nascimento deve ser anterior a hoje.',
            'responsavel.nome.required' => 'O nome do responsável é obrigatório.',
            'responsavel.cpf.unique' => 'Este CPF já está cadastrado.',
        ];
    }
}
