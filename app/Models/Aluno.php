<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Aluno extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'data_nascimento',
        'ano_escolar',
        'foto',
        'responsavel_id',
        'status',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'data_nascimento' => 'date',
        ];
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(Responsavel::class);
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class);
    }

    public function chamadaAlunos(): HasMany
    {
        return $this->hasMany(ChamadaAluno::class);
    }

    public function getIdadeAttribute(): ?int
    {
        if (! $this->data_nascimento) {
            return null;
        }

        return $this->data_nascimento->age;
    }

    public function scopeAtivos($query)
    {
        return $query->where('status', 'ativo');
    }

    public function scopeAniversariantesMes($query, ?int $mes = null)
    {
        $mes = $mes ?? Carbon::now()->month;

        return $query->whereMonth('data_nascimento', $mes);
    }

    public function scopeAniversariantesSemana($query)
    {
        $inicio = Carbon::now()->startOfWeek();
        $fim = Carbon::now()->endOfWeek();

        return $query->whereRaw('DATE_FORMAT(data_nascimento, "%m-%d") BETWEEN ? AND ?', [
            $inicio->format('m-d'),
            $fim->format('m-d'),
        ]);
    }
}
