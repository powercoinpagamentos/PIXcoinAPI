<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ConfiguracaoMaquina extends Model
{
    use HasUuids;

    /**
     * Indica que a chave primária não é um número incremental.
     *
     * @var bool
     */
    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'configuracaoMaquina';

    protected $fillable = [
        'codigo',
        'operacao',
        'urlServidor',
        'webhook01',
        'webhook02',
        'rotaConsultaStatusMaq',
        'rotaConsultaAdimplencia',
        'idMaquina',
        'idCliente',
        'valor1',
        'valor2',
        'valor3',
        'valor4',
        'textoEmpresa',
        'corPrincipal',
        'corSecundaria',
        'minValue',
        'maxValue',
        'identificadorMaquininha',
        'serialMaquininha',
        'macaddressMaquininha',
        'operadora',
    ];
}
