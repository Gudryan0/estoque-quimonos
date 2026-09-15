@extends('layouts.app')

@section('title', 'Relatórios e Inteligência')

@section('content')

@php
    $nomesUnidades = [
        'm' => 'Metro',
        'un' => 'Unidade',
        'rolo' => 'Rolo',
        'kg' => 'Quilograma',
    ];

    $formatarQuantidadeMaterial =
        function ($valor, $unidade) {
            $numero = (float) $valor;

            $unidadeDiscreta =
                in_array(
                    $unidade,
                    [
                        'un',
                        'rolo',
                    ],
                    true
                );

            $ehInteiro =
                abs(
                    $numero
                    - round($numero)
                ) < 0.0005;

            $decimais =
                $unidadeDiscreta
                && $ehInteiro
                    ? 0
                    : 3;

            $quantidade =
                number_format(
                    $numero,
                    $decimais,
                    ',',
                    '.'
                );

            $sufixo = match ($unidade) {
                'un' =>
                    'un.',

                'rolo' =>
                    abs(
                        $numero - 1
                    ) < 0.0005
                        ? 'rolo'
                        : 'rolos',

                default =>
                    $unidade,
            };

            return
                $quantidade
                . ' '
                . $sufixo;
        };
@endphp

<div class="mb-4">
    <h1 class="mb-1">
        Relatórios e Inteligência
    </h1>

    <p class="text-muted mb-0">
        Indicadores de estoque, materiais,
        demanda e desempenho da produção.
    </p>
</div>

{{-- Período --}}
<div class="card mb-4">
    <div class="card-body p-4">

        <form
            method="GET"
            action="{{ route('relatorios.index') }}"
            class="row align-items-end g-3"
        >
            <div class="col-md-5 col-lg-4">
                <label
                    for="periodo"
                    class="form-label"
                >
                    Período analisado
                </label>

                <select
                    name="periodo"
                    id="periodo"
                    class="form-select"
                >
                    <option
                        value="7"
                        @selected($periodo === 7)
                    >
                        Últimos 7 dias
                    </option>

                    <option
                        value="30"
                        @selected($periodo === 30)
                    >
                        Últimos 30 dias
                    </option>

                    <option
                        value="60"
                        @selected($periodo === 60)
                    >
                        Últimos 60 dias
                    </option>

                    <option
                        value="90"
                        @selected($periodo === 90)
                    >
                        Últimos 90 dias
                    </option>
                </select>
            </div>

            <div class="col-md-auto">
                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Atualizar relatório
                </button>
            </div>
        </form>

        <div class="small text-muted mt-2">
            Período:
            {{ $inicio->format('d/m/Y') }}
            até
            {{ $fim->format('d/m/Y') }}
        </div>

    </div>
</div>

{{-- Movimentações --}}
<div class="mb-3">
    <h2 class="h4 mb-1">
        Movimentações no período
    </h2>
</div>

<div class="row g-3 mb-4">

    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="text-muted mb-2">
                    Movimentações
                </div>

                <h2 class="mb-1">
                    {{ $totalMovimentacoes }}
                </h2>

                <small class="text-muted">
                    registros
                </small>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="text-muted mb-2">
                    Entradas de produtos
                </div>

                <h2 class="text-success mb-1">
                    {{
                        number_format(
                            $entradasProdutos,
                            0,
                            ',',
                            '.'
                        )
                    }}
                </h2>

                <small class="text-muted">
                    unidades acabadas
                </small>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="text-muted mb-2">
                    Saídas consideradas como demanda
                </div>

                <h2 class="text-danger mb-1">
                    {{
                        number_format(
                            $saidasProdutos,
                            0,
                            ',',
                            '.'
                        )
                    }}
                </h2>

                <small class="text-muted">
                    unidades de produto
                </small>
            </div>
        </div>
    </div>

</div>

{{-- Materiais --}}
<h2 class="h5 mb-3">
    Movimentação de materiais
</h2>

@if($resumoMateriaisPorUnidade->isEmpty())

    <div class="card mb-5">
        <div class="card-body text-muted">
            Não há movimentações de materiais
            neste período.
        </div>
    </div>

@else

    <div class="card mb-5">
        <div class="table-responsive">
            <table
                class="
                    table
                    table-hover
                    align-middle
                    mb-0
                "
            >
                <thead>
                    <tr>
                        <th>
                            Unidade de medida
                        </th>

                        <th class="text-end">
                            Entradas
                        </th>

                        <th class="text-end">
                            Saídas / consumo
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach(
                        $resumoMateriaisPorUnidade
                        as $resumo
                    )
                        <tr>
                            <td>
                                <strong>
                                    {{
                                        $nomesUnidades[
                                            $resumo
                                                ->unidade_medida
                                        ]
                                        ??
                                        ucfirst(
                                            $resumo
                                                ->unidade_medida
                                        )
                                    }}
                                </strong>

                                <span class="text-muted">
                                    ({{
                                        $resumo
                                            ->unidade_medida
                                    }})
                                </span>
                            </td>

                            <td
                                class="
                                    text-end
                                    text-success
                                    text-nowrap
                                "
                            >
                                {{
                                    $formatarQuantidadeMaterial(
                                        $resumo
                                            ->total_entradas,
                                        $resumo
                                            ->unidade_medida
                                    )
                                }}
                            </td>

                            <td
                                class="
                                    text-end
                                    text-danger
                                    text-nowrap
                                "
                            >
                                {{
                                    $formatarQuantidadeMaterial(
                                        $resumo
                                            ->total_saidas,
                                        $resumo
                                            ->unidade_medida
                                    )
                                }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endif

{{-- Produtos mais movimentados --}}
<div class="row g-4 mb-5">

    <div class="col-lg-6">
        <h2 class="h5 mb-3">
            Produtos com mais saídas
        </h2>

        @if($topProdutos->isEmpty())

            <div class="card h-100">
                <div class="card-body text-muted">
                    Não há saídas de produtos
                    neste período.
                </div>
            </div>

        @else

            <div class="card">
                <div class="table-responsive">
                    <table
                        class="
                            table
                            table-hover
                            align-middle
                            mb-0
                        "
                    >
                        <thead>
                            <tr>
                                <th>
                                    Produto
                                </th>

                                <th>
                                    Variação
                                </th>

                                <th class="text-end">
                                    Saídas
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach(
                                $topProdutos
                                as $item
                            )
                                <tr>
                                    <td>
                                        <strong>
                                            {{
                                                $item
                                                    ->nome_produto
                                            }}
                                        </strong>
                                    </td>

                                    <td>
                                        {{ $item->tamanho }}
                                        /
                                        {{ $item->cor }}
                                    </td>

                                    <td
                                        class="
                                            text-end
                                            text-nowrap
                                        "
                                    >
                                        {{
                                            number_format(
                                                $item
                                                    ->total_saida,
                                                0,
                                                ',',
                                                '.'
                                            )
                                        }}
                                        un.
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        @endif
    </div>

    <div class="col-lg-6">
        <h2 class="h5 mb-3">
            Interpretação das saídas
        </h2>

        <div
            class="
                alert
                bg-primary-subtle
                text-primary-emphasis
                border
                border-primary-subtle
                mb-0
            "
        >
            As saídas causadas por
            <strong>reversão de produção</strong>
            são correções técnicas e não entram
            nos cálculos de demanda.

            No modelo atual, as demais saídas
            de produtos são consideradas demanda
            para média diária, previsão e reposição.
        </div>
    </div>

</div>

{{-- Consumo de materiais --}}
<h2 class="h5 mb-3">
    Materiais com maior consumo
</h2>

@if($topMateriaisPorUnidade->isEmpty())

    <div class="card mb-5">
        <div class="card-body text-muted">
            Não há consumo de materiais
            neste período.
        </div>
    </div>

@else

    <div class="row g-4 mb-5">

        @foreach(
            $topMateriaisPorUnidade
            as $unidade => $materiais
        )
            <div class="col-lg-6">
                <div class="card h-100">

                    <div class="card-header">
                        <strong>
                            {{
                                $nomesUnidades[
                                    $unidade
                                ]
                                ??
                                ucfirst(
                                    $unidade
                                )
                            }}

                            ({{ $unidade }})
                        </strong>
                    </div>

                    <div class="table-responsive">
                        <table
                            class="
                                table
                                table-hover
                                align-middle
                                mb-0
                            "
                        >
                            <thead>
                                <tr>
                                    <th>
                                        Material
                                    </th>

                                    <th>
                                        Categoria
                                    </th>

                                    <th class="text-end">
                                        Consumo
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach(
                                    $materiais
                                    as $material
                                )
                                    <tr>
                                        <td>
                                            <strong>
                                                {{
                                                    $material
                                                        ->nome_material
                                                }}
                                            </strong>

                                            @if($material->cor)
                                                <br>

                                                <small
                                                    class="text-muted"
                                                >
                                                    {{
                                                        $material
                                                            ->cor
                                                    }}
                                                </small>
                                            @endif
                                        </td>

                                        <td>
                                            {{
                                                ucfirst(
                                                    $material
                                                        ->categoria
                                                )
                                            }}
                                        </td>

                                        <td
                                            class="
                                                text-end
                                                text-nowrap
                                            "
                                        >
                                            {{
                                                $formatarQuantidadeMaterial(
                                                    $material
                                                        ->total_saida,
                                                    $unidade
                                                )
                                            }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        @endforeach

    </div>

@endif

<hr class="my-5">

{{-- Produção --}}
<div class="mb-3">
    <h2 class="mb-1">
        Desempenho da Produção
    </h2>

    <p class="text-muted mb-0">
        Indicadores calculados a partir do
        histórico das ordens de produção.
    </p>
</div>

<div class="row g-3 mb-4">

    <div class="col-md-6 col-lg">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="text-muted mb-2">
                    Ordens criadas
                </div>

                <h2 class="mb-1">
                    {{ $ordensCriadasPeriodo }}
                </h2>

                <small class="text-muted">
                    no período
                </small>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg">
        <div
            class="
                card
                h-100
                border-success-subtle
            "
        >
            <div class="card-body p-4">
                <div class="text-muted mb-2">
                    Ordens concluídas
                </div>

                <h2 class="text-success mb-1">
                    {{ $ordensConcluidasPeriodo }}
                </h2>

                <small class="text-muted">
                    no período
                </small>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="text-muted mb-2">
                    Itens concluídos
                </div>

                <h2 class="mb-1">
                    {{ $itensConcluidosPeriodo }}
                </h2>

                <small class="text-muted">
                    {{
                        number_format(
                            $unidadesConcluidasPeriodo,
                            0,
                            ',',
                            '.'
                        )
                    }}
                    unidades
                </small>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg">
        <div
            class="
                card
                h-100
                border-danger-subtle
            "
        >
            <div class="card-body p-4">
                <div class="text-muted mb-2">
                    Ordens atrasadas
                </div>

                <h2 class="text-danger mb-1">
                    {{ $ordensAtrasadasProducao }}
                </h2>

                <small class="text-muted">
                    posição atual
                </small>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="text-muted mb-2">
                    Tempo médio de produção
                </div>

                <h4 class="mb-1">
                    {{ $tempoMedioProducao }}
                </h4>

                <small class="text-muted">
                    ordens concluídas no período
                </small>
            </div>
        </div>
    </div>

</div>

<div
    class="
        alert
        bg-primary-subtle
        text-primary-emphasis
        border
        border-primary-subtle
        mb-4
    "
>
    <strong>
        Sobre os indicadores de tempo:
    </strong>

    eles são calculados a partir do histórico real
    das mudanças de etapa.

    Reversões e cancelamentos não são tratados como
    conclusões normais de etapa e não entram nas
    médias por etapa.

    Dados de teste avançados rapidamente podem gerar
    tempos muito curtos.
</div>

<div class="row g-4 mb-5">

    {{-- Situação atual --}}
    <div class="col-lg-6">
        <h3 class="h5 mb-1">
            Situação atual por etapa
        </h3>

        <p class="text-muted small">
            Fotografia da produção atual,
            independentemente do período selecionado.
        </p>

        <div class="card">
            <div class="table-responsive">
                <table
                    class="
                        table
                        table-hover
                        align-middle
                        mb-0
                    "
                >
                    <thead>
                        <tr>
                            <th>
                                Etapa
                            </th>

                            <th class="text-end">
                                Itens
                            </th>

                            <th class="text-end">
                                Unidades
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach(
                            $etapasAtuais
                            as $etapa
                        )
                            <tr>
                                <td>
                                    {{ $etapa['nome'] }}
                                </td>

                                <td class="text-end">
                                    {{ $etapa['itens'] }}
                                </td>

                                <td class="text-end">
                                    {{ $etapa['unidades'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Tempo --}}
    <div class="col-lg-6">
        <h3 class="h5 mb-1">
            Tempo médio por etapa
        </h3>

        <p class="text-muted small">
            Considera etapas encerradas normalmente
            dentro do período selecionado.
        </p>

        <div class="card">
            <div class="table-responsive">
                <table
                    class="
                        table
                        table-hover
                        align-middle
                        mb-0
                    "
                >
                    <thead>
                        <tr>
                            <th>
                                Etapa
                            </th>

                            <th class="text-end">
                                Amostras
                            </th>

                            <th class="text-end">
                                Tempo médio
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach(
                            $temposMediosEtapas
                            as $etapa
                        )
                            <tr>
                                <td>
                                    {{ $etapa['nome'] }}
                                </td>

                                <td class="text-end">
                                    {{
                                        $etapa[
                                            'ocorrencias'
                                        ]
                                    }}
                                </td>

                                <td
                                    class="
                                        text-end
                                        text-nowrap
                                    "
                                >
                                    {{
                                        $etapa[
                                            'media_formatada'
                                        ]
                                    }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Gargalos --}}
<h3 class="h5 mb-3">
    Indicadores de possível gargalo
</h3>

<div class="row g-3 mb-4">

    <div class="col-md-6">
        <div
            class="
                card
                h-100
                border-warning-subtle
            "
        >
            <div class="card-body p-4">
                <div class="text-muted mb-2">
                    Maior concentração atual
                </div>

                @if($maiorFilaAtual)

                    <h3>
                        {{
                            $maiorFilaAtual[
                                'nome'
                            ]
                        }}
                    </h3>

                    <p class="mb-0">
                        {{
                            $maiorFilaAtual[
                                'unidades'
                            ]
                        }}

                        {{
                            $maiorFilaAtual[
                                'unidades'
                            ] === 1
                                ? 'unidade'
                                : 'unidades'
                        }}

                        distribuída(s) em

                        {{
                            $maiorFilaAtual[
                                'itens'
                            ]
                        }}

                        {{
                            $maiorFilaAtual[
                                'itens'
                            ] === 1
                                ? 'item'
                                : 'itens'
                        }}

                        de produção.
                    </p>

                @else

                    <p class="mb-0 text-muted">
                        Não há produção ativa
                        suficiente para análise.
                    </p>

                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div
            class="
                card
                h-100
                border-warning-subtle
            "
        >
            <div class="card-body p-4">
                <div class="text-muted mb-2">
                    Maior tempo médio
                </div>

                @if($etapaMaisLenta)

                    <h3>
                        {{
                            $etapaMaisLenta[
                                'nome'
                            ]
                        }}
                    </h3>

                    <p class="mb-0">
                        Média de

                        <strong>
                            {{
                                $etapaMaisLenta[
                                    'media_formatada'
                                ]
                            }}
                        </strong>

                        com

                        {{
                            $etapaMaisLenta[
                                'ocorrencias'
                            ]
                        }}

                        {{
                            $etapaMaisLenta[
                                'ocorrencias'
                            ] === 1
                                ? 'amostra'
                                : 'amostras'
                        }}.
                    </p>

                @else

                    <p class="mb-0 text-muted">
                        Ainda não há histórico
                        suficiente para análise.
                    </p>

                @endif
            </div>
        </div>
    </div>

</div>

<div
    class="
        alert
        bg-warning-subtle
        text-warning-emphasis
        border
        border-warning-subtle
    "
>
    <strong>Importante:</strong>

    maior fila ou maior tempo médio são
    <strong>indicadores de possível gargalo</strong>,
    não uma confirmação definitiva.

    Quanto maior o histórico real de produção,
    mais representativa se torna a análise.
</div>

<hr class="my-5">

{{-- Inteligência --}}
<div class="mb-3">
    <h2 class="mb-1">
        Inteligência de Estoque
    </h2>

    <p class="text-muted mb-0">
        Estimativas de demanda e reposição
        para produtos acabados ativos.
    </p>
</div>

<div
    class="
        alert
        bg-primary-subtle
        text-primary-emphasis
        border
        border-primary-subtle
        mb-4
    "
>
    <strong>Como interpretar:</strong>

    a média diária considera as saídas dos
    últimos {{ $periodo }} dias selecionados.

    A estimativa de 30 dias projeta essa média
    histórica, e movimentações técnicas de
    reversão são ignoradas.

    Produtos ou variações inativos não recebem
    sugestões de reposição.
</div>

@if($inteligenciaProdutos->isEmpty())

    <div class="card">
        <div class="card-body text-muted">
            Não existem produtos ativos disponíveis
            para análise de estoque.
        </div>
    </div>

@else

    <div class="card">
        <div class="table-responsive">
            <table
                class="
                    table
                    table-hover
                    align-middle
                    mb-0
                "
            >
                <thead>
                    <tr>
                        <th>
                            Produto
                        </th>

                        <th>
                            Variação
                        </th>

                        <th>
                            Estoque
                        </th>

                        <th>
                            Mínimo
                        </th>

                        <th>
                            Saídas
                        </th>

                        <th>
                            Média/dia
                        </th>

                        <th>
                            Cobertura
                        </th>

                        <th>
                            Estimativa 30d
                        </th>

                        <th>
                            Reposição sugerida
                        </th>

                        <th>
                            Situação
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach(
                        $inteligenciaProdutos
                        as $item
                    )
                        <tr>
                            <td>
                                <strong>
                                    {{
                                        $item[
                                            'produto'
                                        ]
                                    }}
                                </strong>
                            </td>

                            <td class="text-nowrap">
                                {{
                                    $item[
                                        'tamanho'
                                    ]
                                }}

                                /

                                {{
                                    $item[
                                        'cor'
                                    ]
                                }}
                            </td>

                            <td>
                                {{
                                    number_format(
                                        $item[
                                            'estoque_atual'
                                        ],
                                        0,
                                        ',',
                                        '.'
                                    )
                                }}
                            </td>

                            <td>
                                {{
                                    number_format(
                                        $item[
                                            'estoque_minimo'
                                        ],
                                        0,
                                        ',',
                                        '.'
                                    )
                                }}
                            </td>

                            <td>
                                {{
                                    number_format(
                                        $item[
                                            'total_saidas'
                                        ],
                                        0,
                                        ',',
                                        '.'
                                    )
                                }}
                            </td>

                            <td>
                                {{
                                    number_format(
                                        $item[
                                            'media_diaria'
                                        ],
                                        2,
                                        ',',
                                        '.'
                                    )
                                }}
                            </td>

                            <td class="text-nowrap">
                                @if(
                                    $item[
                                        'cobertura_dias'
                                    ] === null
                                )
                                    <span class="text-muted">
                                        —
                                    </span>
                                @else
                                    {{
                                        number_format(
                                            $item[
                                                'cobertura_dias'
                                            ],
                                            1,
                                            ',',
                                            '.'
                                        )
                                    }}
                                    dias
                                @endif
                            </td>

                            <td>
                                {{
                                    number_format(
                                        $item[
                                            'demanda_30_dias'
                                        ],
                                        1,
                                        ',',
                                        '.'
                                    )
                                }}
                            </td>

                            <td class="text-nowrap">
                                @if(
                                    $item[
                                        'sugestao_reposicao'
                                    ] > 0
                                )
                                    <strong>
                                        {{
                                            $item[
                                                'sugestao_reposicao'
                                            ]
                                        }}
                                        un.
                                    </strong>
                                @else
                                    <span class="text-muted">
                                        —
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if(
                                    $item['situacao']
                                    === 'critico'
                                )
                                    <span
                                        class="
                                            badge
                                            bg-danger-subtle
                                            text-danger-emphasis
                                            border
                                            border-danger-subtle
                                        "
                                    >
                                        Crítico
                                    </span>

                                @elseif(
                                    $item['situacao']
                                    === 'baixo'
                                )
                                    <span
                                        class="
                                            badge
                                            bg-warning-subtle
                                            text-warning-emphasis
                                            border
                                            border-warning-subtle
                                        "
                                    >
                                        Estoque baixo
                                    </span>

                                @elseif(
                                    $item['situacao']
                                    === 'atencao'
                                )
                                    <span
                                        class="
                                            badge
                                            bg-warning-subtle
                                            text-warning-emphasis
                                            border
                                            border-warning-subtle
                                        "
                                    >
                                        Atenção
                                    </span>

                                @elseif(
                                    $item['situacao']
                                    === 'sem_historico'
                                )
                                    <span
                                        class="
                                            badge
                                            bg-secondary-subtle
                                            text-secondary-emphasis
                                            border
                                            border-secondary-subtle
                                        "
                                    >
                                        Sem histórico
                                    </span>

                                @else
                                    <span
                                        class="
                                            badge
                                            bg-success-subtle
                                            text-success-emphasis
                                            border
                                            border-success-subtle
                                        "
                                    >
                                        Normal
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endif

@endsection
