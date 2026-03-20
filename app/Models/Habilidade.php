<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Habilidade extends Model
{
    //
    use HasFactory;

    protected $fillable = ['nome'];

    public function voluntarios()
    {
        return $this->belongsToMany(Voluntario::class, 'habilidade_voluntario');
    }
}
