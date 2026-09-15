@extends('layouts.app')

@section('title', 'Usuários')

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
            Usuários
        </h1>

        <p class="text-muted mb-0">
            Gerencie usuários e níveis
            de acesso ao sistema.
        </p>
    </div>

    <a
        href="{{ route('usuarios.create') }}"
        class="btn btn-primary"
    >
        Novo usuário
    </a>
</div>

<div class="small text-muted mb-2">
    {{ $usuarios->count() }}

    {{
        $usuarios->count() === 1
            ? 'usuário cadastrado'
            : 'usuários cadastrados'
    }}
</div>

@if($usuarios->isEmpty())

    <div class="card">
        <div
            class="
                card-body
                text-center
                py-5
            "
        >
            <h2 class="h5 mb-2">
                Nenhum usuário cadastrado
            </h2>

            <p class="text-muted mb-3">
                Cadastre o primeiro usuário
                para começar.
            </p>

            <a
                href="{{ route('usuarios.create') }}"
                class="btn btn-primary"
            >
                Novo usuário
            </a>
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
                            E-mail
                        </th>

                        <th>
                            Nível de acesso
                        </th>

                        <th class="text-end">
                            Ações
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach(
                        $usuarios
                        as $usuario
                    )
                        <tr>
                            <td>
                                <strong>
                                    {{ $usuario->name }}
                                </strong>

                                @if(
                                    $usuario->id
                                    === auth()->id()
                                )
                                    <span
                                        class="
                                            badge
                                            bg-secondary-subtle
                                            text-secondary-emphasis
                                            border
                                            border-secondary-subtle
                                            ms-1
                                        "
                                    >
                                        Você
                                    </span>
                                @endif
                            </td>

                            <td>
                                <a
                                    href="mailto:{{
                                        $usuario->email
                                    }}"
                                    class="
                                        text-body
                                        text-decoration-none
                                    "
                                >
                                    {{ $usuario->email }}
                                </a>
                            </td>

                            <td>
                                @if(
                                    $usuario->nivel_acesso
                                    === 'administrador'
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
                                        Administrador
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
                                        Funcionário
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
                                            'usuarios.edit',
                                            $usuario
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
                                    $usuario->id
                                    === auth()->id()
                                )

                                    <span
                                        class="d-inline-block"
                                        tabindex="0"
                                        title="Excluir indisponível — Você não pode excluir a conta que está utilizando no momento."
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
                                                'usuarios.destroy',
                                                $usuario
                                            )
                                        }}"
                                        method="POST"
                                        class="d-inline"
                                        data-confirm="Deseja realmente excluir este usuário? Esta ação não poderá ser desfeita."
                                        data-confirm-title="Excluir usuário"
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
