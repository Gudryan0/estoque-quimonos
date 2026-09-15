<?php

namespace App\Http\Controllers;

use App\Models\Movimentacao;
use App\Models\OrdemProducao;
use App\Models\OrdemProducaoHistorico;
use App\Models\OrdemProducaoItem;
use App\Models\ProdutoVariacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatorioController extends Controller
{
    public function index(Request $request)
    {
        $periodo = (int) $request->input(
            'periodo',
            30
        );

        if (
            !in_array(
                $periodo,
                [7, 30, 60, 90],
                true
            )
        ) {
            $periodo = 30;
        }

        $inicio = now()
            ->subDays($periodo - 1)
            ->startOfDay();

        $fim = now()->endOfDay();

        /*
        |--------------------------------------------------------------------------
        | Movimentações gerais
        |--------------------------------------------------------------------------
        */

        $movimentacoesPeriodo =
            Movimentacao::query()
                ->whereBetween(
                    'data_movimentacao',
                    [
                        $inicio,
                        $fim,
                    ]
                );

        $totalMovimentacoes = (
            clone $movimentacoesPeriodo
        )->count();

        /*
        |--------------------------------------------------------------------------
        | Entradas de produtos acabados
        |--------------------------------------------------------------------------
        */

        $entradasProdutos = (float) (
            clone $movimentacoesPeriodo
        )
            ->whereNotNull(
                'id_variacao'
            )
            ->where(
                'tipo_movimentacao',
                'entrada'
            )
            ->sum('quantidade');

        /*
        |--------------------------------------------------------------------------
        | Saídas consideradas como demanda
        |--------------------------------------------------------------------------
        |
        | A saída causada pela reversão de uma conclusão é uma correção
        | técnica de estoque e não representa demanda.
        |
        | As demais saídas de produtos são consideradas demanda pelo modelo
        | atual do sistema.
        |
        */

        $saidasProdutos = (float) (
            clone $movimentacoesPeriodo
        )
            ->whereNotNull(
                'id_variacao'
            )
            ->where(
                'tipo_movimentacao',
                'saida'
            )
            ->where(
                'origem',
                '!=',
                'producao_reversao'
            )
            ->sum('quantidade');

        /*
        |--------------------------------------------------------------------------
        | Materiais por unidade de medida
        |--------------------------------------------------------------------------
        |
        | Unidades incompatíveis não devem ser somadas entre si.
        |
        */

        $resumoMateriaisPorUnidade =
            DB::table('movimentacoes')
                ->join(
                    'materiais',
                    'movimentacoes.id_material',
                    '=',
                    'materiais.id_material'
                )
                ->whereNotNull(
                    'movimentacoes.id_material'
                )
                ->whereBetween(
                    'movimentacoes.data_movimentacao',
                    [
                        $inicio,
                        $fim,
                    ]
                )
                ->select(
                    'materiais.unidade_medida'
                )
                ->selectRaw(
                    "
                    SUM(
                        CASE
                            WHEN movimentacoes.tipo_movimentacao = 'entrada'
                            THEN movimentacoes.quantidade
                            ELSE 0
                        END
                    ) as total_entradas
                    "
                )
                ->selectRaw(
                    "
                    SUM(
                        CASE
                            WHEN movimentacoes.tipo_movimentacao = 'saida'
                            THEN movimentacoes.quantidade
                            ELSE 0
                        END
                    ) as total_saidas
                    "
                )
                ->groupBy(
                    'materiais.unidade_medida'
                )
                ->orderBy(
                    'materiais.unidade_medida'
                )
                ->get();

        /*
        |--------------------------------------------------------------------------
        | Saídas por variação
        |--------------------------------------------------------------------------
        */

        $saidasPorVariacao =
            Movimentacao::query()
                ->select('id_variacao')
                ->selectRaw(
                    'SUM(quantidade) as total_saida'
                )
                ->whereNotNull(
                    'id_variacao'
                )
                ->where(
                    'tipo_movimentacao',
                    'saida'
                )
                ->where(
                    'origem',
                    '!=',
                    'producao_reversao'
                )
                ->whereBetween(
                    'data_movimentacao',
                    [
                        $inicio,
                        $fim,
                    ]
                )
                ->groupBy(
                    'id_variacao'
                )
                ->pluck(
                    'total_saida',
                    'id_variacao'
                );

        /*
        |--------------------------------------------------------------------------
        | Inteligência de estoque de produtos
        |--------------------------------------------------------------------------
        |
        | A recomendação operacional considera apenas produtos e variações
        | ativos. Itens arquivados continuam preservados nos relatórios
        | históricos, mas não recebem sugestões de reposição.
        |
        */

        $inteligenciaProdutos =
            ProdutoVariacao::with(
                'produto'
            )
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
                )
                ->get()
                ->map(
                    function ($variacao) use (
                        $saidasPorVariacao,
                        $periodo
                    ) {
                        $estoqueAtual =
                            (int) $variacao
                                ->quantidade;

                        $estoqueMinimo =
                            (int) $variacao
                                ->estoque_minimo;

                        $totalSaidas =
                            (float) (
                                $saidasPorVariacao[
                                    $variacao
                                        ->id_variacao
                                ]
                                ?? 0
                            );

                        $mediaDiaria =
                            $totalSaidas
                            / $periodo;

                        $coberturaDias =
                            $mediaDiaria > 0
                                ? $estoqueAtual
                                    / $mediaDiaria
                                : null;

                        $demandaEstimada30Dias =
                            $mediaDiaria * 30;

                        $sugestaoReposicao =
                            max(
                                0,
                                (int) ceil(
                                    $demandaEstimada30Dias
                                    + $estoqueMinimo
                                    - $estoqueAtual
                                )
                            );

                        [
                            $situacao,
                            $prioridade,
                        ] = $this
                            ->classificarSituacaoEstoque(
                                $estoqueAtual,
                                $estoqueMinimo,
                                $mediaDiaria,
                                $coberturaDias
                            );

                        return [
                            'produto' =>
                                $variacao
                                    ->produto
                                    ->nome_produto,

                            'tamanho' =>
                                $variacao->tamanho,

                            'cor' =>
                                $variacao->cor,

                            'estoque_atual' =>
                                $estoqueAtual,

                            'estoque_minimo' =>
                                $estoqueMinimo,

                            'total_saidas' =>
                                $totalSaidas,

                            'media_diaria' =>
                                $mediaDiaria,

                            'cobertura_dias' =>
                                $coberturaDias,

                            'demanda_30_dias' =>
                                $demandaEstimada30Dias,

                            'sugestao_reposicao' =>
                                $sugestaoReposicao,

                            'situacao' =>
                                $situacao,

                            'prioridade' =>
                                $prioridade,
                        ];
                    }
                )
                ->sortBy([
                    [
                        'prioridade',
                        'asc',
                    ],
                    [
                        'cobertura_dias',
                        'asc',
                    ],
                ])
                ->values();

        /*
        |--------------------------------------------------------------------------
        | Produtos com mais saídas
        |--------------------------------------------------------------------------
        |
        | Este é um relatório histórico. Por isso, produtos atualmente
        | inativos continuam aparecendo caso tenham movimentações no período.
        |
        */

        $topProdutos =
            DB::table(
                'movimentacoes'
            )
                ->join(
                    'produto_variacoes',
                    'movimentacoes.id_variacao',
                    '=',
                    'produto_variacoes.id_variacao'
                )
                ->join(
                    'produtos',
                    'produto_variacoes.id_produto',
                    '=',
                    'produtos.id_produto'
                )
                ->where(
                    'movimentacoes.tipo_movimentacao',
                    'saida'
                )
                ->where(
                    'movimentacoes.origem',
                    '!=',
                    'producao_reversao'
                )
                ->whereBetween(
                    'movimentacoes.data_movimentacao',
                    [
                        $inicio,
                        $fim,
                    ]
                )
                ->select(
                    'produtos.nome_produto',
                    'produto_variacoes.tamanho',
                    'produto_variacoes.cor'
                )
                ->selectRaw(
                    'SUM(movimentacoes.quantidade) as total_saida'
                )
                ->groupBy(
                    'produto_variacoes.id_variacao',
                    'produtos.nome_produto',
                    'produto_variacoes.tamanho',
                    'produto_variacoes.cor'
                )
                ->orderByDesc(
                    'total_saida'
                )
                ->limit(5)
                ->get();

        /*
        |--------------------------------------------------------------------------
        | Materiais com maior consumo
        |--------------------------------------------------------------------------
        */

        $consumoMateriais =
            DB::table(
                'movimentacoes'
            )
                ->join(
                    'materiais',
                    'movimentacoes.id_material',
                    '=',
                    'materiais.id_material'
                )
                ->where(
                    'movimentacoes.tipo_movimentacao',
                    'saida'
                )
                ->whereNotNull(
                    'movimentacoes.id_material'
                )
                ->whereBetween(
                    'movimentacoes.data_movimentacao',
                    [
                        $inicio,
                        $fim,
                    ]
                )
                ->select(
                    'materiais.id_material',
                    'materiais.nome_material',
                    'materiais.categoria',
                    'materiais.tipo_material',
                    'materiais.cor',
                    'materiais.unidade_medida'
                )
                ->selectRaw(
                    'SUM(movimentacoes.quantidade) as total_saida'
                )
                ->groupBy(
                    'materiais.id_material',
                    'materiais.nome_material',
                    'materiais.categoria',
                    'materiais.tipo_material',
                    'materiais.cor',
                    'materiais.unidade_medida'
                )
                ->get();

        $topMateriaisPorUnidade =
            $consumoMateriais
                ->groupBy(
                    'unidade_medida'
                )
                ->map(
                    function ($grupo) {
                        return $grupo
                            ->sortByDesc(
                                function ($item) {
                                    return (float)
                                        $item
                                            ->total_saida;
                                }
                            )
                            ->take(5)
                            ->values();
                    }
                );

        /*
        |--------------------------------------------------------------------------
        | Produção - resumo do período
        |--------------------------------------------------------------------------
        */

        $ordensCriadasPeriodo =
            OrdemProducao::whereBetween(
                'created_at',
                [
                    $inicio,
                    $fim,
                ]
            )->count();

        /*
         * Ordens concluídas são terminais no sistema.
         * Dessa forma, updated_at representa o momento final de conclusão.
         */
        $ordensConcluidasPeriodo =
            OrdemProducao::where(
                'status',
                'concluida'
            )
                ->whereBetween(
                    'updated_at',
                    [
                        $inicio,
                        $fim,
                    ]
                )
                ->count();

        /*
        |--------------------------------------------------------------------------
        | Itens concluídos no período
        |--------------------------------------------------------------------------
        |
        | Se um item for concluído, revertido e concluído novamente,
        | ele deve ser contado somente uma vez.
        |
        | Além disso, ele precisa continuar atualmente concluído.
        |
        */

        $idsItensComConclusaoPeriodo =
            OrdemProducaoHistorico::query()
                ->where(
                    'acao',
                    'conclusao'
                )
                ->where(
                    'etapa_destino',
                    'concluido'
                )
                ->whereBetween(
                    'created_at',
                    [
                        $inicio,
                        $fim,
                    ]
                )
                ->distinct()
                ->pluck(
                    'id_item'
                );

        $itensConcluidos =
            OrdemProducaoItem::query()
                ->whereIn(
                    'id_item',
                    $idsItensComConclusaoPeriodo
                )
                ->where(
                    'etapa_atual',
                    'concluido'
                )
                ->get();

        $itensConcluidosPeriodo =
            $itensConcluidos->count();

        $unidadesConcluidasPeriodo =
            $itensConcluidos->sum(
                'quantidade'
            );

        /*
        |--------------------------------------------------------------------------
        | Ordens atrasadas - posição atual
        |--------------------------------------------------------------------------
        |
        | Este indicador é propositalmente independente do período.
        |
        */

        $ordensAtrasadasProducao =
            OrdemProducao::whereIn(
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
        | Produção - situação atual por etapa
        |--------------------------------------------------------------------------
        |
        | Também representa uma fotografia atual e não depende do período.
        |
        */

        $dadosEtapasAtuais =
            OrdemProducaoItem::query()
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
                    'SUM(ordem_producao_itens.quantidade) as total_unidades'
                )
                ->groupBy(
                    'ordem_producao_itens.etapa_atual'
                )
                ->get()
                ->keyBy(
                    'etapa_atual'
                );

        $nomesEtapas = [
            'corte' =>
                'Corte',

            'bordado' =>
                'Bordado',

            'costura' =>
                'Costura',

            'finalizacao' =>
                'Finalização',
        ];

        $etapasAtuais =
            collect(
                $nomesEtapas
            )->map(
                function (
                    $nome,
                    $chave
                ) use (
                    $dadosEtapasAtuais
                ) {
                    $registro =
                        $dadosEtapasAtuais
                            ->get(
                                $chave
                            );

                    return [
                        'chave' =>
                            $chave,

                        'nome' =>
                            $nome,

                        'itens' =>
                            (int) (
                                $registro
                                    ?->total_itens
                                ?? 0
                            ),

                        'unidades' =>
                            (int) (
                                $registro
                                    ?->total_unidades
                                ?? 0
                            ),
                    ];
                }
            )->values();

        $maiorFilaAtual =
            $etapasAtuais
                ->sortByDesc(
                    'unidades'
                )
                ->first();

        if (
            !$maiorFilaAtual
            ||
            $maiorFilaAtual[
                'unidades'
            ] === 0
        ) {
            $maiorFilaAtual =
                null;
        }

        /*
        |--------------------------------------------------------------------------
        | Produção - tempo médio por etapa
        |--------------------------------------------------------------------------
        |
        | O tempo de uma etapa só entra na média quando ela é encerrada
        | normalmente por avanço ou conclusão.
        |
        | Cancelamentos e reversões não são tratados como conclusão normal
        | da etapa e, portanto, não entram na média.
        |
        */

        $historicos =
            OrdemProducaoHistorico::query()
                ->where(
                    'created_at',
                    '<=',
                    $fim
                )
                ->orderBy(
                    'id_item'
                )
                ->orderBy(
                    'created_at'
                )
                ->orderBy(
                    'id_historico'
                )
                ->get()
                ->groupBy(
                    'id_item'
                );

        $acumuladores = [
            'corte' => [
                'total_segundos' => 0,
                'ocorrencias' => 0,
            ],

            'bordado' => [
                'total_segundos' => 0,
                'ocorrencias' => 0,
            ],

            'costura' => [
                'total_segundos' => 0,
                'ocorrencias' => 0,
            ],

            'finalizacao' => [
                'total_segundos' => 0,
                'ocorrencias' => 0,
            ],
        ];

        foreach (
            $historicos
            as $historicosItem
        ) {
            $anterior = null;

            foreach (
                $historicosItem
                as $historico
            ) {
                if ($anterior !== null) {
                    $etapa =
                        $historico
                            ->etapa_origem;

                    $transicaoDentroPeriodo =
                        $historico
                            ->created_at
                            ->gte($inicio)
                        &&
                        $historico
                            ->created_at
                            ->lte($fim);

                    $sequenciaValida =
                        $anterior
                            ->etapa_destino
                        === $etapa;

                    $acaoEncerraEtapa =
                        in_array(
                            $historico->acao,
                            [
                                'avanco',
                                'conclusao',
                            ],
                            true
                        );

                    if (
                        isset(
                            $acumuladores[
                                $etapa
                            ]
                        )
                        &&
                        $transicaoDentroPeriodo
                        &&
                        $sequenciaValida
                        &&
                        $acaoEncerraEtapa
                    ) {
                        $segundos =
                            abs(
                                $anterior
                                    ->created_at
                                    ->diffInSeconds(
                                        $historico
                                            ->created_at
                                    )
                            );

                        $acumuladores[
                            $etapa
                        ]['total_segundos']
                            += $segundos;

                        $acumuladores[
                            $etapa
                        ]['ocorrencias']++;
                    }
                }

                $anterior =
                    $historico;
            }
        }

        $temposMediosEtapas =
            collect(
                $acumuladores
            )->map(
                function (
                    $dados,
                    $chave
                ) use (
                    $nomesEtapas
                ) {
                    $media = null;

                    if (
                        $dados[
                            'ocorrencias'
                        ] > 0
                    ) {
                        $media =
                            $dados[
                                'total_segundos'
                            ]
                            /
                            $dados[
                                'ocorrencias'
                            ];
                    }

                    return [
                        'chave' =>
                            $chave,

                        'nome' =>
                            $nomesEtapas[
                                $chave
                            ],

                        'ocorrencias' =>
                            $dados[
                                'ocorrencias'
                            ],

                        'media_segundos' =>
                            $media,

                        'media_formatada' =>
                            $this
                                ->formatarDuracao(
                                    $media
                                ),
                    ];
                }
            )->values();

        $etapaMaisLenta =
            $temposMediosEtapas
                ->filter(
                    fn ($etapa) =>
                        $etapa[
                            'media_segundos'
                        ] !== null
                )
                ->sortByDesc(
                    'media_segundos'
                )
                ->first();

        /*
        |--------------------------------------------------------------------------
        | Tempo médio total das ordens concluídas
        |--------------------------------------------------------------------------
        */

        $idsOrdensConcluidas =
            OrdemProducao::where(
                'status',
                'concluida'
            )
                ->whereBetween(
                    'updated_at',
                    [
                        $inicio,
                        $fim,
                    ]
                )
                ->pluck(
                    'id_ordem'
                );

        $duracoesOrdens = [];

        if (
            $idsOrdensConcluidas
                ->isNotEmpty()
        ) {
            $historicosOrdens =
                OrdemProducaoHistorico::query()
                    ->whereIn(
                        'id_ordem',
                        $idsOrdensConcluidas
                    )
                    ->orderBy(
                        'created_at'
                    )
                    ->get()
                    ->groupBy(
                        'id_ordem'
                    );

            foreach (
                $historicosOrdens
                as $historicosOrdem
            ) {
                $primeiroInicio =
                    $historicosOrdem
                        ->where(
                            'acao',
                            'inicio'
                        )
                        ->sortBy(
                            'created_at'
                        )
                        ->first();

                $ultimaConclusao =
                    $historicosOrdem
                        ->where(
                            'acao',
                            'conclusao'
                        )
                        ->where(
                            'etapa_destino',
                            'concluido'
                        )
                        ->sortByDesc(
                            'created_at'
                        )
                        ->first();

                if (
                    $primeiroInicio
                    &&
                    $ultimaConclusao
                ) {
                    $duracoesOrdens[] =
                        abs(
                            $primeiroInicio
                                ->created_at
                                ->diffInSeconds(
                                    $ultimaConclusao
                                        ->created_at
                                )
                        );
                }
            }
        }

        $tempoMedioProducaoSegundos =
            count(
                $duracoesOrdens
            ) > 0
                ? array_sum(
                    $duracoesOrdens
                )
                    /
                    count(
                        $duracoesOrdens
                    )
                : null;

        $tempoMedioProducao =
            $this->formatarDuracao(
                $tempoMedioProducaoSegundos
            );

        return view(
            'relatorios.index',
            compact(
                'periodo',
                'inicio',
                'fim',
                'totalMovimentacoes',
                'entradasProdutos',
                'saidasProdutos',
                'resumoMateriaisPorUnidade',
                'inteligenciaProdutos',
                'topProdutos',
                'topMateriaisPorUnidade',
                'ordensCriadasPeriodo',
                'ordensConcluidasPeriodo',
                'itensConcluidosPeriodo',
                'unidadesConcluidasPeriodo',
                'ordensAtrasadasProducao',
                'etapasAtuais',
                'maiorFilaAtual',
                'temposMediosEtapas',
                'etapaMaisLenta',
                'tempoMedioProducao'
            )
        );
    }

    private function classificarSituacaoEstoque(
        int $estoqueAtual,
        int $estoqueMinimo,
        float $mediaDiaria,
        ?float $coberturaDias
    ): array {
        /*
         * Sem histórico de demanda:
         *
         * O estoque mínimo continua sendo respeitado caso tenha sido
         * configurado. Entretanto, estoque zero com mínimo zero não é
         * automaticamente classificado como crítico.
         */
        if ($mediaDiaria <= 0) {
            if (
                $estoqueMinimo > 0
                &&
                $estoqueAtual === 0
            ) {
                return [
                    'critico',
                    1,
                ];
            }

            if (
                $estoqueMinimo > 0
                &&
                $estoqueAtual
                    <= $estoqueMinimo
            ) {
                return [
                    'baixo',
                    2,
                ];
            }

            return [
                'sem_historico',
                5,
            ];
        }

        /*
         * Existe demanda histórica.
         */
        if ($estoqueAtual === 0) {
            return [
                'critico',
                1,
            ];
        }

        if (
            $estoqueAtual
            <= $estoqueMinimo
        ) {
            return [
                'baixo',
                2,
            ];
        }

        if (
            $coberturaDias !== null
            &&
            $coberturaDias <= 7
        ) {
            return [
                'atencao',
                3,
            ];
        }

        return [
            'normal',
            4,
        ];
    }

    private function formatarDuracao(
        ?float $segundos
    ): string {
        if ($segundos === null) {
            return '-';
        }

        $segundos = (int) round(
            $segundos
        );

        if ($segundos < 60) {
            return
                $segundos
                . ' s';
        }

        if ($segundos < 3600) {
            $minutos = intdiv(
                $segundos,
                60
            );

            $restoSegundos =
                $segundos % 60;

            return
                $minutos
                . ' min '
                . $restoSegundos
                . ' s';
        }

        if ($segundos < 86400) {
            $horas = intdiv(
                $segundos,
                3600
            );

            $minutos = intdiv(
                $segundos % 3600,
                60
            );

            return
                $horas
                . ' h '
                . $minutos
                . ' min';
        }

        $dias = intdiv(
            $segundos,
            86400
        );

        $horas = intdiv(
            $segundos % 86400,
            3600
        );

        return
            $dias
            . ' d '
            . $horas
            . ' h';
    }
}
