<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaudePrograma extends Model
{
    protected $table = 'saude_programas';

    protected $fillable = [
        'nome',
        'area_id',
        'descricao',
        'cor',
        'status',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(SaudeArea::class, 'area_id');
    }

    public function convocacoes(): HasMany
    {
        return $this->hasMany(SaudeConvocacao::class, 'programa_id');
    }

    public function atendimentos(): HasMany
    {
        return $this->hasMany(SaudeAtendimento::class, 'programa_id');
    }

    public function scopeAtivos($q)
    {
        return $q->where('status', 'ativo');
    }
}
