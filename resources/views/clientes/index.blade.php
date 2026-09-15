@extends('layouts.app')

@section('title', 'Clientes')

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
            Clientes
        </h1>

        <p class="text-muted mb-0">
            Gerencie os clientes vinculados
            às ordens de produção.
        </p>
    </div>

    <a
        href="{{ route('clientes.create') }}"
        class="btn btn-primary"
    >
        Novo cliente
    </a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form
            method="GET"
            action="{{ route('clientes.index') }}"
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
                    Buscar cliente
                </label>

                <input
                    type="search"
                    name="q"
                    id="q"
                    class="form-control"
                    value="{{ $busca }}"
                    placeholder="Nome, telefone, e-mail ou endereço"
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
                    href="{{ route('clientes.index') }}"
                    class="btn btn-outline-secondary"
                >
                    Limpar
                </a>
            @endif
        </form>
    </div>
</div>

<div class="small text-muted mb-2">
    @if($busca !== '')
        {{ $clientes->count() }}

        {{
            $clientes->count() === 1
                ? 'resultado encontrado'
                : 'resultados encontrados'
        }}
    @else
        {{ $clientes->count() }}

        {{
            $clientes->count() === 1
                ? 'cliente cadastrado'
                : 'clientes cadastrados'
        }}
    @endif
</div>

@if($clientes->isEmpty())

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
                    Nenhum cliente encontrado
                </h2>

                <p class="text-muted mb-3">
                    Não encontramos clientes
                    correspondentes à busca
                    "{{ $busca }}".
                </p>

                <a
                    href="{{ route('clientes.index') }}"
                    class="btn btn-outline-primary"
                >
                    Limpar busca
                </a>

            @else

                <h2 class="h5 mb-2">
                    Nenhum cliente cadastrado
                </h2>

                <p class="text-muted mb-3">
                    Cadastre o primeiro cliente
                    para começar.
                </p>

                <a
                    href="{{ route('clientes.create') }}"
                    class="btn btn-primary"
                >
                    Novo cliente
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
                            Cliente
                        </th>

                        <th>
                            Contato
                        </th>

                        <th>
                            Endereço
                        </th>

                        <th class="text-center">
                            Ordens
                        </th>

                        <th class="text-end">
                            Ações
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach(
                        $clientes
                        as $cliente
                    )
                        <tr>
                            <td>
                                <strong>
                                    {{
                                        $cliente
                                            ->nome_cliente
                                    }}
                                </strong>
                            </td>

                            <td>
                                @if($cliente->telefone)
                                    <a
                                        href="tel:{{
                                            preg_replace(
                                                '/[^0-9+]/',
                                                '',
                                                $cliente
                                                    ->telefone
                                            )
                                        }}"
                                        class="
                                            text-body
                                            text-decoration-none
                                        "
                                    >
                                        {{
                                            $cliente
                                                ->telefone
                                        }}
                                    </a>
                                @else
                                    <span class="text-muted">
                                        Telefone não informado
                                    </span>
                                @endif

                                <br>

                                @if($cliente->email)
                                    <a
                                        href="mailto:{{
                                            $cliente->email
                                        }}"
                                        class="
                                            text-primary
                                            text-decoration-none
                                            small
                                        "
                                    >
                                        {{ $cliente->email }}
                                    </a>
                                @else
                                    <small class="text-muted">
                                        E-mail não informado
                                    </small>
                                @endif
                            </td>

                            <td>
                                @if($cliente->endereco)
                                    {{ $cliente->endereco }}
                                @else
                                    <span class="text-muted">
                                        Não informado
                                    </span>
                                @endif
                            </td>

                            <td class="text-center">
                                @if(
                                    $cliente
                                        ->ordens_producao_count
                                    > 0
                                )
                                    <span
                                        class="
                                            badge
                                            bg-primary-subtle
                                            text-primary-emphasis
                                            border
                                            border-primary-subtle
                                        "
                                    >
                                        {{
                                            $cliente
                                                ->ordens_producao_count
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
                                            'clientes.edit',
                                            $cliente
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
                                    $cliente
                                        ->ordens_producao_count
                                    > 0
                                )

                                    <span
                                        class="d-inline-block"
                                        tabindex="0"
                                        title="Excluir indisponível — Este cliente possui ordens de produção vinculadas e precisa ser mantido para preservar o histórico."
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
                                                'clientes.destroy',
                                                $cliente
                                            )
                                        }}"
                                        method="POST"
                                        class="d-inline"
                                        data-confirm="Deseja realmente excluir este cliente? Esta ação não poderá ser desfeita."
                                        data-confirm-title="Excluir cliente"
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
