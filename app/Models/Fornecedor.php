<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'nome_fornecedor',
    'telefone',
    'endereco',
])]
class Fornecedor extends Model
{
    protected $table = 'fornecedores';

    protected $primaryKey = 'id_fornecedor';

    public function materiais(): HasMany
    {
        return $this->hasMany(
            Material::class,
            'id_fornecedor',
            'id_fornecedor'
        );
    }
}
