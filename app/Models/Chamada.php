<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chamada extends Model
{
    use HasFactory;

    protected $fillable = [
        'turma_id',
        'data',
        'professor_id',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'date',
        ];
    }

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class);
    }

    public function professor(): BelongsTo
    {
        return $this->belongsTo(Professor::class);
    }

    public function presencas(): HasMany
    {
        return $this->hasMany(ChamadaAluno::class);
    }

    public function getTotalPresentesAttribute(): int
    {
        return $this->presencas()->where('presente', true)->count();
    }

    public function getTotalAusentesAttribute(): int
    {
        return $this->presencas()->where('presente', false)->count();
    }
}
