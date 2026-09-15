<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'id_variacao',
    'id_material',
    'quantidade_por_unidade',
])]
class ProdutoVariacaoMaterial extends Model
{
    protected $table = 'produto_variacao_materiais';

    protected $primaryKey = 'id_composicao';

    protected function casts(): array
    {
        return [
            'quantidade_por_unidade' => 'decimal:3',
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
}
