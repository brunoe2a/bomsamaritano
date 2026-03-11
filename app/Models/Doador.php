<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doador extends Model
{
    use HasFactory;

    protected $table = 'doadores';

    protected $fillable = [
        'nome',
        'tipo',
        'cpf_cnpj',
        'telefone',
        'email',
        'endereco',
    ];

    public function lancamentos(): HasMany
    {
        return $this->hasMany(FinanceiroLancamento::class);
    }
}
