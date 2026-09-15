@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

@php
    $totalProdutosCriticos =
        $produtosSemEstoque
        + $produtosEstoqueBaixo;

    $totalMateriaisCriticos =
        $materiaisSemEstoque
        + $materiaisEstoqueBaixo;

    $corProdutosCriticos =
        $produtosSemEstoque > 0
            ? 'danger'
            : (
                $produtosEstoqueBaixo > 0
                    ? 'warning'
                    : 'success'
            );

    $corMateriaisCriticos =
        $materiaisSemEstoque > 0
            ? 'danger'
            : (
                $materiaisEstoqueBaixo > 0
                    ? 'warning'
                    : 'success'
            );

    $corAtrasadas =
        $ordensAtrasadas > 0
            ? 'danger'
            : 'success';

    $nomesOrigem = [
        'manual' => 'Manual',
        'producao_consumo' => 'Produção',
        'producao_conclusao' => 'Conclusão',
        'producao_reversao' => 'Reversão',
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

            $quantidade = number_format(
                $numero,
                $decimais,
                ',',
                '.'
            );

            $sufixo = match ($unidade) {
                'un' => 'un.',

                'rolo' =>
                    abs($numero - 1) < 0.0005
                        ? 'rolo'
                        : 'rolos',

                default => $unidade,
            };

            return
                $quantidade
                . ' '
                . $sufixo;
        };
@endphp

{{-- Cabeçalho --}}
<div class="mb-4">
    <h1 class="mb-1">
        Dashboard
    </h1>

    <p class="text-muted mb-0">
        Acompanhe os pontos que exigem atenção no estoque
        e o andamento atual da produção.
    </p>
</div>

{{-- Indicadores principais --}}
<div class="row g-3 mb-5">

    <div class="col-sm-6 col-xl-3">
        <div
            class="
                card
                dashboard-kpi
                dashboard-kpi-{{ $corProdutosCriticos }}
                h-100
            "
        >
            <div class="card-body">
                <div
                    class="
                        d-flex
                        justify-content-between
                        align-items-start
                        gap-3
                    "
                >
                    <div>
                        <div
                            class="
                                dashboard-kpi-title
                                mb-2
                            "
                        >
                            Produtos críticos
                        </div>

                        <div
                            class="
                                dashboard-kpi-value
                                fs-2
                                fw-bold
                                lh-1
                                mb-2
                            "
                        >
                            {{ $totalProdutosCriticos }}
                        </div>

                        <div class="small text-muted">
                            {{ $produtosSemEstoque }}
                            sem estoque
                            ·
                            {{ $produtosEstoqueBaixo }}
                            com estoque baixo
                        </div>
                    </div>

                    <span class="dashboard-kpi-tag">
                        Estoque
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div
            class="
                card
                dashboard-kpi
                dashboard-kpi-{{ $corMateriaisCriticos }}
                h-100
            "
        >
            <div class="card-body">
                <div
                    class="
                        d-flex
                        justify-content-between
                        align-items-start
                        gap-3
                    "
                >
                    <div>
                        <div
                            class="
                                dashboard-kpi-title
                                mb-2
                            "
                        >
                            Materiais críticos
                        </div>

                        <div
                            class="
                                dashboard-kpi-value
                                fs-2
                                fw-bold
                                lh-1
                                mb-2
                            "
                        >
                            {{ $totalMateriaisCriticos }}
                        </div>

                        <div class="small text-muted">
                            {{ $materiaisSemEstoque }}
                            sem estoque
                            ·
                            {{ $materiaisEstoqueBaixo }}
                            com estoque baixo
                        </div>
                    </div>

                    <span class="dashboard-kpi-tag">
                        Materiais
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div
            class="
                card
                dashboard-kpi
                dashboard-kpi-primary
                h-100
            "
        >
            <div class="card-body">
                <div
                    class="
                        d-flex
                        justify-content-between
                        align-items-start
                        gap-3
                    "
                >
                    <div>
                        <div
                            class="
                                dashboard-kpi-title
                                mb-2
                            "
                        >
                            Em produção
                        </div>

                        <div
                            class="
                                dashboard-kpi-value
                                fs-2
                                fw-bold
                                lh-1
                                mb-2
                            "
                        >
                            {{ $ordensEmProducao }}
                        </div>

                        <div class="small text-muted">
                            ordens em andamento
                        </div>
                    </div>

                    <span class="dashboard-kpi-tag">
                        Produção
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div
            class="
                card
                dashboard-kpi
                dashboard-kpi-{{ $corAtrasadas }}
                h-100
            "
        >
            <div class="card-body">
                <div
                    class="
                        d-flex
                        justify-content-between
                        align-items-start
                        gap-3
                    "
                >
                    <div>
                        <div
                            class="
                                dashboard-kpi-title
                                mb-2
                            "
                        >
                            Ordens atrasadas
                        </div>

                        <div
                            class="
                                dashboard-kpi-value
                                fs-2
                                fw-bold
                                lh-1
                                mb-2
                            "
                        >
                            {{ $ordensAtrasadas }}
                        </div>

                        <div class="small text-muted">
                            ainda não concluídas
                        </div>
                    </div>

                    <span class="dashboard-kpi-tag">
                        Prazo
                    </span>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Estoque em atenção --}}
<div
    class="
        d-flex
        flex-column
        flex-md-row
        justify-content-between
        align-items-md-end
        gap-2
        mb-3
    "
>
    <div>
        <h2 class="h4 mb-1">
            Estoque que precisa de atenção
        </h2>

        <p class="text-muted small mb-0">
            Itens sem estoque ou que atingiram o nível mínimo.
        </p>
    </div>
</div>

<div class="row g-4 mb-5">

    {{-- Produtos --}}
    <div class="col-xl-6">
        <div
            class="
                d-flex
                justify-content-between
                align-items-center
                mb-3
            "
        >
            <h3 class="h5 mb-0">
                Produtos
            </h3>

            <a
                href="{{ route('produtos.index') }}"
                class="btn btn-outline-primary btn-sm"
            >
                Ver produtos
            </a>
        </div>

        @if($produtosCriticos->isEmpty())
            <div class="card h-100">
                <div class="card-body">
                    <div class="alert alert-success mb-0">
                        Nenhuma variação de produto
                        precisa de atenção no momento.
                    </div>
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
                                    Situação
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach(
                                $produtosCriticos
                                as $variacao
                            )
                                <tr>
                                    <td>
                                        <strong>
                                            {{
                                                $variacao
                                                    ->produto
                                                    ->nome_produto
                                            }}
                                        </strong>
                                    </td>

                                    <td>
                                        {{ $variacao->tamanho }}
                                        /
                                        {{ $variacao->cor }}
                                    </td>

                                    <td>
                                        {{
                                            number_format(
                                                $variacao->quantidade,
                                                0,
                                                ',',
                                                '.'
                                            )
                                        }}
                                        un.
                                    </td>

                                    <td>
                                        {{
                                            number_format(
                                                $variacao->estoque_minimo,
                                                0,
                                                ',',
                                                '.'
                                            )
                                        }}
                                        un.
                                    </td>

                                    <td>
                                        @if(
                                            $variacao->quantidade
                                            <= 0
                                        )
                                            <span
                                                class="
                                                    badge
                                                    text-bg-danger
                                                "
                                            >
                                                Sem estoque
                                            </span>
                                        @else
                                            <span
                                                class="
                                                    badge
                                                    text-bg-warning
                                                "
                                            >
                                                Estoque baixo
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
    </div>

    {{-- Materiais --}}
    <div class="col-xl-6">
        <div
            class="
                d-flex
                justify-content-between
                align-items-center
                mb-3
            "
        >
            <h3 class="h5 mb-0">
                Materiais
            </h3>

            <a
                href="{{ route('materiais.index') }}"
                class="btn btn-outline-primary btn-sm"
            >
                Ver materiais
            </a>
        </div>

        @if($materiaisCriticos->isEmpty())
            <div class="card h-100">
                <div class="card-body">
                    <div class="alert alert-success mb-0">
                        Nenhum material precisa de atenção
                        no momento.
                    </div>
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
                                    Material
                                </th>

                                <th>
                                    Estoque
                                </th>

                                <th>
                                    Mínimo
                                </th>

                                <th>
                                    Situação
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach(
                                $materiaisCriticos
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
                                                {{ $material->cor }}
                                            </small>
                                        @endif
                                    </td>

                                    <td>
                                        {{
                                            $formatarQuantidadeMaterial(
                                                $material->quantidade,
                                                $material->unidade_medida
                                            )
                                        }}
                                    </td>

                                    <td>
                                        {{
                                            $formatarQuantidadeMaterial(
                                                $material->estoque_minimo,
                                                $material->unidade_medida
                                            )
                                        }}
                                    </td>

                                    <td>
                                        @if(
                                            (float)
                                            $material->quantidade
                                            <= 0
                                        )
                                            <span
                                                class="
                                                    badge
                                                    text-bg-danger
                                                "
                                            >
                                                Sem estoque
                                            </span>
                                        @else
                                            <span
                                                class="
                                                    badge
                                                    text-bg-warning
                                                "
                                            >
                                                Estoque baixo
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
    </div>

</div>

{{-- Produção --}}
<div
    class="
        d-flex
        flex-column
        flex-md-row
        justify-content-between
        align-items-md-end
        gap-3
        mb-3
    "
>
    <div>
        <h2 class="h4 mb-1">
            Produção
        </h2>

        <p class="text-muted small mb-0">
            Situação das ordens e distribuição atual
            dos itens pelas etapas produtivas.
        </p>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <span
            class="
                dashboard-summary-pill
                dashboard-summary-secondary
            "
        >
            Planejadas:
            <strong>
                {{ $ordensPlanejadas }}
            </strong>
        </span>

        <span
            class="
                dashboard-summary-pill
                dashboard-summary-primary
            "
        >
            Em produção:
            <strong>
                {{ $ordensEmProducao }}
            </strong>
        </span>

        <span
            class="
                dashboard-summary-pill
                dashboard-summary-success
            "
        >
            Concluídas:
            <strong>
                {{ $ordensConcluidas }}
            </strong>
        </span>
    </div>
</div>

<div class="row g-4 mb-5">

    {{-- Fluxo produtivo --}}
    <div class="col-xl-5">
        <h3 class="h5 mb-3">
            Fluxo atual
        </h3>

        <div class="card h-100">
            <div class="card-body p-0">

                @foreach(
                    $producaoPorEtapa
                    as $etapa
                )
                    @php
                        $etapaAtiva =
                            $etapa['itens'] > 0;
                    @endphp

                    <div
                        class="
                            dashboard-production-step

                            {{
                                $etapaAtiva
                                    ? 'is-active'
                                    : ''
                            }}

                            @if(!$loop->last)
                                border-bottom
                            @endif
                        "
                    >
                        <span
                            class="
                                dashboard-step-number

                                {{
                                    $etapaAtiva
                                        ? 'is-active'
                                        : ''
                                }}
                            "
                        >
                            {{ $loop->iteration }}
                        </span>

                        <div class="flex-grow-1">
                            <strong class="d-block">
                                {{ $etapa['nome'] }}
                            </strong>

                            <small class="text-muted">
                                {{ $etapa['itens'] }}

                                {{
                                    $etapa['itens'] === 1
                                        ? 'item'
                                        : 'itens'
                                }}
                            </small>
                        </div>

                        <div class="text-end">
                            <strong
                                class="
                                    d-block
                                    fs-5
                                "
                            >
                                {{ $etapa['unidades'] }}
                            </strong>

                            <small class="text-muted">
                                {{
                                    $etapa['unidades'] === 1
                                        ? 'unidade'
                                        : 'unidades'
                                }}
                            </small>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </div>

    {{-- Próximas ordens --}}
    <div class="col-xl-7">
        <div
            class="
                d-flex
                justify-content-between
                align-items-center
                mb-3
            "
        >
            <h3 class="h5 mb-0">
                Próximas ordens
            </h3>

            <a
                href="{{ route('ordens.index') }}"
                class="btn btn-outline-primary btn-sm"
            >
                Ver produção
            </a>
        </div>

        @if($ordensAtivas->isEmpty())
            <div class="card h-100">
                <div class="card-body">
                    <div class="alert alert-secondary mb-0">
                        Nenhuma ordem planejada ou
                        em produção.
                    </div>
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
                                    Ordem
                                </th>

                                <th>
                                    Cliente
                                </th>

                                <th class="text-center">
                                    Itens
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Previsão
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach(
                                $ordensAtivas
                                as $ordem
                            )
                                @php
                                    $atrasada =
                                        $ordem->data_prevista
                                        && $ordem
                                            ->data_prevista
                                            ->lt(today());
                                @endphp

                                <tr>
                                    <td>
                                        <a
                                            href="{{
                                                route(
                                                    'ordens.show',
                                                    $ordem
                                                )
                                            }}"
                                            class="
                                                fw-semibold
                                                text-decoration-none
                                            "
                                        >
                                            {{ $ordem->codigo }}
                                        </a>
                                    </td>

                                    <td>
                                        {{
                                            $ordem->cliente_nome
                                            ?? '-'
                                        }}
                                    </td>

                                    <td class="text-center">
                                        {{ $ordem->itens_count }}
                                    </td>

                                    <td>
                                        @if(
                                            $ordem->status
                                            === 'planejada'
                                        )
                                            <span
                                                class="
                                                    badge
                                                    text-bg-secondary
                                                "
                                            >
                                                Planejada
                                            </span>
                                        @else
                                            <span
                                                class="
                                                    badge
                                                    text-bg-primary
                                                "
                                            >
                                                Em produção
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        @if(
                                            $ordem->data_prevista
                                        )
                                            <span
                                                class="
                                                    {{
                                                        $atrasada
                                                            ? 'text-danger fw-semibold'
                                                            : ''
                                                    }}
                                                "
                                            >
                                                {{
                                                    $ordem
                                                        ->data_prevista
                                                        ->format(
                                                            'd/m/Y'
                                                        )
                                                }}
                                            </span>

                                            @if($atrasada)
                                                <br>

                                                <small
                                                    class="
                                                        text-danger
                                                    "
                                                >
                                                    Atrasada
                                                </small>
                                            @endif
                                        @else
                                            <span
                                                class="text-muted"
                                            >
                                                Sem previsão
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
    </div>

</div>

{{-- Últimas movimentações --}}
<div
    class="
        d-flex
        justify-content-between
        align-items-center
        gap-3
        mb-3
    "
>
    <div>
        <h2 class="h4 mb-1">
            Últimas movimentações
        </h2>

        <p class="text-muted small mb-0">
            Entradas e saídas mais recentes do estoque.
        </p>
    </div>

    <a
        href="{{ route('movimentacoes.index') }}"
        class="btn btn-outline-primary btn-sm"
    >
        Ver todas
    </a>
</div>

@if($ultimasMovimentacoes->isEmpty())
    <div class="card">
        <div class="card-body">
            <div class="alert alert-secondary mb-0">
                Nenhuma movimentação registrada.
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
                    </tr>
                </thead>

                <tbody>
                    @foreach(
                        $ultimasMovimentacoes
                        as $movimentacao
                    )
                        <tr>
                            <td class="text-nowrap">
                                {{
                                    $movimentacao
                                        ->data_movimentacao
                                        ->format(
                                            'd/m/Y H:i'
                                        )
                                }}
                            </td>

                            <td>
                                @if(
                                    $movimentacao->variacao
                                )
                                    <strong>
                                        {{
                                            $movimentacao
                                                ->variacao
                                                ->produto
                                                ->nome_produto
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
                                    $movimentacao->material
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
                                            text-bg-success
                                        "
                                    >
                                        Entrada
                                    </span>
                                @else
                                    <span
                                        class="
                                            badge
                                            text-bg-danger
                                        "
                                    >
                                        Saída
                                    </span>
                                @endif
                            </td>

                            <td class="text-nowrap">
                                @if(
                                    $movimentacao->variacao
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
                                    $movimentacao->material
                                )
                                    {{
                                        $formatarQuantidadeMaterial(
                                            $movimentacao->quantidade,
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
                                {{
                                    $nomesOrigem[
                                        $movimentacao->origem
                                    ]
                                    ?? ucfirst(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $movimentacao
                                                ->origem
                                        )
                                    )
                                }}
                            </td>

                            <td>
                                {{
                                    $movimentacao
                                        ->usuario
                                        ->name
                                }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection
