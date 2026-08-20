<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Aluno extends Model
{
    use HasFactory;

    protected $appends = ['foto_url'];

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

    public function atendimentosSaude(): HasMany
    {
        return $this->hasMany(SaudeAtendimento::class);
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

    /**
     * Aniversariantes da semana corrente.
     *
     * Compara dia e mês pelos helpers do Eloquent (portáveis entre MySQL e SQLite)
     * em vez de DATE_FORMAT, que só existe no MySQL. Enumerar os 7 dias também
     * resolve a virada de ano: comparar '12-29' BETWEEN '01-04' não retornava nada.
     */
    public function scopeAniversariantesSemana($query)
    {
        $dias = CarbonPeriod::create(Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek());

        return $query->where(function ($q) use ($dias) {
            foreach ($dias as $dia) {
                $q->orWhere(fn ($sub) => $sub
                    ->whereMonth('data_nascimento', $dia->month)
                    ->whereDay('data_nascimento', $dia->day));
            }
        });
    }

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? Storage::url($this->foto) : null;
    }
}
