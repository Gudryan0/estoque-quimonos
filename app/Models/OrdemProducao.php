<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'codigo',
    'id_cliente',
    'cliente_nome',
    'id_usuario_criador',
    'status',
    'data_prevista',
    'observacao',
    'motivo_cancelamento',
    'cancelada_em',
    'id_usuario_cancelamento',
])]
class OrdemProducao extends Model
{
    protected $table = 'ordens_producao';

    protected $primaryKey = 'id_ordem';

    protected function casts(): array
    {
        return [
            'data_prevista' => 'date',
            'cancelada_em' => 'datetime',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(
            Cliente::class,
            'id_cliente',
            'id_cliente'
        );
    }

    public function criador(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'id_usuario_criador',
            'id'
        );
    }

    public function cancelador(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'id_usuario_cancelamento',
            'id'
        );
    }

    public function itens(): HasMany
    {
        return $this->hasMany(
            OrdemProducaoItem::class,
            'id_ordem',
            'id_ordem'
        );
    }

    public function historicos(): HasMany
    {
        return $this->hasMany(
            OrdemProducaoHistorico::class,
            'id_ordem',
            'id_ordem'
        )
            ->orderByDesc('created_at')
            ->orderByDesc('id_historico');
    }
}
