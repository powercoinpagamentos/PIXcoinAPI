<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Maquina extends Model
{
    use HasUuids;

    /**
     * Indica que a chave primária não é um número incremental.
     *
     * @var bool
     */
    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'maquinas';

    protected $fillable = [
        'pessoa_id',
        'cliente_id',
        'nome',
        'descricao',
        'store_id',
        'maquininha_serial',
        'estoque',
        'valor_do_pix',
        'valorDoPulso',
        'data_inclusao',
        'ultimo_pagamento_recebido',
        'ultima_requisicao',
        'disabled',
        'tempoHigh',
        'tempoLow',
        'bonusPlay',
        'moves',
        'moves_count',
        'bonus'
    ];

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(Pagamento::class, 'maquina_id')->orderBy('created_at', 'desc');
    }
}
