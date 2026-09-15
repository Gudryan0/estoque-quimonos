<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'id_variacao',
    'id_material',
    'id_usuario',
    'id_ordem',
    'id_item_producao',
    'tipo_movimentacao',
    'origem',
    'quantidade',
    'observacao',
    'data_movimentacao',
])]
class Movimentacao extends Model
{
    protected $table = 'movimentacoes';

    protected $primaryKey = 'id_movimentacao';

    protected function casts(): array
    {
        return [
            'quantidade' => 'decimal:3',
            'data_movimentacao' => 'datetime',
        ];
    }

    public function variacao(): BelongsTo
    {
        return $this->belongsTo(
            ProdutoVariacao::class,
            'id_variacao',
            'id_variacao'
        );
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(
            Material::class,
            'id_material',
            'id_material'
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

    public function ordem(): BelongsTo
    {
        return $this->belongsTo(
            OrdemProducao::class,
            'id_ordem',
            'id_ordem'
        );
    }

    public function itemProducao(): BelongsTo
    {
        return $this->belongsTo(
            OrdemProducaoItem::class,
            'id_item_producao',
            'id_item'
        );
    }
}
