<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'id_ordem',
    'id_variacao',
    'quantidade',
    'etapa_atual',
    'materiais_consumidos_at',
    'produto_estoque_adicionado_at',
    'descricao_bordado',
    'cor_linha',
    'etiqueta',
    'posicao_etiqueta',
    'responsavel_costura',
    'observacao',
])]
class OrdemProducaoItem extends Model
{
    protected $table = 'ordem_producao_itens';

    protected $primaryKey = 'id_item';

    protected function casts(): array
    {
        return [
            'quantidade' => 'integer',

            'materiais_consumidos_at' =>
                'datetime',

            'produto_estoque_adicionado_at' =>
                'datetime',
        ];
    }

    public function ordem(): BelongsTo
    {
        return $this->belongsTo(
            OrdemProducao::class,
            'id_ordem',
            'id_ordem'
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

    public function historicos(): HasMany
    {
        return $this->hasMany(
            OrdemProducaoHistorico::class,
            'id_item',
            'id_item'
        )
            ->orderByDesc('created_at')
            ->orderByDesc('id_historico');
    }
}
