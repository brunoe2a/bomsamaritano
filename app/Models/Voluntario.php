<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Voluntario extends Model
{
    use HasFactory;

    protected $appends = ['foto_url'];

    protected $fillable = [
        'nome',
        'foto',
        'cpf',
        'rg',
        'data_nascimento',
        'telefone',
        'whatsapp',
        'email',
        'endereco_rua',
        'endereco_numero',
        'endereco_complemento',
        'endereco_bairro',
        'endereco_cidade',
        'endereco_estado',
        'endereco_cep',
        'area_atuacao',
        'habilidade',
        'data_inicio',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'data_nascimento' => 'date',
            'data_inicio' => 'date',
        ];
    }

    public function scopeAtivos($query)
    {
        return $query->where('status', 'ativo');
    }

    public function turmas(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Turma::class, 'turma_voluntario');
    }

    public function unidades(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Unidade::class, 'unidade_voluntario');
    }

    public function habilidades(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Habilidade::class, 'habilidade_voluntario');
    }

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? Storage::url($this->foto) : null;
    }
}
