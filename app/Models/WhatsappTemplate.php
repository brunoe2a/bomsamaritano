<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'conteudo',
        'variaveis',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'variaveis' => 'array',
        ];
    }

    public const VARIAVEIS_DISPONIVEIS = [
        'nome_aluno' => 'Nome do Aluno',
        'nome_responsavel' => 'Nome do Responsável',
        'turma' => 'Turma',
        'curso' => 'Curso',
        'data' => 'Data atual',
        'hora' => 'Hora atual',
        'unidade' => 'Unidade',
    ];

    public function scopeAtivos($query)
    {
        return $query->where('status', 'ativo');
    }
}
