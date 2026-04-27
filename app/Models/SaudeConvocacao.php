<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaudeConvocacao extends Model
{
    protected $table = 'saude_convocacoes';

    protected $fillable = [
        'programa_id',
        'titulo',
        'data',
        'hora',
        'local',
        'profissional',
        'observacoes',
        'status',
        'unidade_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'date',
        ];
    }

    public function programa(): BelongsTo
    {
        return $this->belongsTo(SaudePrograma::class, 'programa_id');
    }

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function alunos(): BelongsToMany
    {
        return $this->belongsToMany(Aluno::class, 'saude_convocacao_aluno', 'convocacao_id', 'aluno_id')
            ->withPivot(['id', 'presente', 'observacao'])
            ->withTimestamps();
    }

    public function atendimentos(): HasMany
    {
        return $this->hasMany(SaudeAtendimento::class, 'convocacao_id');
    }
}
