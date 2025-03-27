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
        'mercadoPagoToken',
        'pagbankEmail',
        'pagbankToken',
        'pessoa_id',
        'data_inclusao',
        'ultimo_acesso',
        'ativo',
        'dataVencimento',
        'parent_id',
        'can_delete_payments',
        'can_add_remote_credit',
        'can_add_edit_machine',
        'is_employee'
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'parent_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Cliente::class, 'parent_id');
    }
}
