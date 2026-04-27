<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'whatsapp_instance_id',
        'whatsapp_template_id',
        'aluno_id',
        'responsavel_id',
        'user_id',
        'numero',
        'numero_normalizado',
        'mensagem',
        'status',
        'numero_valido',
        'erro',
        'evolution_message_id',
        'enviado_em',
    ];

    protected function casts(): array
    {
        return [
            'numero_valido' => 'boolean',
            'enviado_em' => 'datetime',
        ];
    }

    public function instance(): BelongsTo
    {
        return $this->belongsTo(WhatsappInstance::class, 'whatsapp_instance_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(WhatsappTemplate::class, 'whatsapp_template_id');
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(Responsavel::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
