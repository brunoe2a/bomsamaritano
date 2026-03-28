<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Professor extends Model
{
    use HasFactory;
    use \Illuminate\Support\Facades\Storage;

    protected $appends = ['foto_url'];

    protected $table = 'professores';

    protected $fillable = [
        'nome',
        'foto',
        'cpf',
        'rg',
        'data_nascimento',
        'telefone',
        'whatsapp',
        'email',
        'endereco_rua',
        'endereco_numero',
        'endereco_complemento',
        'endereco_bairro',
        'endereco_cidade',
        'endereco_estado',
        'endereco_cep',
        'especialidade',
        'tipo_vinculo',
        'data_inicio',
        'status',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'especialidade' => 'array',
            'data_nascimento' => 'date',
            'data_inicio' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function turmas(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Turma::class, 'professor_turma');
    }

    public function unidades(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Unidade::class, 'professor_unidade');
    }

    public function especialidades(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Especialidade::class, 'especialidade_professor');
    }

    public function chamadas(): HasMany
    {
        return $this->hasMany(Chamada::class);
    }

    public function scopeAtivos($query)
    {
        return $query->where('status', 'ativo');
    }
}
