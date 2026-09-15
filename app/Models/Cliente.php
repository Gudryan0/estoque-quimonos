<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

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
}
