<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceiroCategoria extends Model
{
    use HasFactory;

    protected $table = 'financeiro_categorias';

    protected $fillable = [
        'nome',
        'tipo',
        'descricao',
    ];

    public function lancamentos(): HasMany
    {
        return $this->hasMany(FinanceiroLancamento::class, 'categoria_id');
    }
}
