@extends('layouts.app')

@section('title', 'Fornecedores')

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
            Fornecedores
        </h1>

        <p class="text-muted mb-0">
            Gerencie os fornecedores de
            matérias-primas da confecção.
        </p>
    </div>

    <a
        href="{{ route('fornecedores.create') }}"
        class="btn btn-primary"
    >
        Novo fornecedor
    </a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form
            method="GET"
            action="{{ route('fornecedores.index') }}"
            class="
                d-flex
                flex-column
                flex-md-row
                align-items-md-end
                gap-2
            "
        >
            <div class="flex-grow-1">
                <label
                    for="q"
                    class="form-label"
                >
                    Buscar fornecedor
                </label>

                <input
                    type="search"
                    name="q"
                    id="q"
                    class="form-control"
                    value="{{ $busca }}"
                    placeholder="Nome, telefone ou endereço"
                >
            </div>

            <button
                type="submit"
                class="btn btn-primary"
            >
                Buscar
            </button>

            @if($busca !== '')
                <a
                    href="{{
                        route(
                            'fornecedores.index'
                        )
                    }}"
                    class="btn btn-outline-secondary"
                >
                    Limpar
                </a>
            @endif
        </form>
    </div>
</div>

<div
    class="
        d-flex
        justify-content-between
        align-items-center
        mb-2
    "
>
    <div class="small text-muted">
        @if($busca !== '')
            {{ $fornecedores->count() }}

            {{
                $fornecedores->count() === 1
                    ? 'resultado encontrado'
                    : 'resultados encontrados'
            }}
        @else
            {{ $fornecedores->count() }}

            {{
                $fornecedores->count() === 1
                    ? 'fornecedor cadastrado'
                    : 'fornecedores cadastrados'
            }}
        @endif
    </div>
</div>

@if($fornecedores->isEmpty())

    <div class="card">
        <div
            class="
                card-body
                text-center
                py-5
            "
        >
            @if($busca !== '')

                <h2 class="h5 mb-2">
                    Nenhum fornecedor encontrado
                </h2>

                <p class="text-muted mb-3">
                    Não encontramos fornecedores
                    correspondentes à busca
                    "{{ $busca }}".
                </p>

                <a
                    href="{{
                        route(
                            'fornecedores.index'
                        )
                    }}"
                    class="btn btn-outline-primary"
                >
                    Limpar busca
                </a>

            @else

                <h2 class="h5 mb-2">
                    Nenhum fornecedor cadastrado
                </h2>

                <p class="text-muted mb-3">
                    Cadastre o primeiro fornecedor
                    para começar.
                </p>

                <a
                    href="{{
                        route(
                            'fornecedores.create'
                        )
                    }}"
                    class="btn btn-primary"
                >
                    Novo fornecedor
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
                            Nome
                        </th>

                        <th>
                            Telefone
                        </th>

                        <th>
                            Endereço
                        </th>

                        <th class="text-center">
                            Materiais
                        </th>

                        <th class="text-end">
                            Ações
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach(
                        $fornecedores
                        as $fornecedor
                    )
                        <tr>
                            <td>
                                <strong>
                                    {{
                                        $fornecedor
                                            ->nome_fornecedor
                                    }}
                                </strong>
                            </td>

                            <td>
                                @if($fornecedor->telefone)
                                    <a
                                        href="tel:{{
                                            preg_replace(
                                                '/\s+/',
                                                '',
                                                $fornecedor
                                                    ->telefone
                                            )
                                        }}"
                                        class="
                                            text-body
                                            text-decoration-none
                                        "
                                    >
                                        {{
                                            $fornecedor
                                                ->telefone
                                        }}
                                    </a>
                                @else
                                    <span class="text-muted">
                                        Não informado
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if($fornecedor->endereco)
                                    {{
                                        $fornecedor
                                            ->endereco
                                    }}
                                @else
                                    <span class="text-muted">
                                        Não informado
                                    </span>
                                @endif
                            </td>

                            <td class="text-center">
                                @if(
                                    $fornecedor
                                        ->materiais_count
                                    > 0
                                )
                                    <span
                                        class="
                                            badge
                                            text-bg-light
                                            border
                                            text-secondary
                                        "
                                    >
                                        {{
                                            $fornecedor
                                                ->materiais_count
                                        }}
                                    </span>
                                @else
                                    <span class="text-muted">
                                        0
                                    </span>
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
                                            'fornecedores.edit',
                                            $fornecedor
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

                                @if(
                                    $fornecedor
                                        ->materiais_count
                                    > 0
                                )

                                    <span
                                        class="d-inline-block"
                                        tabindex="0"
                                        title="Excluir indisponível — Este fornecedor possui materiais vinculados e precisa ser mantido para preservar esses vínculos."
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
                                                'fornecedores.destroy',
                                                $fornecedor
                                            )
                                        }}"
                                        method="POST"
                                        class="d-inline"
                                        data-confirm="Deseja realmente excluir este fornecedor? Esta ação não poderá ser desfeita."
                                        data-confirm-title="Excluir fornecedor"
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
