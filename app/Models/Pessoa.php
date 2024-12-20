<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pessoa extends Model
{
    use HasUuids;

    /**
     * Indica que a chave primária não é um número incremental.
     *
     * @var bool
     */
    public $incrementing = false;

    protected $keyType = 'string';

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
