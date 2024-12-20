<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasUuids;

    /**
     * Indica que a chave primária não é um número incremental.
     *
     * @var bool
     */
    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'clientes';

    protected $fillable = [
        'nome',
        'email',
        'senha',
        'mercado_pago_token',
        'pagbank_email',
        'pagbank_token',
        'pessoa_id',
        'data_inclusao',
        'ultimo_acesso',
        'ativo',
        'data_vencimento',
    ];

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }

    public function maquinas(): HasMany
    {
        return $this->hasMany(Maquina::class, 'cliente_id');
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(Pagamento::class, 'cliente_id');
    }
}
