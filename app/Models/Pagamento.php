<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pagamento extends Model
{
    use HasUuids;

    /**
     * Indica que a chave primária não é um número incremental.
     *
     * @var bool
     */
    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'pagamentos';

    protected $fillable = [
        'maquina_id',
        'valor',
        'mercadoPagoId',
        'estornado',
        'motivo_estorno',
        'tipo',
        'taxas',
        'cliente_id',
        'operadora',
        'data',
    ];

    public function maquina(): BelongsTo
    {
        return $this->belongsTo(Maquina::class, 'maquina_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
