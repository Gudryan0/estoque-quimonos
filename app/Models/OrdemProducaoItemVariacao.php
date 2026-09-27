<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'id_item',
    'id_variacao',
    'quantidade',
])]
class OrdemProducaoItemVariacao extends Model
{
    protected $table =
        'ordem_producao_item_variacoes';

    protected $primaryKey =
        'id_item_variacao';

    protected function casts(): array
    {
        return [
            'quantidade' => 'integer',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(
            OrdemProducaoItem::class,
            'id_item',
            'id_item'
        );
    }

    public function variacao(): BelongsTo
    {
        return $this->belongsTo(
            ProdutoVariacao::class,
            'id_variacao',
            'id_variacao'
        );
    }
}
