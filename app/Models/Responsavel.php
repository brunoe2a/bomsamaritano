<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Responsavel extends Model
{
    use HasFactory;

    protected $table = 'responsaveis';

    protected $fillable = [
        'nome',
        'endereco_rua',
        'endereco_numero',
        'endereco_complemento',
        'endereco_bairro',
        'endereco_cidade',
        'endereco_estado',
        'endereco_cep',
        'telefone',
        'whatsapp',
        'cpf',
        'renda_familiar',
        'veiculo_proprio',
        'casa_propria',
        'cadastro_cras',
        'auxilio_governo',
        'desempregado',
        'autorizacao_sozinho',
        'autorizacao_imagem',
    ];

    protected function casts(): array
    {
        return [
            'veiculo_proprio' => 'boolean',
            'casa_propria' => 'boolean',
            'cadastro_cras' => 'boolean',
            'auxilio_governo' => 'boolean',
            'desempregado' => 'boolean',
            'autorizacao_sozinho' => 'boolean',
            'autorizacao_imagem' => 'boolean',
        ];
    }

    public function alunos(): HasMany
    {
        return $this->hasMany(Aluno::class);
    }

    public function getEnderecoCompletoAttribute(): string
    {
        $parts = array_filter([
            $this->endereco_rua,
            $this->endereco_numero,
            $this->endereco_complemento,
            $this->endereco_bairro,
            $this->endereco_cidade ? "{$this->endereco_cidade}/{$this->endereco_estado}" : null,
            $this->endereco_cep,
        ]);

        return implode(', ', $parts);
    }
}
