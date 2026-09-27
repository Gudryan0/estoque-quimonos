<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'nome_cliente',
    'telefone',
    'email',
    'endereco',
])]
class Cliente extends Model
{
    protected $table = 'clientes';

    protected $primaryKey = 'id_cliente';

    public function lotesProducao(): HasMany
    {
        return $this->hasMany(
            OrdemProducaoItem::class,
            'id_cliente',
            'id_cliente'
        );
    }
}
