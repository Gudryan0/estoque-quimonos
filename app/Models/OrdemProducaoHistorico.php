<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'id_ordem',
    'id_item',
    'id_usuario',
    'acao',
    'etapa_origem',
    'etapa_destino',
    'observacao',
])]
class OrdemProducaoHistorico extends Model
{
    protected $table = 'ordem_producao_historicos';

    protected $primaryKey = 'id_historico';

    public function ordem(): BelongsTo
    {
        return $this->belongsTo(
            OrdemProducao::class,
            'id_ordem',
            'id_ordem'
        );
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(
            OrdemProducaoItem::class,
            'id_item',
            'id_item'
        );
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'id_usuario',
            'id'
        );
    }
}
