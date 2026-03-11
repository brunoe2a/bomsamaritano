<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChamadaAluno extends Model
{
    use HasFactory;

    protected $table = 'chamada_aluno';

    protected $fillable = [
        'chamada_id',
        'aluno_id',
        'presente',
        'observacao',
    ];

    protected function casts(): array
    {
        return [
            'presente' => 'boolean',
        ];
    }

    public function chamada(): BelongsTo
    {
        return $this->belongsTo(Chamada::class);
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }
}
