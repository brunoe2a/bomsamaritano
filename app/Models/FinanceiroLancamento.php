<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceiroLancamento extends Model
{
    use HasFactory;

    protected $table = 'financeiro_lancamentos';

    protected $fillable = [
        'tipo',
        'categoria_id',
        'doador_id',
        'descricao',
        'valor',
        'data',
        'comprovante',
        'user_id',
        'unidade_id',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'date',
            'valor' => 'decimal:2',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(FinanceiroCategoria::class, 'categoria_id');
    }

    public function doador(): BelongsTo
    {
        return $this->belongsTo(Doador::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }

    public function scopeEntradas($query)
    {
        return $query->where('tipo', 'entrada');
    }

    public function scopeSaidas($query)
    {
        return $query->where('tipo', 'saida');
    }
}
