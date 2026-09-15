@extends('layouts.app')

@section('title', 'Ordem de Produção')

@section('content')

@php
    $statusConfig = [
        'planejada' => [
            'texto' => 'Planejada',
            'classe' =>
                'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle',
        ],

        'em_producao' => [
            'texto' => 'Em produção',
            'classe' =>
                'bg-primary-subtle text-primary-emphasis border border-primary-subtle',
        ],

        'concluida' => [
            'texto' => 'Concluída',
            'classe' =>
                'bg-success-subtle text-success-emphasis border border-success-subtle',
        ],

        'cancelada' => [
            'texto' => 'Cancelada',
            'classe' =>
                'bg-danger-subtle text-danger-emphasis border border-danger-subtle',
        ],
    ];

    $etapasConfig = [
        'corte' => [
            'texto' => 'Corte',
            'classe' =>
                'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle',
        ],

        'bordado' => [
            'texto' => 'Bordado',
            'classe' =>
                'bg-info-subtle text-info-emphasis border border-info-subtle',
        ],

        'costura' => [
            'texto' => 'Costura',
            'classe' =>
                'bg-primary-subtle text-primary-emphasis border border-primary-subtle',
        ],

        'finalizacao' => [
            'texto' => 'Finalização',
            'classe' =>
                'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
        ],

        'concluido' => [
            'texto' => 'Concluído',
            'classe' =>
                'bg-success-subtle text-success-emphasis border border-success-subtle',
        ],

        'cancelado' => [
            'texto' => 'Cancelado',
            'classe' =>
                'bg-danger-subtle text-danger-emphasis border border-danger-subtle',
        ],
    ];

    $nomesEtapas = [
        'corte' => 'Corte',
        'bordado' => 'Bordado',
        'costura' => 'Costura',
        'finalizacao' => 'Finalização',
        'concluido' => 'Concluído',
        'cancelado' => 'Cancelado',
    ];

    $proximasEtapas = [
        'corte' => 'bordado',
        'bordado' => 'costura',
        'costura' => 'finalizacao',
        'finalizacao' => 'concluido',
    ];

    $etapasAnteriores = [
        'bordado' => 'corte',
        'costura' => 'bordado',
        'finalizacao' => 'costura',
        'concluido' => 'finalizacao',
    ];

    $configStatus =
        $statusConfig[$ordem->status]
        ?? $statusConfig['planejada'];

    $quantidadeItensPendentes =
        $ordem->itens
            ->where(
                'etapa_atual',
                '!=',
                'concluido'
            )
            ->count();

    $totalItens =
        $ordem->itens->count();

    $totalUnidades =
        $ordem->itens->sum(
            function ($item) {
                return (int) $item->quantidade;
            }
        );

    $itensConcluidos =
        $ordem->itens
            ->where(
                'etapa_atual',
                'concluido'
            )
            ->count();

    $possuiItemConcluido =
        $itensConcluidos > 0;

    $atrasada =
        in_array(
            $ordem->status,
            [
                'planejada',
                'em_producao',
            ],
            true
        )
        &&
        $ordem->data_prevista
        &&
        $ordem
            ->data_prevista
            ->lt(today());
@endphp

<div class="mb-3">
    <a
        href="{{ route('ordens.index') }}"
        class="btn btn-outline-secondary btn-sm"
    >
        ← Voltar para produção
    </a>
</div>

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
            {{ $ordem->codigo }}
        </h1>

        <p class="text-muted mb-0">
            Ordem de Produção
        </p>
    </div>

    <span
        class="
            badge
            fs-6
            {{ $configStatus['classe'] }}
        "
    >
        {{ $configStatus['texto'] }}
    </span>
</div>

<div class="card mb-4">
    <div class="card-body p-4">

        <div class="row g-4">

            <div class="col-sm-6 col-lg-3">
                <div class="small text-muted mb-1">
                    Cliente
                </div>

                <strong>
                    {{
                        $ordem->cliente_nome
                        ?? 'Sem cliente definido'
                    }}
                </strong>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="small text-muted mb-1">
                    Data prevista
                </div>

                @if($ordem->data_prevista)

                    <strong
                        class="{{
                            $atrasada
                                ? 'text-danger'
                                : ''
                        }}"
                    >
                        {{
                            $ordem
                                ->data_prevista
                                ->format('d/m/Y')
                        }}
                    </strong>

                    @if($atrasada)
                        <div class="mt-1">
                            <span
                                class="
                                    badge
                                    bg-danger-subtle
                                    text-danger-emphasis
                                    border
                                    border-danger-subtle
                                "
                            >
                                Atrasada
                            </span>
                        </div>
                    @endif

                @else

                    <span class="text-muted">
                        Sem previsão
                    </span>

                @endif
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="small text-muted mb-1">
                    Criada por
                </div>

                <strong>
                    {{
                        $ordem
                            ->criador
                            ?->name
                        ?? 'Não disponível'
                    }}
                </strong>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="small text-muted mb-1">
                    Produção
                </div>

                <strong>
                    {{ $totalItens }}

                    {{
                        $totalItens === 1
                            ? 'item'
                            : 'itens'
                    }}
                </strong>

                <div class="small text-muted">
                    {{ $totalUnidades }}

                    {{
                        $totalUnidades === 1
                            ? 'unidade'
                            : 'unidades'
                    }}
                </div>
            </div>

        </div>

        <hr class="my-4">

        <div>
            <div class="small text-muted mb-1">
                Observações
            </div>

            @if($ordem->observacao)
                <div>
                    {{ $ordem->observacao }}
                </div>
            @else
                <span class="text-muted">
                    Nenhuma observação informada.
                </span>
            @endif
        </div>

    </div>
</div>

@if($ordem->status === 'cancelada')

    <div
        class="
            alert
            bg-danger-subtle
            text-danger-emphasis
            border
            border-danger-subtle
            mb-4
        "
    >
        <h2 class="h5 mb-3">
            Ordem cancelada
        </h2>

        <div class="row g-3">

            <div class="col-md-4">
                <div class="small mb-1">
                    Data do cancelamento
                </div>

                <strong>
                    @if($ordem->cancelada_em)
                        {{
                            $ordem
                                ->cancelada_em
                                ->format(
                                    'd/m/Y H:i:s'
                                )
                        }}
                    @else
                        Não disponível
                    @endif
                </strong>
            </div>

            <div class="col-md-4">
                <div class="small mb-1">
                    Responsável
                </div>

                <strong>
                    {{
                        $ordem
                            ->cancelador
                            ?->name
                        ?? 'Não disponível'
                    }}
                </strong>
            </div>

            <div class="col-md-4">
                <div class="small mb-1">
                    Motivo
                </div>

                <strong>
                    {{
                        $ordem
                            ->motivo_cancelamento
                        ?? 'Não informado'
                    }}
                </strong>
            </div>

        </div>
    </div>

@endif

@if($ordem->status === 'concluida')

    <div
        class="
            alert
            bg-success-subtle
            text-success-emphasis
            border
            border-success-subtle
            mb-4
        "
    >
        <strong>
            Ordem encerrada definitivamente.
        </strong>

        Todos os itens foram concluídos e esta ordem
        não permite mais reversão de etapas,
        edição ou cancelamento.
    </div>

@endif

@if($ordem->status === 'planejada')

    <div class="card border-primary-subtle mb-4">
        <div
            class="
                card-body
                p-4
                d-flex
                flex-column
                flex-lg-row
                justify-content-between
                align-items-lg-center
                gap-3
            "
        >
            <div>
                <h2 class="h5 mb-1">
                    Produção ainda não iniciada
                </h2>

                <p class="text-muted mb-0">
                    Revise os itens antes de iniciar.
                    Depois disso, eles não poderão mais
                    ser editados ou removidos.
                </p>
            </div>

            @if($ordem->itens->isNotEmpty())

                <form
                    action="{{
                        route(
                            'ordens.iniciar',
                            $ordem
                        )
                    }}"
                    method="POST"
                    data-confirm="Deseja iniciar esta ordem de produção? Os itens deixarão de poder ser editados ou removidos e os materiais definidos nas fichas de consumo serão baixados automaticamente do estoque."
                    data-confirm-title="Iniciar produção"
                    data-confirm-button="Iniciar produção"
                    data-confirm-variant="primary"
                >
                    @csrf

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Iniciar produção
                    </button>
                </form>

            @else

                <span
                    class="d-inline-block"
                    tabindex="0"
                    title="Iniciar indisponível — Adicione pelo menos um item antes de iniciar a produção."
                >
                    <button
                        type="button"
                        class="btn btn-primary"
                        disabled
                    >
                        Iniciar produção
                    </button>
                </span>

            @endif
        </div>
    </div>

@endif

@if($ordem->status === 'em_producao')

    <div class="d-flex flex-wrap gap-2 mb-4">

        <span
            class="
                badge
                bg-primary-subtle
                text-primary-emphasis
                border
                border-primary-subtle
                p-2
            "
        >
            {{ $quantidadeItensPendentes }}
            pendentes
        </span>

        <span
            class="
                badge
                bg-success-subtle
                text-success-emphasis
                border
                border-success-subtle
                p-2
            "
        >
            {{ $itensConcluidos }}
            concluídos
        </span>

    </div>

@endif

<div
    class="
        d-flex
        flex-column
        flex-sm-row
        justify-content-between
        align-items-sm-end
        gap-3
        mb-3
    "
>
    <div>
        <h2 class="h4 mb-1">
            Itens da produção
        </h2>

        <p class="text-muted small mb-0">
            Produtos e especificações desta ordem.
        </p>
    </div>

    @if($ordem->status === 'planejada')

        <a
            href="{{
                route(
                    'ordens.itens.create',
                    $ordem
                )
            }}"
            class="btn btn-primary"
        >
            Adicionar item
        </a>

    @endif
</div>

@if($ordem->itens->isEmpty())

    <div class="card mb-5">
        <div class="card-body text-center py-5">

            <h3 class="h5 mb-2">
                Nenhum item adicionado
            </h3>

            <p class="text-muted mb-3">
                Adicione pelo menos um produto
                antes de iniciar esta ordem.
            </p>

            @if($ordem->status === 'planejada')

                <a
                    href="{{
                        route(
                            'ordens.itens.create',
                            $ordem
                        )
                    }}"
                    class="btn btn-primary"
                >
                    Adicionar item
                </a>

            @endif

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
                            Produto
                        </th>

                        <th class="text-center">
                            Quantidade
                        </th>

                        <th>
                            Etapa
                        </th>

                        <th>
                            Especificações
                        </th>

                        <th class="text-end">
                            Ações
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($ordem->itens as $item)

                        @php
                            $etapaConfig =
                                $etapasConfig[
                                    $item->etapa_atual
                                ]
                                ?? $etapasConfig['corte'];

                            $possuiEspecificacao =
                                $item->descricao_bordado
                                ||
                                $item->cor_linha
                                ||
                                $item->etiqueta
                                ||
                                $item->posicao_etiqueta
                                ||
                                $item->responsavel_costura
                                ||
                                $item->observacao;
                        @endphp

                        <tr>
                            <td>
                                <strong>
                                    {{
                                        $item
                                            ->variacao
                                            ->produto
                                            ->nome_produto
                                    }}
                                </strong>

                                <br>

                                <small class="text-muted">
                                    {{
                                        $item
                                            ->variacao
                                            ->tamanho
                                    }}

                                    /

                                    {{
                                        $item
                                            ->variacao
                                            ->cor
                                    }}
                                </small>
                            </td>

                            <td class="text-center">
                                <strong>
                                    {{
                                        number_format(
                                            $item->quantidade,
                                            0,
                                            ',',
                                            '.'
                                        )
                                    }}
                                </strong>

                                <span class="text-muted">
                                    un.
                                </span>
                            </td>

                            <td>
                                @if(
                                    $ordem->status
                                    === 'planejada'
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
                                        Aguardando início
                                    </span>
                                @else
                                    <span
                                        class="
                                            badge
                                            {{ $etapaConfig['classe'] }}
                                        "
                                    >
                                        {{
                                            $etapaConfig[
                                                'texto'
                                            ]
                                        }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if($possuiEspecificacao)

                                    @if($item->descricao_bordado)
                                        <div class="small mb-1">
                                            <strong>
                                                Bordado:
                                            </strong>

                                            {{
                                                \Illuminate\Support\Str::limit(
                                                    $item
                                                        ->descricao_bordado,
                                                    80
                                                )
                                            }}
                                        </div>
                                    @endif

                                    @if($item->cor_linha)
                                        <div class="small mb-1">
                                            <strong>
                                                Linha:
                                            </strong>

                                            {{ $item->cor_linha }}
                                        </div>
                                    @endif

                                    @if($item->etiqueta)
                                        <div class="small mb-1">
                                            <strong>
                                                Etiqueta/estampa:
                                            </strong>

                                            {{ $item->etiqueta }}

                                            @if(
                                                $item
                                                    ->posicao_etiqueta
                                            )
                                                <span class="text-muted">
                                                    ·
                                                    {{
                                                        $item
                                                            ->posicao_etiqueta
                                                    }}
                                                </span>
                                            @endif
                                        </div>

                                    @elseif(
                                        $item->posicao_etiqueta
                                    )

                                        <div class="small mb-1">
                                            <strong>
                                                Posição:
                                            </strong>

                                            {{
                                                $item
                                                    ->posicao_etiqueta
                                            }}
                                        </div>

                                    @endif

                                    @if(
                                        $item
                                            ->responsavel_costura
                                    )
                                        <div class="small mb-1">
                                            <strong>
                                                Costura:
                                            </strong>

                                            {{
                                                $item
                                                    ->responsavel_costura
                                            }}
                                        </div>
                                    @endif

                                    @if($item->observacao)
                                        <div
                                            class="small text-muted"
                                            title="{{
                                                $item->observacao
                                            }}"
                                        >
                                            {{
                                                \Illuminate\Support\Str::limit(
                                                    $item->observacao,
                                                    90
                                                )
                                            }}
                                        </div>
                                    @endif

                                @else

                                    <span class="text-muted">
                                        Sem especificações
                                    </span>

                                @endif
                            </td>

                            <td
                                class="
                                    text-end
                                    text-nowrap
                                "
                            >
                                @if(
                                    $ordem->status
                                    === 'planejada'
                                )

                                    <a
                                        href="{{
                                            route(
                                                'ordens.itens.edit',
                                                [
                                                    $ordem,
                                                    $item,
                                                ]
                                            )
                                        }}"
                                        class="
                                            btn
                                            btn-sm
                                            btn-outline-primary
                                        "
                                    >
                                        Editar
                                    </a>

                                    <form
                                        action="{{
                                            route(
                                                'ordens.itens.destroy',
                                                [
                                                    $ordem,
                                                    $item,
                                                ]
                                            )
                                        }}"
                                        method="POST"
                                        class="d-inline"
                                        data-confirm="Deseja remover este item da ordem? Como a produção ainda não foi iniciada, nenhuma movimentação de estoque será realizada."
                                        data-confirm-title="Remover item"
                                        data-confirm-button="Remover"
                                        data-confirm-variant="danger"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="
                                                btn
                                                btn-sm
                                                btn-outline-danger
                                            "
                                        >
                                            Remover
                                        </button>
                                    </form>

                                @elseif(
                                    $ordem->status
                                    === 'em_producao'
                                )

                                    @if(
                                        isset(
                                            $etapasAnteriores[
                                                $item->etapa_atual
                                            ]
                                        )
                                    )

                                        @php
                                            $etapaAnterior =
                                                $etapasAnteriores[
                                                    $item->etapa_atual
                                                ];

                                            $revertendoConclusao =
                                                $item->etapa_atual
                                                === 'concluido';

                                            $mensagemReversao =
                                                $revertendoConclusao
                                                    ? 'Este item será revertido para Finalização. As unidades adicionadas ao estoque de produtos acabados serão retiradas novamente, e a correção ficará registrada no histórico. Deseja continuar?'
                                                    : 'Este item será revertido para '
                                                        . $nomesEtapas[
                                                            $etapaAnterior
                                                        ]
                                                        . '. A correção ficará registrada no histórico. Deseja continuar?';

                                            $tituloReversao =
                                                $revertendoConclusao
                                                    ? 'Reverter conclusão'
                                                    : 'Reverter etapa';
                                        @endphp

                                        <form
                                            action="{{
                                                route(
                                                    'ordens.itens.reverter',
                                                    [
                                                        $ordem,
                                                        $item,
                                                    ]
                                                )
                                            }}"
                                            method="POST"
                                            class="d-inline"
                                            data-confirm="{{ $mensagemReversao }}"
                                            data-confirm-title="{{ $tituloReversao }}"
                                            data-confirm-button="Reverter"
                                            data-confirm-variant="warning"
                                        >
                                            @csrf

                                            <button
                                                type="submit"
                                                class="
                                                    btn
                                                    btn-sm
                                                    btn-outline-warning
                                                "
                                            >
                                                ←
                                                {{
                                                    $nomesEtapas[
                                                        $etapaAnterior
                                                    ]
                                                }}
                                            </button>
                                        </form>

                                    @endif

                                    @if(
                                        isset(
                                            $proximasEtapas[
                                                $item->etapa_atual
                                            ]
                                        )
                                    )

                                        @php
                                            $proximaEtapa =
                                                $proximasEtapas[
                                                    $item->etapa_atual
                                                ];

                                            $concluindoItem =
                                                $proximaEtapa
                                                === 'concluido';

                                            $ehFechamentoDefinitivo =
                                                $concluindoItem
                                                &&
                                                $quantidadeItensPendentes
                                                === 1;

                                            if (
                                                $ehFechamentoDefinitivo
                                            ) {
                                                $mensagemConfirmacao =
                                                    'Este é o último item pendente. Ao concluí-lo, o estoque do produto acabado será atualizado e a ordem será encerrada definitivamente. Depois disso, não será possível reverter etapas, editar ou cancelar esta ordem. Deseja continuar?';

                                                $tituloConfirmacao =
                                                    'Concluir e encerrar ordem';

                                                $botaoConfirmacao =
                                                    'Concluir e encerrar';

                                                $varianteConfirmacao =
                                                    'success';

                                            } elseif (
                                                $concluindoItem
                                            ) {
                                                $mensagemConfirmacao =
                                                    'Deseja concluir este item? A quantidade produzida será adicionada ao estoque de produtos acabados. A ordem continuará em produção enquanto houver outros itens pendentes.';

                                                $tituloConfirmacao =
                                                    'Concluir item';

                                                $botaoConfirmacao =
                                                    'Concluir item';

                                                $varianteConfirmacao =
                                                    'success';

                                            } else {
                                                $mensagemConfirmacao =
                                                    'Deseja avançar este item de '
                                                    . $nomesEtapas[
                                                        $item->etapa_atual
                                                    ]
                                                    . ' para '
                                                    . $nomesEtapas[
                                                        $proximaEtapa
                                                    ]
                                                    . '?';

                                                $tituloConfirmacao =
                                                    'Avançar etapa';

                                                $botaoConfirmacao =
                                                    'Avançar';

                                                $varianteConfirmacao =
                                                    'primary';
                                            }
                                        @endphp

                                        <form
                                            action="{{
                                                route(
                                                    'ordens.itens.avancar',
                                                    [
                                                        $ordem,
                                                        $item,
                                                    ]
                                                )
                                            }}"
                                            method="POST"
                                            class="d-inline"
                                            data-confirm="{{ $mensagemConfirmacao }}"
                                            data-confirm-title="{{ $tituloConfirmacao }}"
                                            data-confirm-button="{{ $botaoConfirmacao }}"
                                            data-confirm-variant="{{ $varianteConfirmacao }}"
                                        >
                                            @csrf

                                            <button
                                                type="submit"
                                                class="
                                                    btn
                                                    btn-sm

                                                    {{
                                                        $concluindoItem
                                                            ? 'btn-success'
                                                            : 'btn-primary'
                                                    }}
                                                "
                                            >
                                                @if(
                                                    $ehFechamentoDefinitivo
                                                )
                                                    Concluir e encerrar

                                                @elseif($concluindoItem)

                                                    Concluir item

                                                @else

                                                    {{
                                                        $nomesEtapas[
                                                            $proximaEtapa
                                                        ]
                                                    }}
                                                    →

                                                @endif
                                            </button>
                                        </form>

                                    @endif

                                @else

                                    <span class="text-muted small">
                                        Somente consulta
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

<div class="mb-3">
    <h2 class="h4 mb-1">
        Histórico da produção
    </h2>

    <p class="text-muted small mb-0">
        Registro das mudanças de etapa
        realizadas nesta ordem.
    </p>
</div>

@if($ordem->historicos->isEmpty())

    <div class="card mb-4">
        <div class="card-body">
            <div class="text-muted">
                A produção ainda não possui histórico.
            </div>
        </div>
    </div>

@else

    <div class="card mb-4">
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
                            Movimentação
                        </th>

                        <th>
                            Tipo
                        </th>

                        <th>
                            Responsável
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach(
                        $ordem->historicos
                        as $historico
                    )
                        <tr>
                            <td class="text-nowrap">
                                <strong class="d-block">
                                    {{
                                        $historico
                                            ->created_at
                                            ->format('d/m/Y')
                                    }}
                                </strong>

                                <small class="text-muted">
                                    {{
                                        $historico
                                            ->created_at
                                            ->format('H:i:s')
                                    }}
                                </small>
                            </td>

                            <td>
                                @if(
                                    $historico->item
                                    &&
                                    $historico
                                        ->item
                                        ->variacao
                                )
                                    <strong>
                                        {{
                                            $historico
                                                ->item
                                                ->variacao
                                                ->produto
                                                ?->nome_produto
                                            ?? 'Produto indisponível'
                                        }}
                                    </strong>

                                    <br>

                                    <small class="text-muted">
                                        {{
                                            $historico
                                                ->item
                                                ->variacao
                                                ->tamanho
                                        }}

                                        /

                                        {{
                                            $historico
                                                ->item
                                                ->variacao
                                                ->cor
                                        }}
                                    </small>

                                @else

                                    <span class="text-muted">
                                        Item não disponível
                                    </span>

                                @endif
                            </td>

                            <td>
                                @if(
                                    $historico->acao
                                    === 'cancelamento'
                                )

                                    @if(
                                        $historico
                                            ->etapa_origem
                                        === null
                                    )
                                        Planejada
                                    @else
                                        {{
                                            $nomesEtapas[
                                                $historico
                                                    ->etapa_origem
                                            ]
                                            ??
                                            $historico
                                                ->etapa_origem
                                        }}
                                    @endif

                                    →

                                    <strong class="text-danger">
                                        Cancelado
                                    </strong>

                                @elseif(
                                    $historico
                                        ->etapa_origem
                                    === null
                                )

                                    Início →

                                    <strong>
                                        {{
                                            $nomesEtapas[
                                                $historico
                                                    ->etapa_destino
                                            ]
                                            ??
                                            $historico
                                                ->etapa_destino
                                        }}
                                    </strong>

                                @else

                                    {{
                                        $nomesEtapas[
                                            $historico
                                                ->etapa_origem
                                        ]
                                        ??
                                        $historico
                                            ->etapa_origem
                                    }}

                                    →

                                    <strong>
                                        {{
                                            $nomesEtapas[
                                                $historico
                                                    ->etapa_destino
                                            ]
                                            ??
                                            $historico
                                                ->etapa_destino
                                        }}
                                    </strong>

                                @endif
                            </td>

                            <td>
                                @if(
                                    $historico->acao
                                    === 'cancelamento'
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
                                        Cancelamento
                                    </span>

                                @elseif(
                                    $historico->acao
                                    === 'reversao'
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
                                        Reversão
                                    </span>

                                @elseif(
                                    $historico->acao
                                    === 'inicio'
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
                                        Início
                                    </span>

                                @elseif(
                                    $historico->acao
                                    === 'conclusao'
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
                                        Conclusão
                                    </span>

                                @else

                                    <span
                                        class="
                                            badge
                                            bg-primary-subtle
                                            text-primary-emphasis
                                            border
                                            border-primary-subtle
                                        "
                                    >
                                        Avanço
                                    </span>

                                @endif
                            </td>

                            <td>
                                {{
                                    $historico
                                        ->usuario
                                        ?->name
                                    ?? 'Não disponível'
                                }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>

@endif

<div class="d-flex flex-wrap gap-2">

    <a
        href="{{ route('ordens.index') }}"
        class="btn btn-outline-secondary"
    >
        Voltar
    </a>

    @if($ordem->status === 'planejada')

        <a
            href="{{ route('ordens.edit', $ordem) }}"
            class="btn btn-outline-primary"
        >
            Editar ordem
        </a>

    @else

        <span
            class="d-inline-block"
            tabindex="0"
            title="Editar indisponível — Somente ordens planejadas podem ter seus dados cadastrais editados."
        >
            <button
                type="button"
                class="btn btn-outline-primary"
                disabled
            >
                Editar ordem
            </button>
        </span>

    @endif

    @if(
        $ordem->status === 'planejada'
        &&
        $ordem->itens->isEmpty()
        &&
        $ordem->historicos->isEmpty()
    )

        <form
            action="{{
                route(
                    'ordens.destroy',
                    $ordem
                )
            }}"
            method="POST"
            class="d-inline"
            data-confirm="Deseja excluir definitivamente esta ordem de produção? Esta ação não poderá ser desfeita."
            data-confirm-title="Excluir ordem"
            data-confirm-button="Excluir ordem"
            data-confirm-variant="danger"
        >
            @csrf
            @method('DELETE')

            <button
                type="submit"
                class="btn btn-outline-danger"
            >
                Excluir ordem
            </button>
        </form>

    @else

        <span
            class="d-inline-block"
            tabindex="0"
            title="{{
                $ordem->status !== 'planejada'
                    ? 'Excluir indisponível — Somente ordens planejadas podem ser excluídas.'
                    : (
                        $ordem->itens->isNotEmpty()
                            ? 'Excluir indisponível — Remova todos os itens antes de excluir esta ordem.'
                            : 'Excluir indisponível — Esta ordem possui histórico de produção e deve ser preservada.'
                    )
            }}"
        >
            <button
                type="button"
                class="btn btn-outline-danger"
                disabled
            >
                Excluir ordem
            </button>
        </span>

    @endif

    @if($ordem->status === 'planejada')

        <a
            href="{{
                route(
                    'ordens.cancelar.form',
                    $ordem
                )
            }}"
            class="btn btn-outline-danger"
        >
            Cancelar ordem
        </a>

    @elseif($ordem->status === 'em_producao')

        @if($possuiItemConcluido)

            <span
                class="d-inline-block"
                tabindex="0"
                title="Cancelar indisponível — Reverta primeiro todos os itens concluídos para Finalização antes de cancelar esta ordem."
            >
                <button
                    type="button"
                    class="btn btn-outline-danger"
                    disabled
                >
                    Cancelar ordem
                </button>
            </span>

        @else

            <a
                href="{{
                    route(
                        'ordens.cancelar.form',
                        $ordem
                    )
                }}"
                class="btn btn-outline-danger"
            >
                Cancelar ordem
            </a>

        @endif

    @endif

</div>

@endsection
