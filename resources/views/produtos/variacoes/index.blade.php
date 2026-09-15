@extends('layouts.app')

@section('title', 'Variações do Produto')

@section('content')

<div class="mb-3">
    <a
        href="{{ route('produtos.index') }}"
        class="btn btn-outline-secondary btn-sm"
    >
        ← Voltar para produtos
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
            Variações
        </h1>

        <p class="text-muted mb-0">
            Produto:

            <strong class="text-body">
                {{ $produto->nome_produto }}
            </strong>

            @if($produto->categoria)
                · {{ $produto->categoria }}
            @endif
        </p>
    </div>

    @if($produto->ativo)
        <a
            href="{{
                route(
                    'produtos.variacoes.create',
                    $produto
                )
            }}"
            class="btn btn-primary"
        >
            Nova variação
        </a>
    @endif
</div>

@if(!$produto->ativo)
    <div
        class="
            alert
            bg-warning-subtle
            text-warning-emphasis
            border
            border-warning-subtle
            mb-4
        "
    >
        <strong>
            Produto inativo.
        </strong>

        As variações podem ser consultadas e editadas,
        mas só poderão ser reativadas depois que o produto
        também for reativado.
    </div>
@endif

<div
    class="
        d-flex
        flex-column
        flex-md-row
        justify-content-between
        align-items-md-center
        gap-3
        mb-3
    "
>
    <div class="d-flex flex-wrap gap-2">
        <a
            href="{{
                route(
                    'produtos.variacoes.index',
                    [
                        'produto' => $produto,
                        'status' => 'ativos',
                    ]
                )
            }}"
            class="
                btn

                {{
                    $status === 'ativos'
                        ? 'btn-primary'
                        : 'btn-outline-primary'
                }}
            "
        >
            Ativas

            <span
                class="
                    badge
                    bg-light
                    text-dark
                    ms-1
                "
            >
                {{ $quantidadeAtivas }}
            </span>
        </a>

        <a
            href="{{
                route(
                    'produtos.variacoes.index',
                    [
                        'produto' => $produto,
                        'status' => 'inativos',
                    ]
                )
            }}"
            class="
                btn

                {{
                    $status === 'inativos'
                        ? 'btn-secondary'
                        : 'btn-outline-secondary'
                }}
            "
        >
            Inativas

            <span
                class="
                    badge
                    bg-light
                    text-dark
                    ms-1
                "
            >
                {{ $quantidadeInativas }}
            </span>
        </a>
    </div>

    @if($produto->ativo)
        <div>

            @if(
                $status === 'ativos'
                &&
                $quantidadeAtivas > 0
            )

                @if($produtoPossuiOrdemAtiva)

                    <span
                        class="d-inline-block"
                        tabindex="0"
                        title="Inativar todas indisponível — Pelo menos uma variação participa de uma ordem planejada ou em produção."
                    >
                        <button
                            type="button"
                            class="
                                btn
                                btn-outline-secondary
                            "
                            disabled
                        >
                            Inativar todas
                        </button>
                    </span>

                @else

                    <form
                        action="{{
                            route(
                                'produtos.variacoes.inativarTodas',
                                $produto
                            )
                        }}"
                        method="POST"
                        class="d-inline"
                        data-confirm="Deseja inativar todas as variações ativas deste produto?"
                        data-confirm-title="Inativar todas as variações"
                        data-confirm-button="Inativar todas"
                        data-confirm-variant="warning"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="
                                btn
                                btn-outline-secondary
                            "
                        >
                            Inativar todas
                        </button>
                    </form>

                @endif

            @elseif(
                $status === 'inativos'
                &&
                $quantidadeInativas > 0
            )

                <form
                    action="{{
                        route(
                            'produtos.variacoes.reativarTodas',
                            $produto
                        )
                    }}"
                    method="POST"
                    class="d-inline"
                    data-confirm="Deseja reativar todas as variações inativas deste produto?"
                    data-confirm-title="Reativar todas as variações"
                    data-confirm-button="Reativar todas"
                    data-confirm-variant="success"
                >
                    @csrf

                    <button
                        type="submit"
                        class="
                            btn
                            btn-outline-success
                        "
                    >
                        Reativar todas
                    </button>
                </form>

            @endif

        </div>
    @endif
</div>

<div class="card mb-3">
    <div class="card-body">

        <form
            method="GET"
            action="{{
                route(
                    'produtos.variacoes.index',
                    $produto
                )
            }}"
        >
            <input
                type="hidden"
                name="status"
                value="{{ $status }}"
            >

            <div class="row g-3 align-items-end">

                <div
                    class="{{
                        $status === 'ativos'
                            ? 'col-md-7'
                            : 'col-md-9'
                    }}"
                >
                    <label
                        for="q"
                        class="form-label"
                    >
                        Buscar variação
                    </label>

                    <input
                        type="search"
                        name="q"
                        id="q"
                        class="form-control"
                        value="{{ $busca }}"
                        placeholder="Tamanho ou cor"
                    >
                </div>

                @if($status === 'ativos')
                    <div class="col-md-3">
                        <label
                            for="situacao"
                            class="form-label"
                        >
                            Situação do estoque
                        </label>

                        <select
                            name="situacao"
                            id="situacao"
                            class="form-select"
                        >
                            <option value="">
                                Todas
                            </option>

                            <option
                                value="normal"
                                @selected(
                                    $situacao === 'normal'
                                )
                            >
                                Normal
                            </option>

                            <option
                                value="baixo"
                                @selected(
                                    $situacao === 'baixo'
                                )
                            >
                                Estoque baixo
                            </option>

                            <option
                                value="sem_estoque"
                                @selected(
                                    $situacao
                                    === 'sem_estoque'
                                )
                            >
                                Sem estoque
                            </option>
                        </select>
                    </div>
                @endif

                <div class="col-md-auto">
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
                            $situacao !== ''
                        )
                            <a
                                href="{{
                                    route(
                                        'produtos.variacoes.index',
                                        [
                                            'produto' =>
                                                $produto,

                                            'status' =>
                                                $status,
                                        ]
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

<div class="small text-muted mb-2">
    {{ $variacoes->count() }}

    {{
        $variacoes->count() === 1
            ? 'variação encontrada'
            : 'variações encontradas'
    }}
</div>

@if($variacoes->isEmpty())

    <div class="card">
        <div
            class="
                card-body
                text-center
                py-5
            "
        >
            <h2 class="h5 mb-2">
                Nenhuma variação encontrada
            </h2>

            <p class="text-muted mb-3">
                @if(
                    $busca !== ''
                    ||
                    $situacao !== ''
                )
                    Não existem variações
                    correspondentes aos filtros atuais.

                @elseif($status === 'inativos')
                    Este produto não possui
                    variações inativas.

                @else
                    Este produto ainda não possui
                    variações ativas.
                @endif
            </p>

            @if(
                $busca !== ''
                ||
                $situacao !== ''
            )
                <a
                    href="{{
                        route(
                            'produtos.variacoes.index',
                            [
                                'produto' => $produto,
                                'status' => $status,
                            ]
                        )
                    }}"
                    class="btn btn-outline-primary"
                >
                    Limpar filtros
                </a>

            @elseif(
                $status === 'ativos'
                &&
                $produto->ativo
            )

                <a
                    href="{{
                        route(
                            'produtos.variacoes.create',
                            $produto
                        )
                    }}"
                    class="btn btn-primary"
                >
                    Nova variação
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

                        <th class="text-end">
                            Ações
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach(
                        $variacoes
                        as $variacao
                    )

                        @php
                            if (!$variacao->ativo) {
                                $situacaoTexto =
                                    'Inativa';

                                $situacaoClasse =
                                    'bg-secondary-subtle '
                                    . 'text-secondary-emphasis '
                                    . 'border '
                                    . 'border-secondary-subtle';

                            } elseif (
                                (float) $variacao
                                    ->quantidade
                                <= 0
                            ) {
                                $situacaoTexto =
                                    'Sem estoque';

                                $situacaoClasse =
                                    'bg-danger-subtle '
                                    . 'text-danger-emphasis '
                                    . 'border '
                                    . 'border-danger-subtle';

                            } elseif (
                                (float) $variacao
                                    ->quantidade
                                <=
                                (float) $variacao
                                    ->estoque_minimo
                            ) {
                                $situacaoTexto =
                                    'Estoque baixo';

                                $situacaoClasse =
                                    'bg-warning-subtle '
                                    . 'text-warning-emphasis '
                                    . 'border '
                                    . 'border-warning-subtle';

                            } else {
                                $situacaoTexto =
                                    'Normal';

                                $situacaoClasse =
                                    'bg-success-subtle '
                                    . 'text-success-emphasis '
                                    . 'border '
                                    . 'border-success-subtle';
                            }
                        @endphp

                        <tr>
                            <td>
                                <strong>
                                    {{ $variacao->tamanho }}
                                </strong>

                                <br>

                                <small class="text-muted">
                                    {{ $variacao->cor }}
                                </small>
                            </td>

                            <td class="text-nowrap">
                                {{
                                    number_format(
                                        $variacao
                                            ->quantidade,
                                        0,
                                        ',',
                                        '.'
                                    )
                                }}
                                un.
                            </td>

                            <td class="text-nowrap">
                                {{
                                    number_format(
                                        $variacao
                                            ->estoque_minimo,
                                        0,
                                        ',',
                                        '.'
                                    )
                                }}
                                un.
                            </td>

                            <td>
                                <span
                                    class="
                                        badge
                                        {{ $situacaoClasse }}
                                    "
                                >
                                    {{ $situacaoTexto }}
                                </span>
                            </td>

                            <td
                                class="
                                    text-end
                                    text-nowrap
                                "
                            >
                                <a
                                    href="{{
                                        route(
                                            'produtos.variacoes.composicao.index',
                                            [
                                                $produto,
                                                $variacao,
                                            ]
                                        )
                                    }}"
                                    class="
                                        btn
                                        btn-sm
                                        btn-outline-primary
                                    "
                                >
                                    Ficha de consumo
                                </a>

                                <a
                                    href="{{
                                        route(
                                            'produtos.variacoes.edit',
                                            [
                                                $produto,
                                                $variacao,
                                            ]
                                        )
                                    }}"
                                    class="
                                        btn
                                        btn-sm
                                        btn-outline-secondary
                                    "
                                >
                                    Editar
                                </a>

                                @if($variacao->ativo)

                                    @if(
                                        $variacao
                                            ->possui_ordem_ativa
                                    )

                                        <span
                                            class="d-inline-block"
                                            tabindex="0"
                                            title="Inativar indisponível — Esta variação está vinculada a uma ordem planejada ou em produção."
                                        >
                                            <button
                                                type="button"
                                                class="
                                                    btn
                                                    btn-sm
                                                    btn-outline-secondary
                                                "
                                                disabled
                                            >
                                                Inativar
                                            </button>
                                        </span>

                                    @else

                                        <form
                                            action="{{
                                                route(
                                                    'produtos.variacoes.inativar',
                                                    [
                                                        $produto,
                                                        $variacao,
                                                    ]
                                                )
                                            }}"
                                            method="POST"
                                            class="d-inline"
                                            data-confirm="Deseja inativar esta variação? Ela deixará de ficar disponível para novas operações enquanto permanecer inativa."
                                            data-confirm-title="Inativar variação"
                                            data-confirm-button="Inativar"
                                            data-confirm-variant="warning"
                                        >
                                            @csrf

                                            <button
                                                type="submit"
                                                class="
                                                    btn
                                                    btn-sm
                                                    btn-outline-secondary
                                                "
                                            >
                                                Inativar
                                            </button>
                                        </form>

                                    @endif

                                @else

                                    @if($produto->ativo)

                                        <form
                                            action="{{
                                                route(
                                                    'produtos.variacoes.reativar',
                                                    [
                                                        $produto,
                                                        $variacao,
                                                    ]
                                                )
                                            }}"
                                            method="POST"
                                            class="d-inline"
                                            data-confirm="Deseja reativar esta variação? Ela voltará a ficar disponível para uso no sistema."
                                            data-confirm-title="Reativar variação"
                                            data-confirm-button="Reativar"
                                            data-confirm-variant="success"
                                        >
                                            @csrf

                                            <button
                                                type="submit"
                                                class="
                                                    btn
                                                    btn-sm
                                                    btn-outline-success
                                                "
                                            >
                                                Reativar
                                            </button>
                                        </form>

                                    @else

                                        <span
                                            class="d-inline-block"
                                            tabindex="0"
                                            title="Reativar indisponível — Reative primeiro o produto."
                                        >
                                            <button
                                                type="button"
                                                class="
                                                    btn
                                                    btn-sm
                                                    btn-outline-success
                                                "
                                                disabled
                                            >
                                                Reativar
                                            </button>
                                        </span>

                                    @endif

                                @endif

                                @if(
                                    $variacao
                                        ->motivo_bloqueio_exclusao
                                )

                                    <span
                                        class="d-inline-block"
                                        tabindex="0"
                                        title="Excluir indisponível — {{
                                            $variacao
                                                ->motivo_bloqueio_exclusao
                                        }}"
                                    >
                                        <button
                                            type="button"
                                            class="
                                                btn
                                                btn-sm
                                                btn-outline-danger
                                            "
                                            disabled
                                        >
                                            Excluir
                                        </button>
                                    </span>

                                @else

                                    <form
                                        action="{{
                                            route(
                                                'produtos.variacoes.destroy',
                                                [
                                                    $produto,
                                                    $variacao,
                                                ]
                                            )
                                        }}"
                                        method="POST"
                                        class="d-inline"
                                        data-confirm="Deseja realmente excluir definitivamente esta variação? Esta ação não poderá ser desfeita."
                                        data-confirm-title="Excluir variação"
                                        data-confirm-button="Excluir"
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
                                            Excluir
                                        </button>
                                    </form>

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
