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

                $lotes =
                    OrdemProducaoItem::query()
                        ->where(
                            'id_ordem',
                            $ordemBloqueada->id_ordem
                        )
                        ->orderBy('id_item')
                        ->lockForUpdate()
                        ->get();

                if ($lotes->isEmpty()) {
                    return [
                        'erro' =>
                            'Adicione pelo menos um lote antes de iniciar a produção.',
                    ];
                }

                /*
                 * Valida todos os lotes e todas as
                 * necessidades de materiais antes
                 * de realizar qualquer baixa.
                 */
                $erroConsumo =
                    $this->consumirMateriais(
                        $ordemBloqueada,
                        $lotes
                    );

                if ($erroConsumo !== null) {
                    return [
                        'erro' => $erroConsumo,
                    ];
                }

                $ordemBloqueada->status =
                    'em_producao';

                $ordemBloqueada->save();

                foreach ($lotes as $lote) {
                    OrdemProducaoHistorico::create([
                        'id_ordem' =>
                            $ordemBloqueada->id_ordem,

                        'id_item' =>
                            $lote->id_item,

                        'id_usuario' =>
                            auth()->id(),

                        'acao' =>
                            'inicio',

                        'etapa_origem' =>
                            null,

                        'etapa_destino' =>
                            'corte',

                        'observacao' =>
                            'Lote iniciado na produção. Materiais baixados automaticamente do estoque.',
                    ]);
                }

                return [
                    'erro' => null,
                ];
            }
        );

        if ($resultado['erro'] !== null) {
            return redirect()
                ->route('ordens.show', $ordem)
                ->with(
                    'erro',
                    $resultado['erro']
                );
        }

        return redirect()
            ->route('ordens.show', $ordem)
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
            function () use ($ordem, $item) {
                $ordemBloqueada =
                    OrdemProducao::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $ordem->id_ordem
                        );

                $lote =
                    OrdemProducaoItem::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $item->id_item
                        );

                abort_unless(
                    (int) $lote->id_ordem
                        ===
                    (int) $ordemBloqueada->id_ordem,
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

                $etapaAtual = $lote->etapa_atual;

                if (
                    !array_key_exists(
                        $etapaAtual,
                        self::TRANSICOES
                    )
                ) {
                    return [
                        'erro' =>
                            'Este lote já foi concluído.',
                    ];
                }

                $proximaEtapa =
                    self::TRANSICOES[$etapaAtual];

                if ($proximaEtapa === 'concluido') {
                    $erroEstoque =
                        $this
                            ->adicionarProdutoConcluidoAoEstoque(
                                $ordemBloqueada,
                                $lote
                            );

                    if ($erroEstoque !== null) {
                        return [
                            'erro' => $erroEstoque,
                        ];
                    }
                }

                /*
                 * A etapa pertence ao lote inteiro.
                 * Seus tamanhos não possuem etapas
                 * independentes.
                 */
                $lote->etapa_atual =
                    $proximaEtapa;

                $lote->save();

                OrdemProducaoHistorico::create([
                    'id_ordem' =>
                        $ordemBloqueada->id_ordem,

                    'id_item' =>
                        $lote->id_item,

                    'id_usuario' =>
                        auth()->id(),

                    'acao' =>
                        $proximaEtapa === 'concluido'
                            ? 'conclusao'
                            : 'avanco',

                    'etapa_origem' =>
                        $etapaAtual,

                    'etapa_destino' =>
                        $proximaEtapa,

                    'observacao' =>
                        $proximaEtapa === 'concluido'
                            ? 'Todas as variações do lote foram adicionadas automaticamente ao estoque.'
                            : null,
                ]);

                $ordemConcluida = false;

                if ($proximaEtapa === 'concluido') {
                    $possuiPendentes =
                        OrdemProducaoItem::query()
                            ->where(
                                'id_ordem',
                                $ordemBloqueada->id_ordem
                            )
                            ->where(
                                'etapa_atual',
                                '!=',
                                'concluido'
                            )
                            ->exists();

                    if (!$possuiPendentes) {
                        $ordemBloqueada->status =
                            'concluida';

                        $ordemBloqueada->save();

                        $ordemConcluida = true;
                    }
                }

                return [
                    'erro' => null,

                    'ordem_concluida' =>
                        $ordemConcluida,
                ];
            }
        );

        if ($resultado['erro'] !== null) {
            return redirect()
                ->route('ordens.show', $ordem)
                ->with(
                    'erro',
                    $resultado['erro']
                );
        }

        if ($resultado['ordem_concluida']) {
            return redirect()
                ->route('ordens.show', $ordem)
                ->with(
                    'sucesso',
                    'Último lote concluído. A ordem foi encerrada definitivamente e os produtos acabados foram atualizados no estoque.'
                );
        }

        return redirect()
            ->route('ordens.show', $ordem)
            ->with(
                'sucesso',
                'Etapa do lote atualizada com sucesso.'
            );
    }

    public function reverter(
        OrdemProducao $ordem,
        OrdemProducaoItem $item
    ) {
        $resultado = DB::transaction(
            function () use ($ordem, $item) {
                $ordemBloqueada =
                    OrdemProducao::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $ordem->id_ordem
                        );

                $lote =
                    OrdemProducaoItem::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $item->id_item
                        );

                abort_unless(
                    (int) $lote->id_ordem
                        ===
                    (int) $ordemBloqueada->id_ordem,
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

                $etapaAtual = $lote->etapa_atual;

                if (
                    !array_key_exists(
                        $etapaAtual,
                        self::REVERSOES
                    )
                ) {
                    return [
                        'erro' =>
                            'O lote já está na primeira etapa da produção.',
                    ];
                }

                $etapaAnterior =
                    self::REVERSOES[$etapaAtual];

                if ($etapaAtual === 'concluido') {
                    $erroEstoque =
                        $this
                            ->removerProdutoConcluidoDoEstoque(
                                $ordemBloqueada,
                                $lote
                            );

                    if ($erroEstoque !== null) {
                        return [
                            'erro' => $erroEstoque,
                        ];
                    }
                }

                $lote->etapa_atual =
                    $etapaAnterior;

                $lote->save();

                OrdemProducaoHistorico::create([
                    'id_ordem' =>
                        $ordemBloqueada->id_ordem,

                    'id_item' =>
                        $lote->id_item,

                    'id_usuario' =>
                        auth()->id(),

                    'acao' =>
                        'reversao',

                    'etapa_origem' =>
                        $etapaAtual,

                    'etapa_destino' =>
                        $etapaAnterior,

                    'observacao' =>
                        $etapaAtual === 'concluido'
                            ? 'Conclusão revertida. Todas as variações do lote foram retiradas do estoque de produtos acabados.'
                            : 'Etapa do lote revertida manualmente.',
                ]);

                return [
                    'erro' => null,
                ];
            }
        );

        if ($resultado['erro'] !== null) {
            return redirect()
                ->route('ordens.show', $ordem)
                ->with(
                    'erro',
                    $resultado['erro']
                );
        }

        return redirect()
            ->route('ordens.show', $ordem)
            ->with(
                'sucesso',
                'Etapa do lote revertida com sucesso.'
            );
    }

    /*
     * Carrega e valida todas as variações
     * pertencentes a um lote.
     *
     * A validação de atividade é exigida
     * somente ao iniciar a produção.
     *
     * Na conclusão e na reversão, um produto
     * arquivado não deve impedir a correção
     * de uma movimentação já realizada.
     */
    private function validarVariacoesLote(
        OrdemProducaoItem $lote,
        bool $exigirAtivas = false
    ): array {
        $linhas =
            $lote->variacoes()
                ->orderBy('id_variacao')
                ->lockForUpdate()
                ->get();

        if ($linhas->isEmpty()) {
            return [
                'erro' =>
                    'O lote #' . $lote->id_item
                    . ' não possui tamanhos cadastrados.',

                'linhas' => collect(),
            ];
        }

        $resultado = collect();
        $total = 0;

        foreach ($linhas as $linha) {
            $relacoes = $exigirAtivas
                ? [
                    'produto',
                    'composicoesMateriais',
                ]
                : [
                    'produto',
                ];

            $variacao =
                ProdutoVariacao::query()
                    ->with($relacoes)
                    ->lockForUpdate()
                    ->find($linha->id_variacao);

            if (
                !$variacao
                || !$variacao->produto
            ) {
                return [
                    'erro' =>
                        'Uma das variações do lote #'
                        . $lote->id_item
                        . ' não foi encontrada.',

                    'linhas' => collect(),
                ];
            }

            if (
                (int) $variacao->id_produto
                    !== (int) $lote->id_produto
                || $variacao->cor !== $lote->cor
            ) {
                return [
                    'erro' =>
                        'Uma das variações do lote #'
                        . $lote->id_item
                        . ' não corresponde ao produto e à cor registrados.',

                    'linhas' => collect(),
                ];
            }

            $quantidade = (int) $linha->quantidade;

            if ($quantidade < 1) {
                return [
                    'erro' =>
                        'O lote #'
                        . $lote->id_item
                        . ' possui uma quantidade inválida.',

                    'linhas' => collect(),
                ];
            }

            if ($exigirAtivas) {
                if (!$variacao->produto->ativo) {
                    return [
                        'erro' =>
                            'Não é possível iniciar a produção porque o produto '
                            . $variacao->produto->nome_produto
                            . ' está inativo.',

                        'linhas' => collect(),
                    ];
                }

                if (!$variacao->ativo) {
                    return [
                        'erro' =>
                            'Não é possível iniciar a produção porque a variação '
                            . $variacao->produto->nome_produto
                            . ' - '
                            . $variacao->tamanho
                            . '/'
                            . $variacao->cor
                            . ' está inativa.',

                        'linhas' => collect(),
                    ];
                }
            }

            $total += $quantidade;

            $resultado->push([
                'variacao' => $variacao,
                'quantidade' => $quantidade,
            ]);
        }

        /*
         * O total armazenado no lote precisa
         * corresponder à soma de suas linhas.
         */
        if ($total !== (int) $lote->quantidade) {
            return [
                'erro' =>
                    'O total do lote #'
                    . $lote->id_item
                    . ' não corresponde à soma das quantidades por tamanho.',

                'linhas' => collect(),
            ];
        }

        return [
            'erro' => null,
            'linhas' => $resultado,
        ];
    }

    private function consumirMateriais(
        OrdemProducao $ordem,
        Collection $lotes
    ): ?string {
        $necessidades = [];
        $consumos = [];

        /*
         * PRIMEIRA FASE
         *
         * Confere todos os lotes e todas
         * as suas variações.
         *
         * Nenhum estoque é alterado aqui.
         */
        foreach ($lotes as $lote) {
            if (
                $lote->materiais_consumidos_at
                !== null
            ) {
                return
                    'Um dos lotes desta ordem já possui consumo de materiais registrado.';
            }

            if (
                $lote->produto_estoque_adicionado_at
                !== null
            ) {
                return
                    'Um dos lotes desta ordem já possui entrada de produto acabado registrada.';
            }

            $dadosLote =
                $this->validarVariacoesLote(
                    $lote,
                    true
                );

            if ($dadosLote['erro'] !== null) {
                return $dadosLote['erro'];
            }

            foreach (
                $dadosLote['linhas']
                as $linha
            ) {
                $variacao = $linha['variacao'];
                $quantidadePecas = $linha['quantidade'];

                if (
                    $variacao
                        ->composicoesMateriais
                        ->isEmpty()
                ) {
                    return
                        'A variação '
                        . $variacao->produto->nome_produto
                        . ' - '
                        . $variacao->tamanho
                        . '/'
                        . $variacao->cor
                        . ' não possui ficha de consumo de materiais.';
                }

                foreach (
                    $variacao->composicoesMateriais
                    as $composicao
                ) {
                    $quantidadeMaterial = round(
                        (float) $composicao
                            ->quantidade_por_unidade
                        * $quantidadePecas,
                        3
                    );

                    if ($quantidadeMaterial <= 0) {
                        continue;
                    }

                    $idMaterial =
                        (int) $composicao->id_material;

                    $necessidades[$idMaterial] =
                        round(
                            ($necessidades[$idMaterial] ?? 0)
                            + $quantidadeMaterial,
                            3
                        );

                    $consumos[] = [
                        'lote' => $lote,

                        'id_material' =>
                            $idMaterial,

                        'quantidade' =>
                            $quantidadeMaterial,

                        'descricao' =>
                            $variacao->produto->nome_produto
                            . ' '
                            . $variacao->tamanho
                            . '/'
                            . $variacao->cor,
                    ];
                }
            }
        }

        /*
         * SEGUNDA FASE
         *
         * Bloqueia os materiais e verifica
         * se existe estoque suficiente para
         * a OP inteira.
         *
         * Ainda não há alterações de estoque.
         */
        $materiais =
            Material::query()
                ->whereIn(
                    'id_material',
                    array_keys($necessidades)
                )
                ->orderBy('id_material')
                ->lockForUpdate()
                ->get()
                ->keyBy('id_material');

        foreach (
            $necessidades
            as $idMaterial => $necessario
        ) {
            $material =
                $materiais->get($idMaterial);

            if (!$material) {
                return
                    'Um material da ficha de consumo não foi encontrado.';
            }

            if (!$material->ativo) {
                return
                    'Não é possível iniciar a produção porque o material '
                    . $material->nome_material
                    . ' está inativo. Reative o material ou ajuste a ficha de consumo antes de iniciar a ordem.';
            }

            $disponivel =
                (float) $material->quantidade;

            if ($disponivel < $necessario) {
                return
                    'Estoque insuficiente de '
                    . $material->nome_material
                    . '. Necessário: '
                    . number_format(
                        $necessario,
                        3,
                        ',',
                        '.'
                    )
                    . ' '
                    . $material->unidade_medida
                    . '. Disponível: '
                    . number_format(
                        $disponivel,
                        3,
                        ',',
                        '.'
                    )
                    . ' '
                    . $material->unidade_medida
                    . '.';
            }
        }

        /*
         * TERCEIRA FASE
         *
         * Todas as verificações passaram.
         * Efetua a baixa agregada por material.
         */
        foreach (
            $necessidades
            as $idMaterial => $necessario
        ) {
            $material =
                $materiais->get($idMaterial);

            $material->quantidade = round(
                (float) $material->quantidade
                - $necessario,
                3
            );

            $material->save();
        }

        /*
         * Cada consumo fica associado ao
         * lote que o originou.
         *
         * A descrição identifica também
         * a variação correspondente.
         */
        foreach ($consumos as $consumo) {
            $material =
                $materiais->get(
                    $consumo['id_material']
                );

            Movimentacao::create([
                'id_variacao' => null,

                'id_material' =>
                    $consumo['id_material'],

                'id_usuario' =>
                    auth()->id(),

                'id_ordem' =>
                    $ordem->id_ordem,

                'id_item_producao' =>
                    $consumo['lote']->id_item,

                'tipo_movimentacao' =>
                    'saida',

                'origem' =>
                    'producao_consumo',

                'quantidade' =>
                    $consumo['quantidade'],

                'observacao' =>
                    'Consumo automático da '
                    . $ordem->codigo
                    . ' para '
                    . $consumo['descricao']
                    . ' - '
                    . $material->nome_material
                    . '.',

                'data_movimentacao' =>
                    now(),
            ]);
        }

        $momento = now();

        foreach ($lotes as $lote) {
            $lote->materiais_consumidos_at =
                $momento;

            $lote->save();
        }

        return null;
    }

    private function adicionarProdutoConcluidoAoEstoque(
        OrdemProducao $ordem,
        OrdemProducaoItem $lote
    ): ?string {
        if (
            $lote->materiais_consumidos_at
            === null
        ) {
            return
                'Não é possível concluir um lote sem consumo de materiais registrado.';
        }

        if (
            $lote->produto_estoque_adicionado_at
            !== null
        ) {
            return
                'Este lote já possui entrada de produto acabado registrada.';
        }

        $dadosLote =
            $this->validarVariacoesLote($lote);

        if ($dadosLote['erro'] !== null) {
            return $dadosLote['erro'];
        }

        /*
         * Cada tamanho recebe sua própria
         * entrada no estoque.
         *
         * Todas as entradas fazem parte
         * da mesma transação do avanço.
         */
        foreach (
            $dadosLote['linhas']
            as $linha
        ) {
            $variacao = $linha['variacao'];
            $quantidade = $linha['quantidade'];

            $variacao->quantidade =
                (int) $variacao->quantidade
                + $quantidade;

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
                    $lote->id_item,

                'tipo_movimentacao' =>
                    'entrada',

                'origem' =>
                    'producao_conclusao',

                'quantidade' =>
                    $quantidade,

                'observacao' =>
                    'Entrada automática de produto acabado da '
                    . $ordem->codigo
                    . ' - '
                    . $variacao->produto->nome_produto
                    . ' '
                    . $variacao->tamanho
                    . '/'
                    . $variacao->cor
                    . '.',

                'data_movimentacao' =>
                    now(),
            ]);
        }

        /*
         * O lote é marcado somente depois
         * que todas as variações foram
         * adicionadas ao estoque.
         */
        $lote->produto_estoque_adicionado_at =
            now();

        $lote->save();

        return null;
    }

    private function removerProdutoConcluidoDoEstoque(
        OrdemProducao $ordem,
        OrdemProducaoItem $lote
    ): ?string {
        if (
            $lote->produto_estoque_adicionado_at
            === null
        ) {
            return
                'Não há entrada de produto acabado registrada para reverter este lote.';
        }

        $dadosLote =
            $this->validarVariacoesLote($lote);

        if ($dadosLote['erro'] !== null) {
            return $dadosLote['erro'];
        }

        /*
         * PRIMEIRA FASE
         *
         * Confere o estoque de todas as
         * variações antes de retirar
         * qualquer unidade.
         */
        foreach (
            $dadosLote['linhas']
            as $linha
        ) {
            $variacao = $linha['variacao'];
            $quantidade = $linha['quantidade'];

            if (
                (int) $variacao->quantidade
                < $quantidade
            ) {
                return
                    'Não é possível reverter a conclusão porque o estoque atual de '
                    . $variacao->produto->nome_produto
                    . ' '
                    . $variacao->tamanho
                    . '/'
                    . $variacao->cor
                    . ' é menor que as '
                    . $quantidade
                    . ' unidades que seriam retiradas.';
            }
        }

        /*
         * SEGUNDA FASE
         *
         * Todas as variações possuem estoque.
         * Executa as retiradas e registra
         * as movimentações correspondentes.
         */
        foreach (
            $dadosLote['linhas']
            as $linha
        ) {
            $variacao = $linha['variacao'];
            $quantidade = $linha['quantidade'];

            $variacao->quantidade =
                (int) $variacao->quantidade
                - $quantidade;

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
                    $lote->id_item,

                'tipo_movimentacao' =>
                    'saida',

                'origem' =>
                    'producao_reversao',

                'quantidade' =>
                    $quantidade,

                'observacao' =>
                    'Retirada automática devido à reversão da conclusão da '
                    . $ordem->codigo
                    . ' - '
                    . $variacao->produto->nome_produto
                    . ' '
                    . $variacao->tamanho
                    . '/'
                    . $variacao->cor
                    . '.',

                'data_movimentacao' =>
                    now(),
            ]);
        }

        $lote->produto_estoque_adicionado_at =
            null;

        $lote->save();

        return null;
    }
}
