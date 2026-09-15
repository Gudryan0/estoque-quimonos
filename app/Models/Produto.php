<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'nome_produto',
    'categoria',
    'descricao',
])]
class Produto extends Model
{
    protected $table = 'produtos';

    protected $primaryKey = 'id_produto';

    public function variacoes(): HasMany
    {
        return $this->hasMany(
            ProdutoVariacao::class,
            'id_produto',
            'id_produto'
        );
    }
}
