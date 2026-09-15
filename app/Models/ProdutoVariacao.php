<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'id_produto',
    'tamanho',
    'cor',
    'estoque_minimo',
])]
class ProdutoVariacao extends Model
{
    protected $table = 'produto_variacoes';

    protected $primaryKey = 'id_variacao';

    protected function casts(): array
    {
        return [
            'quantidade' => 'integer',
            'estoque_minimo' => 'integer',
        ];
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(
            Produto::class,
            'id_produto',
            'id_produto'
        );
    }

    public function movimentacoes(): HasMany
    {
        return $this->hasMany(
            Movimentacao::class,
            'id_variacao',
            'id_variacao'
        );
    }

    public function composicoesMateriais(): HasMany
    {
        return $this->hasMany(
            ProdutoVariacaoMaterial::class,
            'id_variacao',
            'id_variacao'
        );
    }
}
