<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ExpedienteEscalado extends Model
{
    use HasFactory;

    protected $table = 'expediente_escalados';

    protected $fillable = [
        'expediente_id',
        'escalavel_type',
        'escalavel_id',
        'presente',
        'justificativa',
    ];

    protected function casts(): array
    {
        return [
            'presente' => 'boolean',
        ];
    }

    public function expediente(): BelongsTo
    {
        return $this->belongsTo(Expediente::class);
    }

    public function escalavel(): MorphTo
    {
        return $this->morphTo();
    }
}
