<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'nivel_acesso',
])]
#[Hidden([
    'password',
    'remember_token',
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function movimentacoes(): HasMany
    {
        return $this->hasMany(
            Movimentacao::class,
            'id_usuario'
        );
    }

    public function ordensCriadas(): HasMany
    {
        return $this->hasMany(
            OrdemProducao::class,
            'id_usuario_criador'
        );
    }

    public function historicosProducao(): HasMany
    {
        return $this->hasMany(
            OrdemProducaoHistorico::class,
            'id_usuario'
        );
    }

    public function ordensCanceladas(): HasMany
    {
        return $this->hasMany(
            OrdemProducao::class,
            'id_usuario_cancelamento'
        );
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
