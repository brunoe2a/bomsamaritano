<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unidade extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'endereco',
        'telefone',
        'email',
        'contato_responsavel',
    ];

    public function professores(): BelongsToMany
    {
        return $this->belongsToMany(Professor::class, 'professor_unidade');
    }

    public function voluntarios(): BelongsToMany
    {
        return $this->belongsToMany(Voluntario::class, 'unidade_voluntario');
    }

    public function turmas(): HasMany
    {
        return $this->hasMany(Turma::class);
    }

    public function lancamentos(): HasMany
    {
        return $this->hasMany(FinanceiroLancamento::class);
    }
}
