<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'nome_material',
    'categoria',
    'tipo_material',
    'cor',
    'unidade_medida',
    'estoque_minimo',
    'id_fornecedor',
])]
class Material extends Model
{
    protected $table = 'materiais';

    protected $primaryKey = 'id_material';

    protected function casts(): array
    {
        return [
            'quantidade' => 'decimal:3',
            'estoque_minimo' => 'decimal:3',
        ];
    }

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(
            Fornecedor::class,
            'id_fornecedor',
            'id_fornecedor'
        );
    }

    public function movimentacoes(): HasMany
    {
        return $this->hasMany(
            Movimentacao::class,
            'id_material',
            'id_material'
        );
    }

    public function composicoes(): HasMany
    {
        return $this->hasMany(
            ProdutoVariacaoMaterial::class,
            'id_material',
            'id_material'
        );
    }
}
