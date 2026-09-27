@extends('layouts.app')

@section('title', 'Movimentações')

@section('content')

@php
    $timezone =
        config(
            'app.business_timezone',
            'America/Sao_Paulo'
        );

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

    $origens = [
        'manual' => [
            'texto' => 'Manual',
            'classe' =>
                'bg-secondary-subtle '
                . 'text-secondary-emphasis '
                . 'border '
                . 'border-secondary-subtle',
        ],

        'producao_consumo' => [
            'texto' => 'Consumo produção',
            'classe' =>
                'bg-primary-subtle '
                . 'text-primary-emphasis '
                . 'border '
                . 'border-primary-subtle',
        ],

        'producao_conclusao' => [
            'texto' => 'Conclusão produção',
            'classe' =>
                'bg-success-subtle '
                . 'text-success-emphasis '
                . 'border '
                . 'border-success-subtle',
        ],

        'producao_reversao' => [
            'texto' => 'Reversão produção',
            'classe' =>
                'bg-warning-subtle '
                . 'text-warning-emphasis '
                . 'border '
                . 'border-warning-subtle',
        ],
    ];
@endphp

{{-- Cabeçalho --}}
<div
    class="
        d-flex
        flex-column
        flex-md-row
        justify-content-between
        align-items-md-start
        gap-3
        mb-4
    "
>
    <div>
        <h1 class="mb-1">
            Movimentações
        </h1>

        <p class="text-muted mb-0">
            Histórico de entradas e saídas do estoque.
            Consumos de produção são agrupados por lote.
        </p>
    </div>

    <a
        href="{{ route('movimentacoes.create') }}"
        class="btn btn-primary"
    >
        Nova movimentação
    </a>
</div>

{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body">
        <form
            action="{{ route('movimentacoes.index') }}"
            method="GET"
        >
            <div class="row g-3">

                <div class="col-lg-5">
                    <label
                        for="q"
                        class="form-label"
                    >
                        Buscar
                    </label>

                    <input
                        type="search"
                        name="q"
                        id="q"
                        class="form-control"
                        value="{{ $busca }}"
                        placeholder="Item, OP, cliente, responsável ou observação"
                    >
                </div>

                <div class="col-md-4 col-lg-2">
                    <label
                        for="tipo_item"
                        class="form-label"
                    >
                        Tipo de item
                    </label>

                    <select
                        name="tipo_item"
                        id="tipo_item"
                        class="form-select"
                    >
                        <option value="">
                            Todos
                        </option>

                        <option
                            value="produto"
                            @selected(
                                $tipoItem
                                === 'produto'
                            )
                        >
                            Produto acabado
                        </option>

                        <option
                            value="material"
                            @selected(
                                $tipoItem
                                === 'material'
                            )
                        >
                            Material
                        </option>
                    </select>
                </div>

                <div class="col-md-4 col-lg-2">
                    <label
                        for="tipo_movimentacao"
                        class="form-label"
                    >
                        Movimentação
                    </label>

                    <select
                        name="tipo_movimentacao"
                        id="tipo_movimentacao"
                        class="form-select"
                    >
                        <option value="">
                            Todas
                        </option>

                        <option
                            value="entrada"
                            @selected(
                                $tipoMovimentacao
                                === 'entrada'
                            )
                        >
                            Entrada
                        </option>

                        <option
                            value="saida"
                            @selected(
                                $tipoMovimentacao
                                === 'saida'
                            )
                        >
                            Saída
                        </option>
                    </select>
                </div>

                <div class="col-md-4 col-lg-3">
                    <label
                        for="origem"
                        class="form-label"
                    >
                        Origem
                    </label>

                    <select
                        name="origem"
                        id="origem"
                        class="form-select"
                    >
                        <option value="">
                            Todas
                        </option>

                        <option
                            value="manual"
                            @selected(
                                $origem === 'manual'
                            )
                        >
                            Manual
                        </option>

                        <option
                            value="producao_consumo"
                            @selected(
                                $origem
                                === 'producao_consumo'
                            )
                        >
                            Consumo de produção
                        </option>

                        <option
                            value="producao_conclusao"
                            @selected(
                                $origem
                                === 'producao_conclusao'
                            )
                        >
                            Conclusão de produção
                        </option>

                        <option
                            value="producao_reversao"
                            @selected(
                                $origem
                                === 'producao_reversao'
                            )
                        >
                            Reversão de produção
                        </option>
                    </select>
                </div>

                <div class="col-md-4 col-lg-3">
                    <label
                        for="data_inicio"
                        class="form-label"
                    >
                        De
                    </label>

                    <input
                        type="date"
                        name="data_inicio"
                        id="data_inicio"
                        class="form-control"
                        value="{{ $dataInicio }}"
                    >
                </div>

                <div class="col-md-4 col-lg-3">
                    <label
                        for="data_fim"
                        class="form-label"
                    >
                        Até
                    </label>

                    <input
                        type="date"
                        name="data_fim"
                        id="data_fim"
                        class="form-control"
                        value="{{ $dataFim }}"
                    >
                </div>

                <div
                    class="
                        col-md-4
                        col-lg-auto
                        d-flex
                        align-items-end
                    "
                >
                    <div class="d-flex gap-2">
                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Filtrar
                        </button>

                        @if(
                            $busca !== ''
                            ||
                            $tipoItem !== ''
                            ||
                            $tipoMovimentacao !== ''
                            ||
                            $origem !== ''
                            ||
                            $dataInicio !== ''
                            ||
                            $dataFim !== ''
                        )
                            <a
                                href="{{
                                    route(
                                        'movimentacoes.index'
                                    )
                                }}"
                                class="
                                    btn
                                    btn-outline-secondary
                                "
                            >
                                Limpar
                            </a>
                        @endif
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- Contagem --}}
<div
    class="
        d-flex
        justify-content-between
        align-items-center
        gap-3
        mb-2
    "
>
    <div class="small text-muted">
        @if($blocosMovimentacao->total() > 0)
            Exibindo
            {{ $blocosMovimentacao->firstItem() }}
            a
            {{ $blocosMovimentacao->lastItem() }}
            de
            {{ $blocosMovimentacao->total() }}

            {{
                $blocosMovimentacao->total() === 1
                    ? 'registro'
                    : 'registros'
            }}.
        @else
            Nenhuma movimentação encontrada.
        @endif
    </div>
</div>

@if($blocosMovimentacao->isEmpty())

    <div class="card">
        <div
            class="
                card-body
                text-center
                py-5
            "
        >
            <h2 class="h5 mb-2">
                Nenhuma movimentação encontrada
            </h2>

            <p class="text-muted mb-3">
                @if(
                    $busca !== ''
                    ||
                    $tipoItem !== ''
                    ||
                    $tipoMovimentacao !== ''
                    ||
                    $origem !== ''
                    ||
                    $dataInicio !== ''
                    ||
                    $dataFim !== ''
                )
                    Não existem registros
                    correspondentes aos filtros atuais.
                @else
                    Ainda não existem movimentações
                    registradas no estoque.
                @endif
            </p>

            @if(
                $busca !== ''
                ||
                $tipoItem !== ''
                ||
                $tipoMovimentacao !== ''
                ||
                $origem !== ''
                ||
                $dataInicio !== ''
                ||
                $dataFim !== ''
            )
                <a
                    href="{{
                        route(
                            'movimentacoes.index'
                        )
                    }}"
                    class="btn btn-outline-primary"
                >
                    Limpar filtros
                </a>
            @else
                <a
                    href="{{
                        route(
                            'movimentacoes.create'
                        )
                    }}"
                    class="btn btn-primary"
                >
                    Nova movimentação
                </a>
            @endif
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
                            Data
                        </th>

                        <th>
                            Item
                        </th>

                        <th>
                            Tipo
                        </th>

                        <th>
                            Quantidade
                        </th>

                        <th>
                            Origem
                        </th>

                        <th>
                            Responsável
                        </th>

                        <th>
                            Observação
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach(
                        $blocosMovimentacao
                        as $bloco
                    )
                        @php
                            $movimentacao =
                                $bloco['principal'];

                            $origemAtual =
                                $origens[
                                    $movimentacao->origem
                                ]
                                ?? null;
                        @endphp

                        @if(
                            $bloco[
                                'agrupado_por_lote'
                            ]
                        )
                            @php
                                $lote =
                                    $movimentacao
                                        ->itemProducao;

                                $ordem =
                                    $movimentacao
                                        ->ordem;

                                $variacoesLote =
                                    $lote
                                        ? $lote
                                            ->variacoes
                                            ->sortBy(
                                                function (
                                                    $linha
                                                ) {
                                                    return
                                                        $linha
                                                            ->variacao
                                                            ?->tamanho
                                                        ?? '';
                                                }
                                            )
                                        : collect();

                                $materiaisConsumidos =
                                    $bloco[
                                        'movimentacoes'
                                    ]
                                        ->groupBy(
                                            function (
                                                $registro
                                            ) {
                                                return
                                                    (string)
                                                    (
                                                        $registro
                                                            ->id_material
                                                        ?? 'sem_material'
                                                    );
                                            }
                                        )
                                        ->map(
                                            function (
                                                $registrosMaterial
                                            ) {
                                                $primeiro =
                                                    $registrosMaterial
                                                        ->first();

                                                return [
                                                    'material' =>
                                                        $primeiro
                                                            ->material,

                                                    'quantidade' =>
                                                        $registrosMaterial
                                                            ->sum(
                                                                function (
                                                                    $registro
                                                                ) {
                                                                    return
                                                                        (float)
                                                                        $registro
                                                                            ->quantidade;
                                                                }
                                                            ),
                                                ];
                                            }
                                        );
                            @endphp

                            <tr>
                                <td
                                    colspan="7"
                                    class="p-0"
                                >
                                    <div
                                        class="
                                            p-3
                                            p-lg-4
                                            bg-body-tertiary
                                        "
                                    >
                                        <div
                                            class="
                                                d-flex
                                                flex-column
                                                flex-lg-row
                                                justify-content-between
                                                gap-3
                                                mb-3
                                            "
                                        >
                                            <div>
                                                <div
                                                    class="
                                                        d-flex
                                                        flex-wrap
                                                        align-items-center
                                                        gap-2
                                                        mb-2
                                                    "
                                                >
                                                    <span
                                                        class="
                                                            badge
                                                            bg-primary-subtle
                                                            text-primary-emphasis
                                                            border
                                                            border-primary-subtle
                                                        "
                                                    >
                                                        Consumo de produção
                                                    </span>

                                                    <span
                                                        class="
                                                            badge
                                                            bg-danger-subtle
                                                            text-danger-emphasis
                                                            border
                                                            border-danger-subtle
                                                        "
                                                    >
                                                        Saída
                                                    </span>
                                                </div>

                                                <h3 class="h6 mb-1">
                                                    @if($ordem)
                                                        <a
                                                            href="{{
                                                                route(
                                                                    'ordens.show',
                                                                    $ordem
                                                                )
                                                            }}"
                                                            class="
                                                                text-decoration-none
                                                            "
                                                        >
                                                            {{ $ordem->codigo }}
                                                        </a>
                                                    @else
                                                        Ordem não disponível
                                                    @endif

                                                    @if($lote)
                                                        ·
                                                        {{
                                                            $lote
                                                                ->produto
                                                                ?->nome_produto
                                                            ??
                                                            'Produto indisponível'
                                                        }}

                                                        ·
                                                        {{ $lote->cor }}
                                                    @endif
                                                </h3>

                                                @if($lote)
                                                    <div
                                                        class="
                                                            small
                                                            text-muted
                                                        "
                                                    >
                                                        Cliente:
                                                        {{
                                                            $lote
                                                                ->cliente_nome
                                                            ??
                                                            'Sem cliente definido'
                                                        }}

                                                        @if(
                                                            $lote
                                                                ->responsavel_costura
                                                        )
                                                            · Costura:
                                                            {{
                                                                $lote
                                                                    ->responsavel_costura
                                                            }}
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>

                                            <div
                                                class="
                                                    text-lg-end
                                                    small
                                                "
                                            >
                                                <strong
                                                    class="d-block"
                                                >
                                                    {{
                                                        $movimentacao
                                                            ->data_movimentacao
                                                            ->copy()
                                                            ->timezone($timezone)
                                                            ->format(
                                                                'd/m/Y H:i'
                                                            )
                                                    }}
                                                </strong>

                                                <span
                                                    class="
                                                        text-muted
                                                    "
                                                >
                                                    Responsável:
                                                    {{
                                                        $movimentacao
                                                            ->usuario
                                                            ?->name
                                                        ??
                                                        'Não disponível'
                                                    }}
                                                </span>
                                            </div>
                                        </div>

                                        @if(
                                            $lote
                                            &&
                                            $variacoesLote
                                                ->isNotEmpty()
                                        )
                                            <div
                                                class="
                                                    border
                                                    rounded
                                                    bg-body
                                                    p-3
                                                    mb-3
                                                "
                                            >
                                                <div
                                                    class="
                                                        small
                                                        text-muted
                                                        mb-2
                                                    "
                                                >
                                                    Quantidades do lote
                                                </div>

                                                <div
                                                    class="
                                                        d-flex
                                                        flex-wrap
                                                        gap-2
                                                    "
                                                >
                                                    @foreach(
                                                        $variacoesLote
                                                        as $linha
                                                    )
                                                        <span
                                                            class="
                                                                badge
                                                                bg-secondary-subtle
                                                                text-secondary-emphasis
                                                                border
                                                                border-secondary-subtle
                                                                p-2
                                                            "
                                                        >
                                                            {{
                                                                $linha
                                                                    ->variacao
                                                                    ?->tamanho
                                                                ??
                                                                'Tamanho indisponível'
                                                            }}:
                                                            {{
                                                                number_format(
                                                                    $linha
                                                                        ->quantidade,
                                                                    0,
                                                                    ',',
                                                                    '.'
                                                                )
                                                            }}
                                                        </span>
                                                    @endforeach

                                                    <span
                                                        class="
                                                            badge
                                                            bg-dark-subtle
                                                            text-dark-emphasis
                                                            border
                                                            p-2
                                                        "
                                                    >
                                                        Total:
                                                        {{
                                                            number_format(
                                                                $lote
                                                                    ->quantidade,
                                                                0,
                                                                ',',
                                                                '.'
                                                            )
                                                        }}
                                                        un.
                                                    </span>
                                                </div>
                                            </div>
                                        @endif

                                        <div
                                            class="
                                                border
                                                rounded
                                                bg-body
                                            "
                                        >
                                            <div
                                                class="
                                                    px-3
                                                    pt-3
                                                    pb-2
                                                "
                                            >
                                                <strong>
                                                    Materiais consumidos
                                                </strong>
                                            </div>

                                            <div
                                                class="
                                                    table-responsive
                                                "
                                            >
                                                <table
                                                    class="
                                                        table
                                                        table-sm
                                                        align-middle
                                                        mb-0
                                                    "
                                                >
                                                    <thead>
                                                        <tr>
                                                            <th
                                                                class="
                                                                    ps-3
                                                                "
                                                            >
                                                                Material
                                                            </th>

                                                            <th>
                                                                Cor
                                                            </th>

                                                            <th
                                                                class="
                                                                    text-end
                                                                    pe-3
                                                                "
                                                            >
                                                                Quantidade
                                                            </th>
                                                        </tr>
                                                    </thead>

                                                    <tbody>
                                                        @foreach(
                                                            $materiaisConsumidos
                                                            as $materialConsumido
                                                        )
                                                            @php
                                                                $material =
                                                                    $materialConsumido[
                                                                        'material'
                                                                    ];

                                                                $quantidadeMaterial =
                                                                    $materialConsumido[
                                                                        'quantidade'
                                                                    ];
                                                            @endphp

                                                            <tr>
                                                                <td
                                                                    class="
                                                                        ps-3
                                                                    "
                                                                >
                                                                    <strong>
                                                                        {{
                                                                            $material
                                                                                ?->nome_material
                                                                            ??
                                                                            'Material indisponível'
                                                                        }}
                                                                    </strong>
                                                                </td>

                                                                <td>
                                                                    {{
                                                                        $material
                                                                            ?->cor
                                                                        ?:
                                                                        '—'
                                                                    }}
                                                                </td>

                                                                <td
                                                                    class="
                                                                        text-end
                                                                        text-nowrap
                                                                        pe-3
                                                                    "
                                                                >
                                                                    @if($material)
                                                                        {{
                                                                            $formatarQuantidadeMaterial(
                                                                                $quantidadeMaterial,
                                                                                $material
                                                                                    ->unidade_medida
                                                                            )
                                                                        }}
                                                                    @else
                                                                        {{
                                                                            number_format(
                                                                                $quantidadeMaterial,
                                                                                3,
                                                                                ',',
                                                                                '.'
                                                                            )
                                                                        }}
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        @if(
                                            $bloco[
                                                'movimentacoes'
                                            ]->isNotEmpty()
                                        )
                                            <details
                                                class="mt-3"
                                            >
                                                <summary
                                                    class="
                                                        small
                                                        text-muted
                                                        user-select-none
                                                    "
                                                    style="cursor: pointer;"
                                                >
                                                    Ver detalhes técnicos
                                                    ({{
                                                        $bloco[
                                                            'movimentacoes'
                                                        ]->count()
                                                    }}
                                                    registros)
                                                </summary>

                                                <div
                                                    class="
                                                        small
                                                        text-muted
                                                        mt-2
                                                    "
                                                >
                                                    @foreach(
                                                        $bloco[
                                                            'movimentacoes'
                                                        ]
                                                        as $registro
                                                    )
                                                        <div
                                                            class="
                                                                mb-1
                                                            "
                                                        >
                                                            <strong>
                                                                {{
                                                                    $registro
                                                                        ->material
                                                                        ?->nome_material
                                                                    ??
                                                                    'Material indisponível'
                                                                }}:
                                                            </strong>

                                                            @if(
                                                                $registro
                                                                    ->material
                                                            )
                                                                {{
                                                                    $formatarQuantidadeMaterial(
                                                                        $registro
                                                                            ->quantidade,
                                                                        $registro
                                                                            ->material
                                                                            ->unidade_medida
                                                                    )
                                                                }}
                                                            @else
                                                                {{
                                                                    $registro
                                                                        ->quantidade
                                                                }}
                                                            @endif

                                                            @if(
                                                                $registro
                                                                    ->observacao
                                                            )
                                                                ·
                                                                {{
                                                                    $registro
                                                                        ->observacao
                                                                }}
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </details>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                        @else

                            <tr>
                                <td class="text-nowrap">
                                    <strong class="d-block">
                                        {{
                                            $movimentacao
                                                ->data_movimentacao
                                                ->copy()
                                                ->timezone($timezone)
                                                ->format(
                                                    'd/m/Y'
                                                )
                                        }}
                                    </strong>

                                    <small class="text-muted">
                                        {{
                                            $movimentacao
                                                ->data_movimentacao
                                                ->copy()
                                                ->timezone($timezone)
                                                ->format(
                                                    'H:i'
                                                )
                                        }}
                                    </small>
                                </td>

                                <td>
                                    @if(
                                        $movimentacao
                                            ->variacao
                                    )
                                        <strong>
                                            {{
                                                $movimentacao
                                                    ->variacao
                                                    ->produto
                                                    ?->nome_produto
                                                ??
                                                'Produto indisponível'
                                            }}
                                        </strong>

                                        <br>

                                        <small class="text-muted">
                                            {{
                                                $movimentacao
                                                    ->variacao
                                                    ->tamanho
                                            }}

                                            /

                                            {{
                                                $movimentacao
                                                    ->variacao
                                                    ->cor
                                            }}
                                        </small>

                                    @elseif(
                                        $movimentacao
                                            ->material
                                    )
                                        <strong>
                                            {{
                                                $movimentacao
                                                    ->material
                                                    ->nome_material
                                            }}
                                        </strong>

                                        @if(
                                            $movimentacao
                                                ->material
                                                ->cor
                                        )
                                            <br>

                                            <small
                                                class="text-muted"
                                            >
                                                {{
                                                    $movimentacao
                                                        ->material
                                                        ->cor
                                                }}
                                            </small>
                                        @endif

                                    @else
                                        <span class="text-muted">
                                            Item não disponível
                                        </span>
                                    @endif

                                    @if(
                                        $movimentacao
                                            ->ordem
                                    )
                                        <div
                                            class="
                                                small
                                                text-muted
                                                mt-1
                                            "
                                        >
                                            {{
                                                $movimentacao
                                                    ->ordem
                                                    ->codigo
                                            }}

                                            @if(
                                                $movimentacao
                                                    ->itemProducao
                                            )
                                                · lote
                                                {{
                                                    $movimentacao
                                                        ->itemProducao
                                                        ->produto
                                                        ?->nome_produto
                                                    ??
                                                    'indisponível'
                                                }}
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    @if(
                                        $movimentacao
                                            ->tipo_movimentacao
                                        === 'entrada'
                                    )
                                        <span
                                            class="
                                                badge
                                                bg-success-subtle
                                                text-success-emphasis
                                                border
                                                border-success-subtle
                                            "
                                        >
                                            Entrada
                                        </span>
                                    @else
                                        <span
                                            class="
                                                badge
                                                bg-danger-subtle
                                                text-danger-emphasis
                                                border
                                                border-danger-subtle
                                            "
                                        >
                                            Saída
                                        </span>
                                    @endif
                                </td>

                                <td class="text-nowrap">
                                    @if(
                                        $movimentacao
                                            ->variacao
                                    )
                                        {{
                                            number_format(
                                                $movimentacao
                                                    ->quantidade,
                                                0,
                                                ',',
                                                '.'
                                            )
                                        }}
                                        un.

                                    @elseif(
                                        $movimentacao
                                            ->material
                                    )
                                        {{
                                            $formatarQuantidadeMaterial(
                                                $movimentacao
                                                    ->quantidade,

                                                $movimentacao
                                                    ->material
                                                    ->unidade_medida
                                            )
                                        }}

                                    @else
                                        {{
                                            $movimentacao
                                                ->quantidade
                                        }}
                                    @endif
                                </td>

                                <td>
                                    @if($origemAtual)
                                        <span
                                            class="
                                                badge
                                                {{
                                                    $origemAtual[
                                                        'classe'
                                                    ]
                                                }}
                                            "
                                        >
                                            {{
                                                $origemAtual[
                                                    'texto'
                                                ]
                                            }}
                                        </span>
                                    @else
                                        <span
                                            class="
                                                badge
                                                bg-secondary-subtle
                                                text-secondary-emphasis
                                                border
                                                border-secondary-subtle
                                            "
                                        >
                                            {{
                                                ucfirst(
                                                    str_replace(
                                                        '_',
                                                        ' ',
                                                        $movimentacao
                                                            ->origem
                                                    )
                                                )
                                            }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{
                                        $movimentacao
                                            ->usuario
                                            ?->name
                                        ??
                                        'Não disponível'
                                    }}
                                </td>

                                <td>
                                    @if(
                                        $movimentacao
                                            ->observacao
                                    )
                                        <span
                                            title="{{
                                                $movimentacao
                                                    ->observacao
                                            }}"
                                        >
                                            {{
                                                \Illuminate\Support\Str::limit(
                                                    $movimentacao
                                                        ->observacao,
                                                    110
                                                )
                                            }}
                                        </span>
                                    @else
                                        <span class="text-muted">
                                            Sem observação
                                        </span>
                                    @endif
                                </td>
                            </tr>

                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Paginação --}}
    @if($blocosMovimentacao->lastPage() > 1)
        <div
            class="
                d-flex
                flex-column
                flex-sm-row
                justify-content-between
                align-items-sm-center
                gap-3
                mt-3
            "
        >
            <div class="small text-muted">
                Página
                {{ $blocosMovimentacao->currentPage() }}
                de
                {{ $blocosMovimentacao->lastPage() }}
            </div>

            <div class="d-flex gap-2">
                @if(
                    $blocosMovimentacao
                        ->onFirstPage()
                )
                    <button
                        type="button"
                        class="
                            btn
                            btn-outline-secondary
                            btn-sm
                        "
                        disabled
                    >
                        Anterior
                    </button>
                @else
                    <a
                        href="{{
                            $blocosMovimentacao
                                ->previousPageUrl()
                        }}"
                        class="
                            btn
                            btn-outline-secondary
                            btn-sm
                        "
                    >
                        Anterior
                    </a>
                @endif

                @if(
                    $blocosMovimentacao
                        ->hasMorePages()
                )
                    <a
                        href="{{
                            $blocosMovimentacao
                                ->nextPageUrl()
                        }}"
                        class="
                            btn
                            btn-outline-primary
                            btn-sm
                        "
                    >
                        Próxima
                    </a>
                @else
                    <button
                        type="button"
                        class="
                            btn
                            btn-outline-secondary
                            btn-sm
                        "
                        disabled
                    >
                        Próxima
                    </button>
                @endif
            </div>
        </div>
    @endif

@endif

@endsection
