<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaudeAtendimento extends Model
{
    protected $table = 'saude_atendimentos';

    protected $fillable = [
        'aluno_id',
        'programa_id',
        'data_atendimento',
        'profissional',
        'observacoes',
        'convocacao_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'data_atendimento' => 'date',
        ];
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }

    public function programa(): BelongsTo
    {
        return $this->belongsTo(SaudePrograma::class, 'programa_id');
    }

    public function convocacao(): BelongsTo
    {
        return $this->belongsTo(SaudeConvocacao::class, 'convocacao_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
