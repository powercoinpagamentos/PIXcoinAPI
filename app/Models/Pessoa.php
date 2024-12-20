<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pessoa extends Model
{
    protected $table = 'pessoas';

    protected $fillable = [
        'nome',
        'email',
        'senha',
        'data_inclusao',
        'ultimo_acesso',
    ];

    public function maquinas(): HasMany
    {
        return $this->hasMany(Maquina::class, 'pessoa_id');
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class, 'pessoa_id');
    }
}
