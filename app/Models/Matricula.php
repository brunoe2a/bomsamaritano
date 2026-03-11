<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Matricula extends Model
{
    use HasFactory;

    protected $fillable = [
        'aluno_id',
        'turma_id',
        'tipo',
        'ano_letivo',
        'status',
        'data_matricula',
    ];

    protected function casts(): array
    {
        return [
            'data_matricula' => 'date',
            'ano_letivo' => 'integer',
        ];
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class);
    }

    public function scopeAtivas($query)
    {
        return $query->where('status', 'ativa');
    }
}
