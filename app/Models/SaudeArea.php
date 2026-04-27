<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaudeArea extends Model
{
    protected $table = 'saude_areas';

    protected $fillable = ['nome', 'status'];

    public function programas(): HasMany
    {
        return $this->hasMany(SaudePrograma::class, 'area_id');
    }

    public function scopeAtivos($q)
    {
        return $q->where('status', 'ativo');
    }
}
