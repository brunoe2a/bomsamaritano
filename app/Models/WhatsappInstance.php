<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'instance_name',
        'numero',
        'status',
        'qr_code',
        'last_status_at',
    ];

    protected function casts(): array
    {
        return [
            'last_status_at' => 'datetime',
        ];
    }

    public function notificacoes(): HasMany
    {
        return $this->hasMany(WhatsappNotification::class);
    }
}
