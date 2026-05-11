<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Expediente extends Model
{
    use HasFactory;

    protected $fillable = [
        'unidade_id',
        'data',
        'descricao',
        'observacoes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'date:Y-m-d',
        ];
    }

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function escalados(): HasMany
    {
        return $this->hasMany(ExpedienteEscalado::class);
    }

    public function getTotalPresentesAttribute(): int
    {
        return $this->escalados()->where('presente', true)->count();
    }

    public function getTotalFaltasAttribute(): int
    {
        return $this->escalados()->where('presente', false)->count();
    }
}
