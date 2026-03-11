<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Turma extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'curso_id',
        'horario_inicio',
        'horario_fim',
        'dias_semana',
        'periodo',
        'capacidade_maxima',
        'ano_letivo',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'dias_semana' => 'array',
            'ano_letivo' => 'integer',
            'capacidade_maxima' => 'integer',
        ];
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function professores(): BelongsToMany
    {
        return $this->belongsToMany(Professor::class, 'professor_turma');
    }

    public function voluntarios(): BelongsToMany
    {
        return $this->belongsToMany(Voluntario::class, 'turma_voluntario');
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class);
    }

    public function alunos(): BelongsToMany
    {
        return $this->belongsToMany(Aluno::class, 'matriculas')
            ->withPivot(['tipo', 'ano_letivo', 'status', 'data_matricula'])
            ->withTimestamps();
    }

    public function chamadas(): HasMany
    {
        return $this->hasMany(Chamada::class);
    }

    public function alunosAtivos(): BelongsToMany
    {
        return $this->alunos()->wherePivot('status', 'ativa');
    }

    public function scopeEmAndamento($query)
    {
        return $query->where('status', 'em_andamento');
    }

    public function scopeAnoAtual($query)
    {
        return $query->where('ano_letivo', date('Y'));
    }
}
