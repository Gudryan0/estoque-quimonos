@extends('layouts.app')

@section('title', 'Produtos')

@section('content')

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
            Produtos
        </h1>

        <p class="text-muted mb-0">
            Gerencie os produtos acabados
            e suas respectivas variações.
        </p>
    </div>

    <a
        href="{{ route('produtos.create') }}"
        class="btn btn-primary"
    >
        Novo produto
    </a>
</div>

<div
    class="
        d-flex
        flex-wrap
        gap-2
        mb-3
    "
>
    <a
        href="{{
            route(
                'produtos.index',
                [
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
        Ativos
    </a>

    <a
        href="{{
            route(
                'produtos.index',
                [
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
        Inativos
    </a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form
            method="GET"
            action="{{ route('produtos.index') }}"
        >
            <input
                type="hidden"
                name="status"
                value="{{ $status }}"
            >

            <div class="row g-3 align-items-end">

                <div class="col-md-7">
                    <label
                        for="q"
                        class="form-label"
                    >
                        Buscar produto
                    </label>

                    <input
                        type="search"
                        name="q"
                        id="q"
                        class="form-control"
                        value="{{ $busca }}"
                        placeholder="Nome, categoria ou descrição"
                    >
                </div>

                <div class="col-md-3">
                    <label
                        for="categoria"
                        class="form-label"
                    >
                        Categoria
                    </label>

                    <select
                        name="categoria"
                        id="categoria"
                        class="form-select"
                    >
                        <option value="">
                            Todas
                        </option>

                        @foreach(
                            $categorias
                            as $categoriaDisponivel
                        )
                            <option
                                value="{{ $categoriaDisponivel }}"
                                @selected(
                                    $categoria
                                    === $categoriaDisponivel
                                )
                            >
                                {{ $categoriaDisponivel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
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
                            $categoria !== ''
                        )
                            <a
                                href="{{
                                    route(
                                        'produtos.index',
                                        [
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
    {{ $produtos->count() }}

    {{
        $produtos->count() === 1
            ? 'produto encontrado'
            : 'produtos encontrados'
    }}
</div>

@if($produtos->isEmpty())

    <div class="card">
        <div
            class="
                card-body
                text-center
                py-5
            "
        >
            <h2 class="h5 mb-2">
                Nenhum produto encontrado
            </h2>

            <p class="text-muted mb-3">
                @if(
                    $busca !== ''
                    ||
                    $categoria !== ''
                )
                    Não existem produtos
                    correspondentes aos filtros atuais.

                @elseif($status === 'inativos')
                    Não existem produtos inativos.

                @else
                    Ainda não existem produtos
                    ativos cadastrados.
                @endif
            </p>

            @if(
                $busca !== ''
                ||
                $categoria !== ''
            )
                <a
                    href="{{
                        route(
                            'produtos.index',
                            [
                                'status' => $status,
                            ]
                        )
                    }}"
                    class="btn btn-outline-primary"
                >
                    Limpar filtros
                </a>

            @elseif($status === 'ativos')

                <a
                    href="{{ route('produtos.create') }}"
                    class="btn btn-primary"
                >
                    Novo produto
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
                            Produto
                        </th>

                        <th>
                            Categoria
                        </th>

                        <th>
                            Descrição
                        </th>

                        <th>
                            Variações
                        </th>

                        <th class="text-end">
                            Ações
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach(
                        $produtos
                        as $produto
                    )
                        <tr>
                            <td>
                                <strong>
                                    {{
                                        $produto
                                            ->nome_produto
                                    }}
                                </strong>
                            </td>

                            <td>
                                <span
                                    class="
                                        badge
                                        bg-primary-subtle
                                        text-primary-emphasis
                                        border
                                        border-primary-subtle
                                    "
                                >
                                    {{ $produto->categoria }}
                                </span>
                            </td>

                            <td>
                                @if($produto->descricao)
                                    <span
                                        title="{{
                                            $produto
                                                ->descricao
                                        }}"
                                    >
                                        {{
                                            \Illuminate\Support\Str::limit(
                                                $produto
                                                    ->descricao,
                                                90
                                            )
                                        }}
                                    </span>
                                @else
                                    <span class="text-muted">
                                        Sem descrição
                                    </span>
                                @endif
                            </td>

                            <td class="text-nowrap">
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
                                        $produto
                                            ->variacoes_count
                                    }}
                                </span>

                                @if(
                                    $produto
                                        ->variacoes_count
                                    > 0
                                )
                                    <small
                                        class="
                                            text-muted
                                            ms-1
                                        "
                                    >
                                        {{
                                            $produto
                                                ->variacoes_ativas_count
                                        }}
                                        ativas
                                    </small>
                                @endif
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
                                            'produtos.variacoes.index',
                                            [
                                                'produto' =>
                                                    $produto,

                                                'status' =>
                                                    $produto->ativo
                                                        ? 'ativos'
                                                        : 'inativos',
                                            ]
                                        )
                                    }}"
                                    class="
                                        btn
                                        btn-sm
                                        btn-outline-primary
                                    "
                                >
                                    Variações
                                </a>

                                <a
                                    href="{{
                                        route(
                                            'produtos.edit',
                                            $produto
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

                                @if($produto->ativo)

                                    @if(
                                        $produto
                                            ->possui_ordem_producao_ativa
                                    )

                                        <span
                                            class="d-inline-block"
                                            tabindex="0"
                                            title="Inativar indisponível — Este produto possui uma variação vinculada a uma ordem planejada ou em produção."
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
                                                    'produtos.inativar',
                                                    $produto
                                                )
                                            }}"
                                            method="POST"
                                            class="d-inline"
                                            data-confirm="Deseja inativar este produto? Todas as variações ativas também serão inativadas."
                                            data-confirm-title="Inativar produto"
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

                                    <form
                                        action="{{
                                            route(
                                                'produtos.reativar',
                                                $produto
                                            )
                                        }}"
                                        method="POST"
                                        class="d-inline"
                                        data-confirm="Deseja reativar este produto? As variações continuarão inativas até que sejam reativadas individualmente ou em conjunto."
                                        data-confirm-title="Reativar produto"
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

                                @endif

                                @if(
                                    $produto
                                        ->variacoes_count
                                    > 0
                                )

                                    <span
                                        class="d-inline-block"
                                        tabindex="0"
                                        title="Excluir indisponível — Produtos com variações cadastradas não podem ser excluídos."
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
                                                'produtos.destroy',
                                                $produto
                                            )
                                        }}"
                                        method="POST"
                                        class="d-inline"
                                        data-confirm="Deseja realmente excluir definitivamente este produto? Esta ação não poderá ser desfeita."
                                        data-confirm-title="Excluir produto"
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
