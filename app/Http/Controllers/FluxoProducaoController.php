<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Movimentacao;
use App\Models\OrdemProducao;
use App\Models\OrdemProducaoHistorico;
use App\Models\OrdemProducaoItem;
use App\Models\ProdutoVariacao;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FluxoProducaoController extends Controller
{
    private const TRANSICOES = [
        'corte' => 'bordado',
        'bordado' => 'costura',
        'costura' => 'finalizacao',
        'finalizacao' => 'concluido',
    ];

    private const REVERSOES = [
        'bordado' => 'corte',
        'costura' => 'bordado',
        'finalizacao' => 'costura',
        'concluido' => 'finalizacao',
    ];

    public function iniciar(
        OrdemProducao $ordem
    ) {
        $resultado = DB::transaction(
            function () use ($ordem) {

                $ordemBloqueada =
                    OrdemProducao::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $ordem->id_ordem
                        );

                if (
                    $ordemBloqueada->status
                    !== 'planejada'
                ) {
                    return [
                        'erro' =>
                            'Esta ordem já foi iniciada ou não pode ser iniciada.',
                    ];
                }

                $itens =
                    OrdemProducaoItem::query()
                        ->where(
                            'id_ordem',
                            $ordemBloqueada
                                ->id_ordem
                        )
                        ->lockForUpdate()
                        ->get();

                if ($itens->isEmpty()) {
                    return [
                        'erro' =>
                            'Adicione pelo menos um item antes de iniciar a produção.',
                    ];
                }

                /*
                 * consumirMateriais() também funciona como
                 * última validação de integridade.
                 *
                 * Nenhum estoque é alterado antes de todos
                 * os produtos, variações, materiais e
                 * quantidades serem conferidos.
                 */
                $erroConsumo =
                    $this->consumirMateriais(
                        $ordemBloqueada,
                        $itens
                    );

                if ($erroConsumo !== null) {
                    return [
                        'erro' =>
                            $erroConsumo,
                    ];
                }

                $ordemBloqueada->status =
                    'em_producao';

                $ordemBloqueada->save();

                foreach ($itens as $item) {
                    OrdemProducaoHistorico::create([
                        'id_ordem' =>
                            $ordemBloqueada
                                ->id_ordem,

                        'id_item' =>
                            $item->id_item,

                        'id_usuario' =>
                            auth()->id(),

                        'acao' =>
                            'inicio',

                        'etapa_origem' =>
                            null,

                        'etapa_destino' =>
                            'corte',

                        'observacao' =>
                            'Item iniciado na produção. Materiais baixados automaticamente do estoque.',
                    ]);
                }

                return [
                    'erro' => null,
                ];
            }
        );

        if ($resultado['erro']) {
            return redirect()
                ->route(
                    'ordens.show',
                    $ordem
                )
                ->with(
                    'erro',
                    $resultado['erro']
                );
        }

        return redirect()
            ->route(
                'ordens.show',
                $ordem
            )
            ->with(
                'sucesso',
                'Produção iniciada e materiais baixados do estoque com sucesso.'
            );
    }

    public function avancar(
        OrdemProducao $ordem,
        OrdemProducaoItem $item
    ) {
        $resultado = DB::transaction(
            function () use (
                $ordem,
                $item
            ) {

                $ordemBloqueada =
                    OrdemProducao::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $ordem->id_ordem
                        );

                $itemBloqueado =
                    OrdemProducaoItem::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $item->id_item
                        );

                abort_unless(
                    $itemBloqueado->id_ordem
                        ===
                    $ordemBloqueada->id_ordem,
                    404
                );

                if (
                    $ordemBloqueada->status
                    !== 'em_producao'
                ) {
                    return [
                        'erro' =>
                            'A ordem precisa estar em produção para avançar etapas.',
                    ];
                }

                $etapaAtual =
                    $itemBloqueado
                        ->etapa_atual;

                if (
                    !array_key_exists(
                        $etapaAtual,
                        self::TRANSICOES
                    )
                ) {
                    return [
                        'erro' =>
                            'Este item já foi concluído.',
                    ];
                }

                $proximaEtapa =
                    self::TRANSICOES[
                        $etapaAtual
                    ];

                if (
                    $proximaEtapa
                    === 'concluido'
                ) {
                    $this
                        ->adicionarProdutoConcluidoAoEstoque(
                            $ordemBloqueada,
                            $itemBloqueado
                        );
                }

                $itemBloqueado
                    ->etapa_atual =
                    $proximaEtapa;

                $itemBloqueado->save();

                OrdemProducaoHistorico::create([
                    'id_ordem' =>
                        $ordemBloqueada
                            ->id_ordem,

                    'id_item' =>
                        $itemBloqueado
                            ->id_item,

                    'id_usuario' =>
                        auth()->id(),

                    'acao' =>
                        $proximaEtapa
                        === 'concluido'
                            ? 'conclusao'
                            : 'avanco',

                    'etapa_origem' =>
                        $etapaAtual,

                    'etapa_destino' =>
                        $proximaEtapa,

                    'observacao' =>
                        $proximaEtapa
                        === 'concluido'
                            ? 'Produto acabado adicionado automaticamente ao estoque.'
                            : null,
                ]);

                $ordemConcluida = false;

                if (
                    $proximaEtapa
                    === 'concluido'
                ) {
                    $possuiPendentes =
                        OrdemProducaoItem::query()
                            ->where(
                                'id_ordem',
                                $ordemBloqueada
                                    ->id_ordem
                            )
                            ->where(
                                'etapa_atual',
                                '!=',
                                'concluido'
                            )
                            ->exists();

                    if (!$possuiPendentes) {
                        $ordemBloqueada
                            ->status =
                            'concluida';

                        $ordemBloqueada
                            ->save();

                        $ordemConcluida =
                            true;
                    }
                }

                return [
                    'erro' => null,

                    'ordem_concluida' =>
                        $ordemConcluida,
                ];
            }
        );

        if ($resultado['erro']) {
            return redirect()
                ->route(
                    'ordens.show',
                    $ordem
                )
                ->with(
                    'erro',
                    $resultado['erro']
                );
        }

        if (
            $resultado[
                'ordem_concluida'
            ]
        ) {
            return redirect()
                ->route(
                    'ordens.show',
                    $ordem
                )
                ->with(
                    'sucesso',
                    'Último item concluído. A ordem foi encerrada definitivamente e os produtos acabados foram atualizados no estoque.'
                );
        }

        return redirect()
            ->route(
                'ordens.show',
                $ordem
            )
            ->with(
                'sucesso',
                'Etapa do item atualizada com sucesso.'
            );
    }

    public function reverter(
        OrdemProducao $ordem,
        OrdemProducaoItem $item
    ) {
        $resultado = DB::transaction(
            function () use (
                $ordem,
                $item
            ) {

                $ordemBloqueada =
                    OrdemProducao::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $ordem->id_ordem
                        );

                $itemBloqueado =
                    OrdemProducaoItem::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $item->id_item
                        );

                abort_unless(
                    $itemBloqueado->id_ordem
                        ===
                    $ordemBloqueada->id_ordem,
                    404
                );

                if (
                    $ordemBloqueada->status
                    === 'concluida'
                ) {
                    return [
                        'erro' =>
                            'Esta ordem já foi concluída definitivamente e não permite mais reversão de etapas.',
                    ];
                }

                if (
                    $ordemBloqueada->status
                    !== 'em_producao'
                ) {
                    return [
                        'erro' =>
                            'Esta ordem não permite reversão de etapas.',
                    ];
                }

                $etapaAtual =
                    $itemBloqueado
                        ->etapa_atual;

                if (
                    !array_key_exists(
                        $etapaAtual,
                        self::REVERSOES
                    )
                ) {
                    return [
                        'erro' =>
                            'O item já está na primeira etapa da produção.',
                    ];
                }

                $etapaAnterior =
                    self::REVERSOES[
                        $etapaAtual
                    ];

                if (
                    $etapaAtual
                    === 'concluido'
                ) {
                    $erroEstoque =
                        $this
                            ->removerProdutoConcluidoDoEstoque(
                                $ordemBloqueada,
                                $itemBloqueado
                            );

                    if (
                        $erroEstoque
                        !== null
                    ) {
                        return [
                            'erro' =>
                                $erroEstoque,
                        ];
                    }
                }

                $itemBloqueado
                    ->etapa_atual =
                    $etapaAnterior;

                $itemBloqueado->save();

                OrdemProducaoHistorico::create([
                    'id_ordem' =>
                        $ordemBloqueada
                            ->id_ordem,

                    'id_item' =>
                        $itemBloqueado
                            ->id_item,

                    'id_usuario' =>
                        auth()->id(),

                    'acao' =>
                        'reversao',

                    'etapa_origem' =>
                        $etapaAtual,

                    'etapa_destino' =>
                        $etapaAnterior,

                    'observacao' =>
                        $etapaAtual
                        === 'concluido'
                            ? 'Conclusão revertida. Produto acabado retirado automaticamente do estoque.'
                            : 'Etapa revertida manualmente.',
                ]);

                return [
                    'erro' => null,
                ];
            }
        );

        if ($resultado['erro']) {
            return redirect()
                ->route(
                    'ordens.show',
                    $ordem
                )
                ->with(
                    'erro',
                    $resultado['erro']
                );
        }

        return redirect()
            ->route(
                'ordens.show',
                $ordem
            )
            ->with(
                'sucesso',
                'Etapa revertida com sucesso.'
            );
    }

    private function consumirMateriais(
        OrdemProducao $ordem,
        Collection $itens
    ): ?string {
        $necessidades = [];
        $consumos = [];

        /*
         * PRIMEIRA FASE
         *
         * Verifica cada item e monta a necessidade total.
         * Nenhum estoque é alterado nesta fase.
         */
        foreach ($itens as $item) {

            if (
                $item
                    ->materiais_consumidos_at
                !== null
            ) {
                return
                    'Um dos itens desta ordem já possui consumo de materiais registrado.';
            }

            /*
             * A variação é bloqueada durante o início
             * da produção para evitar alterações
             * concorrentes enquanto a OP é validada.
             */
            $variacao =
                ProdutoVariacao::with([
                    'produto',
                    'composicoesMateriais.material',
                ])
                    ->lockForUpdate()
                    ->findOrFail(
                        $item->id_variacao
                    );

            /*
             * Última barreira para produto e variação.
             */
            if (
                !$variacao->produto
                ||
                !$variacao->produto->ativo
            ) {
                return
                    'Não é possível iniciar a produção porque o produto '
                    . (
                        $variacao->produto
                            ? $variacao
                                ->produto
                                ->nome_produto
                            : 'vinculado a um dos itens'
                    )
                    . ' está inativo.';
            }

            if (!$variacao->ativo) {
                return
                    'Não é possível iniciar a produção porque a variação '
                    . $variacao
                        ->produto
                        ->nome_produto
                    . ' - '
                    . $variacao->tamanho
                    . '/'
                    . $variacao->cor
                    . ' está inativa.';
            }

            if (
                $variacao
                    ->composicoesMateriais
                    ->isEmpty()
            ) {
                return
                    'A variação '
                    . $variacao
                        ->produto
                        ->nome_produto
                    . ' - '
                    . $variacao
                        ->tamanho
                    . '/'
                    . $variacao
                        ->cor
                    . ' não possui ficha de consumo de materiais.';
            }

            foreach (
                $variacao
                    ->composicoesMateriais
                as $composicao
            ) {
                $quantidade =
                    round(
                        (float)
                        $composicao
                            ->quantidade_por_unidade
                        *
                        $item->quantidade,
                        3
                    );

                if ($quantidade <= 0) {
                    continue;
                }

                if (
                    !isset(
                        $necessidades[
                            $composicao
                                ->id_material
                        ]
                    )
                ) {
                    $necessidades[
                        $composicao
                            ->id_material
                    ] = 0;
                }

                $necessidades[
                    $composicao
                        ->id_material
                ] += $quantidade;

                $consumos[] = [
                    'item' =>
                        $item,

                    'id_material' =>
                        $composicao
                            ->id_material,

                    'quantidade' =>
                        $quantidade,

                    'descricao' =>
                        $variacao
                            ->produto
                            ->nome_produto
                        . ' '
                        . $variacao
                            ->tamanho
                        . '/'
                        . $variacao
                            ->cor,
                ];
            }
        }

        /*
         * SEGUNDA FASE
         *
         * Os materiais são bloqueados e conferidos.
         * Ainda não houve nenhuma alteração de estoque.
         */
        $materiais =
            Material::query()
                ->whereIn(
                    'id_material',
                    array_keys(
                        $necessidades
                    )
                )
                ->lockForUpdate()
                ->get()
                ->keyBy(
                    'id_material'
                );

        foreach (
            $necessidades
            as $idMaterial => $necessario
        ) {
            $material =
                $materiais->get(
                    $idMaterial
                );

            if (!$material) {
                return
                    'Um material da ficha de consumo não foi encontrado.';
            }

            /*
             * Última barreira para materiais inativos.
             */
            if (!$material->ativo) {
                return
                    'Não é possível iniciar a produção porque o material '
                    . $material->nome_material
                    . ' está inativo. Reative o material ou ajuste a ficha de consumo antes de iniciar a ordem.';
            }

            $disponivel =
                (float)
                $material->quantidade;

            if (
                $disponivel
                < $necessario
            ) {
                return
                    'Estoque insuficiente de '
                    . $material
                        ->nome_material
                    . '. Necessário: '
                    . number_format(
                        $necessario,
                        3,
                        ',',
                        '.'
                    )
                    . ' '
                    . $material
                        ->unidade_medida
                    . '. Disponível: '
                    . number_format(
                        $disponivel,
                        3,
                        ',',
                        '.'
                    )
                    . ' '
                    . $material
                        ->unidade_medida
                    . '.';
            }
        }

        /*
         * TERCEIRA FASE
         *
         * Somente após TODAS as verificações terem
         * passado o estoque é realmente alterado.
         */
        foreach (
            $necessidades
            as $idMaterial => $necessario
        ) {
            $material =
                $materiais->get(
                    $idMaterial
                );

            $material->quantidade =
                round(
                    (float)
                    $material->quantidade
                    - $necessario,
                    3
                );

            $material->save();
        }

        foreach (
            $consumos
            as $consumo
        ) {
            $material =
                $materiais->get(
                    $consumo[
                        'id_material'
                    ]
                );

            Movimentacao::create([
                'id_variacao' =>
                    null,

                'id_material' =>
                    $consumo[
                        'id_material'
                    ],

                'id_usuario' =>
                    auth()->id(),

                'id_ordem' =>
                    $ordem->id_ordem,

                'id_item_producao' =>
                    $consumo[
                        'item'
                    ]->id_item,

                'tipo_movimentacao' =>
                    'saida',

                'origem' =>
                    'producao_consumo',

                'quantidade' =>
                    $consumo[
                        'quantidade'
                    ],

                'observacao' =>
                    'Consumo automático da '
                    . $ordem->codigo
                    . ' para '
                    . $consumo[
                        'descricao'
                    ]
                    . ' - '
                    . $material
                        ->nome_material
                    . '.',

                'data_movimentacao' =>
                    now(),
            ]);
        }

        $momento = now();

        foreach ($itens as $item) {
            $item
                ->materiais_consumidos_at =
                $momento;

            $item->save();
        }

        return null;
    }

    private function adicionarProdutoConcluidoAoEstoque(
        OrdemProducao $ordem,
        OrdemProducaoItem $item
    ): void {
        if (
            $item
                ->produto_estoque_adicionado_at
            !== null
        ) {
            return;
        }

        $variacao =
            ProdutoVariacao::query()
                ->lockForUpdate()
                ->findOrFail(
                    $item->id_variacao
                );

        $variacao->quantidade +=
            $item->quantidade;

        $variacao->save();

        Movimentacao::create([
            'id_variacao' =>
                $variacao->id_variacao,

            'id_material' =>
                null,

            'id_usuario' =>
                auth()->id(),

            'id_ordem' =>
                $ordem->id_ordem,

            'id_item_producao' =>
                $item->id_item,

            'tipo_movimentacao' =>
                'entrada',

            'origem' =>
                'producao_conclusao',

            'quantidade' =>
                $item->quantidade,

            'observacao' =>
                'Entrada automática de produto acabado da '
                . $ordem->codigo
                . '.',

            'data_movimentacao' =>
                now(),
        ]);

        $item
            ->produto_estoque_adicionado_at =
            now();

        $item->save();
    }

    private function removerProdutoConcluidoDoEstoque(
        OrdemProducao $ordem,
        OrdemProducaoItem $item
    ): ?string {
        if (
            $item
                ->produto_estoque_adicionado_at
            === null
        ) {
            return null;
        }

        $variacao =
            ProdutoVariacao::with(
                'produto'
            )
                ->lockForUpdate()
                ->findOrFail(
                    $item->id_variacao
                );

        if (
            $variacao->quantidade
            < $item->quantidade
        ) {
            return
                'Não é possível reverter a conclusão porque o estoque atual de '
                . $variacao
                    ->produto
                    ->nome_produto
                . ' '
                . $variacao->tamanho
                . '/'
                . $variacao->cor
                . ' é menor que as '
                . $item->quantidade
                . ' unidades que seriam retiradas.';
        }

        $variacao->quantidade -=
            $item->quantidade;

        $variacao->save();

        Movimentacao::create([
            'id_variacao' =>
                $variacao->id_variacao,

            'id_material' =>
                null,

            'id_usuario' =>
                auth()->id(),

            'id_ordem' =>
                $ordem->id_ordem,

            'id_item_producao' =>
                $item->id_item,

            'tipo_movimentacao' =>
                'saida',

            'origem' =>
                'producao_reversao',

            'quantidade' =>
                $item->quantidade,

            'observacao' =>
                'Retirada automática devido à reversão da conclusão da '
                . $ordem->codigo
                . '.',

            'data_movimentacao' =>
                now(),
        ]);

        $item
            ->produto_estoque_adicionado_at =
            null;

        $item->save();

        return null;
    }
}
