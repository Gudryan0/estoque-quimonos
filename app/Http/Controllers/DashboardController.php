<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Movimentacao;
use App\Models\OrdemProducao;
use App\Models\OrdemProducaoItem;
use App\Models\ProdutoVariacao;

class DashboardController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Consultas-base de itens ativos
        |--------------------------------------------------------------------------
        */

        $variacoesAtivas = function () {
            return ProdutoVariacao::query()
                ->where(
                    'ativo',
                    true
                )
                ->whereHas(
                    'produto',
                    function ($query) {
                        $query->where(
                            'ativo',
                            true
                        );
                    }
                );
        };

        $materiaisAtivos = function () {
            return Material::query()
                ->where(
                    'ativo',
                    true
                );
        };

        /*
        |--------------------------------------------------------------------------
        | Produtos em atenção
        |--------------------------------------------------------------------------
        */

        $produtosSemEstoque = $variacoesAtivas()
            ->where(
                'quantidade',
                '<=',
                0
            )
            ->count();

        $produtosEstoqueBaixo = $variacoesAtivas()
            ->where(
                'quantidade',
                '>',
                0
            )
            ->whereColumn(
                'quantidade',
                '<=',
                'estoque_minimo'
            )
            ->count();

        $produtosCriticos = $variacoesAtivas()
            ->with(
                'produto'
            )
            ->where(function ($query) {
                $query
                    ->where(
                        'quantidade',
                        '<=',
                        0
                    )
                    ->orWhere(function ($query) {
                        $query
                            ->where(
                                'quantidade',
                                '>',
                                0
                            )
                            ->whereColumn(
                                'quantidade',
                                '<=',
                                'estoque_minimo'
                            );
                    });
            })
            ->orderBy('quantidade')
            ->orderBy('id_produto')
            ->orderBy('tamanho')
            ->orderBy('cor')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Materiais em atenção
        |--------------------------------------------------------------------------
        */

        $materiaisSemEstoque = $materiaisAtivos()
            ->where(
                'quantidade',
                '<=',
                0
            )
            ->count();

        $materiaisEstoqueBaixo = $materiaisAtivos()
            ->where(
                'quantidade',
                '>',
                0
            )
            ->where(
                'estoque_minimo',
                '>',
                0
            )
            ->whereColumn(
                'quantidade',
                '<=',
                'estoque_minimo'
            )
            ->count();

        $materiaisCriticos = $materiaisAtivos()
            ->with(
                'fornecedor'
            )
            ->where(function ($query) {
                $query
                    ->where(
                        'quantidade',
                        '<=',
                        0
                    )
                    ->orWhere(function ($query) {
                        $query
                            ->where(
                                'quantidade',
                                '>',
                                0
                            )
                            ->where(
                                'estoque_minimo',
                                '>',
                                0
                            )
                            ->whereColumn(
                                'quantidade',
                                '<=',
                                'estoque_minimo'
                            );
                    });
            })
            ->orderByRaw(
                'CASE
                    WHEN quantidade <= 0 THEN 0
                    ELSE 1
                END'
            )
            ->orderBy('nome_material')
            ->orderBy('cor')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Produção
        |--------------------------------------------------------------------------
        */

        $ordensPlanejadas = OrdemProducao::where(
            'status',
            'planejada'
        )->count();

        $ordensEmProducao = OrdemProducao::where(
            'status',
            'em_producao'
        )->count();

        $ordensConcluidas = OrdemProducao::where(
            'status',
            'concluida'
        )->count();

        $ordensAtrasadas = OrdemProducao::whereIn(
            'status',
            [
                'planejada',
                'em_producao',
            ]
        )
            ->whereNotNull(
                'data_prevista'
            )
            ->whereDate(
                'data_prevista',
                '<',
                today()
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Produção por etapa
        |--------------------------------------------------------------------------
        */

        $dadosEtapas = OrdemProducaoItem::query()
            ->join(
                'ordens_producao',
                'ordem_producao_itens.id_ordem',
                '=',
                'ordens_producao.id_ordem'
            )
            ->where(
                'ordens_producao.status',
                'em_producao'
            )
            ->whereIn(
                'ordem_producao_itens.etapa_atual',
                [
                    'corte',
                    'bordado',
                    'costura',
                    'finalizacao',
                ]
            )
            ->select(
                'ordem_producao_itens.etapa_atual'
            )
            ->selectRaw(
                'COUNT(*) as total_itens'
            )
            ->selectRaw(
                'SUM(
                    ordem_producao_itens.quantidade
                ) as total_unidades'
            )
            ->groupBy(
                'ordem_producao_itens.etapa_atual'
            )
            ->get()
            ->keyBy('etapa_atual');

        $nomesEtapas = [
            'corte' => 'Corte',
            'bordado' => 'Bordado',
            'costura' => 'Costura',
            'finalizacao' => 'Finalização',
        ];

        $producaoPorEtapa = collect(
            $nomesEtapas
        )->map(function (
            $nome,
            $chave
        ) use ($dadosEtapas) {
            $registro = $dadosEtapas->get(
                $chave
            );

            return [
                'chave' => $chave,
                'nome' => $nome,

                'itens' => (int) (
                    $registro?->total_itens
                    ?? 0
                ),

                'unidades' => (int) (
                    $registro?->total_unidades
                    ?? 0
                ),
            ];
        });

        /*
        |--------------------------------------------------------------------------
        | Próximas ordens
        |--------------------------------------------------------------------------
        */

        $ordensAtivas = OrdemProducao::with(
            'cliente'
        )
            ->withCount('itens')
            ->whereIn(
                'status',
                [
                    'planejada',
                    'em_producao',
                ]
            )
            ->orderByRaw(
                'CASE
                    WHEN data_prevista IS NULL
                    THEN 1
                    ELSE 0
                END'
            )
            ->orderBy('data_prevista')
            ->orderBy('created_at')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Últimas movimentações
        |--------------------------------------------------------------------------
        */

        $ultimasMovimentacoes = Movimentacao::with([
            'usuario',
            'variacao.produto',
            'material',
        ])
            ->orderByDesc(
                'data_movimentacao'
            )
            ->orderByDesc(
                'id_movimentacao'
            )
            ->limit(5)
            ->get();

        return view(
            'dashboard.index',
            compact(
                'produtosSemEstoque',
                'produtosEstoqueBaixo',
                'produtosCriticos',

                'materiaisSemEstoque',
                'materiaisEstoqueBaixo',
                'materiaisCriticos',

                'ordensPlanejadas',
                'ordensEmProducao',
                'ordensConcluidas',
                'ordensAtrasadas',
                'producaoPorEtapa',
                'ordensAtivas',

                'ultimasMovimentacoes'
            )
        );
    }
}
