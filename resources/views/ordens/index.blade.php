@extends('layouts.app')

@section('title', 'Ordens de Produção')

@section('content')

@php
    $statusConfig = [
        'planejada' => [
            'texto' => 'Planejada',
            'classe' =>
                'bg-secondary-subtle '
                . 'text-secondary-emphasis '
                . 'border '
                . 'border-secondary-subtle',
        ],

        'em_producao' => [
            'texto' => 'Em produção',
            'classe' =>
                'bg-primary-subtle '
                . 'text-primary-emphasis '
                . 'border '
                . 'border-primary-subtle',
        ],

        'concluida' => [
            'texto' => 'Concluída',
            'classe' =>
                'bg-success-subtle '
                . 'text-success-emphasis '
                . 'border '
                . 'border-success-subtle',
        ],

        'cancelada' => [
            'texto' => 'Cancelada',
            'classe' =>
                'bg-danger-subtle '
                . 'text-danger-emphasis '
                . 'border '
                . 'border-danger-subtle',
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
            Ordens de Produção
        </h1>

        <p class="text-muted mb-0">
            Planeje e acompanhe o andamento
            da produção.
        </p>
    </div>

    <a
        href="{{ route('ordens.create') }}"
        class="btn btn-primary"
    >
        Nova ordem
    </a>
</div>

{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body">
        <form
            method="GET"
            action="{{ route('ordens.index') }}"
        >
            <div class="row g-3 align-items-end">

                <div class="col-lg-5">
                    <label
                        for="q"
                        class="form-label"
                    >
                        Buscar ordem
                    </label>

                    <input
                        type="search"
                        name="q"
                        id="q"
                        class="form-control"
                        value="{{ $busca }}"
                        placeholder="Código, cliente ou responsável"
                    >
                </div>

                <div class="col-md-6 col-lg-3">
                    <label
                        for="status"
                        class="form-label"
                    >
                        Status
                    </label>

                    <select
                        name="status"
                        id="status"
                        class="form-select"
                    >
                        <option value="">
                            Todos
                        </option>

                        <option
                            value="planejada"
                            @selected(
                                $status
                                === 'planejada'
                            )
                        >
                            Planejada
                        </option>

                        <option
                            value="em_producao"
                            @selected(
                                $status
                                === 'em_producao'
                            )
                        >
                            Em produção
                        </option>

                        <option
                            value="concluida"
                            @selected(
                                $status
                                === 'concluida'
                            )
                        >
                            Concluída
                        </option>

                        <option
                            value="cancelada"
                            @selected(
                                $status
                                === 'cancelada'
                            )
                        >
                            Cancelada
                        </option>
                    </select>
                </div>

                <div class="col-md-6 col-lg-2">
                    <label
                        for="prazo"
                        class="form-label"
                    >
                        Prazo
                    </label>

                    <select
                        name="prazo"
                        id="prazo"
                        class="form-select"
                    >
                        <option value="">
                            Todos
                        </option>

                        <option
                            value="atrasadas"
                            @selected(
                                $prazo
                                === 'atrasadas'
                            )
                        >
                            Atrasadas
                        </option>

                        <option
                            value="hoje"
                            @selected(
                                $prazo === 'hoje'
                            )
                        >
                            Para hoje
                        </option>

                        <option
                            value="futuras"
                            @selected(
                                $prazo
                                === 'futuras'
                            )
                        >
                            Futuras
                        </option>

                        <option
                            value="sem_previsao"
                            @selected(
                                $prazo
                                === 'sem_previsao'
                            )
                        >
                            Sem previsão
                        </option>
                    </select>
                </div>

                <div class="col-lg-2">
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
                            $status !== ''
                            ||
                            $prazo !== ''
                        )
                            <a
                                href="{{
                                    route(
                                        'ordens.index'
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
<div class="small text-muted mb-2">
    @if($ordens->total() > 0)
        Exibindo
        {{ $ordens->firstItem() }}
        a
        {{ $ordens->lastItem() }}
        de
        {{ $ordens->total() }}

        {{
            $ordens->total() === 1
                ? 'ordem'
                : 'ordens'
        }}.
    @else
        Nenhuma ordem encontrada.
    @endif
</div>

@if($ordens->isEmpty())

    <div class="card">
        <div
            class="
                card-body
                text-center
                py-5
            "
        >
            <h2 class="h5 mb-2">
                Nenhuma ordem encontrada
            </h2>

            <p class="text-muted mb-3">
                @if(
                    $busca !== ''
                    ||
                    $status !== ''
                    ||
                    $prazo !== ''
                )
                    Não existem ordens correspondentes
                    aos filtros atuais.
                @else
                    Ainda não existem ordens
                    de produção cadastradas.
                @endif
            </p>

            @if(
                $busca !== ''
                ||
                $status !== ''
                ||
                $prazo !== ''
            )
                <a
                    href="{{ route('ordens.index') }}"
                    class="btn btn-outline-primary"
                >
                    Limpar filtros
                </a>
            @else
                <a
                    href="{{ route('ordens.create') }}"
                    class="btn btn-primary"
                >
                    Nova ordem
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
                            Ordem
                        </th>

                        <th>
                            Cliente
                        </th>

                        <th>
                            Status
                        </th>

                        <th class="text-center">
                            Itens
                        </th>

                        <th>
                            Previsão
                        </th>

                        <th>
                            Criada por
                        </th>

                        <th class="text-end">
                            Ações
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach(
                        $ordens
                        as $ordem
                    )
                        @php
                            $config =
                                $statusConfig[
                                    $ordem->status
                                ]
                                ??
                                $statusConfig[
                                    'planejada'
                                ];

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
                                @if($ordem->cliente_nome)
                                    {{
                                        $ordem
                                            ->cliente_nome
                                    }}
                                @else
                                    <span class="text-muted">
                                        Sem cliente
                                    </span>
                                @endif
                            </td>

                            <td>
                                <span
                                    class="
                                        badge
                                        {{
                                            $config[
                                                'classe'
                                            ]
                                        }}
                                    "
                                >
                                    {{
                                        $config[
                                            'texto'
                                        ]
                                    }}
                                </span>
                            </td>

                            <td class="text-center">
                                <span
                                    class="
                                        badge
                                        bg-secondary-subtle
                                        text-secondary-emphasis
                                        border
                                        border-secondary-subtle
                                    "
                                >
                                    {{ $ordem->itens_count }}
                                </span>
                            </td>

                            <td class="text-nowrap">
                                @if($ordem->data_prevista)

                                    <span
                                        class="{{
                                            $atrasada
                                                ? 'text-danger fw-semibold'
                                                : ''
                                        }}"
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

                                        <span
                                            class="
                                                badge
                                                bg-danger-subtle
                                                text-danger-emphasis
                                                border
                                                border-danger-subtle
                                                mt-1
                                            "
                                        >
                                            Atrasada
                                        </span>
                                    @endif

                                @else
                                    <span class="text-muted">
                                        Sem previsão
                                    </span>
                                @endif
                            </td>

                            <td>
                                {{
                                    $ordem
                                        ->criador
                                        ?->name
                                    ??
                                    'Não disponível'
                                }}
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
                                            'ordens.show',
                                            $ordem
                                        )
                                    }}"
                                    class="
                                        btn
                                        btn-sm
                                        btn-outline-primary
                                    "
                                >
                                    Abrir
                                </a>

                                @if(
                                    $ordem->status
                                    === 'planejada'
                                )
                                    <a
                                        href="{{
                                            route(
                                                'ordens.edit',
                                                $ordem
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
                                @else
                                    <span
                                        class="d-inline-block"
                                        tabindex="0"
                                        title="Somente ordens planejadas podem ser editadas."
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
                                            Editar
                                        </button>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($ordens->lastPage() > 1)
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
                {{ $ordens->currentPage() }}
                de
                {{ $ordens->lastPage() }}
            </div>

            <div class="d-flex gap-2">
                @if($ordens->onFirstPage())
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
                            $ordens
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

                @if($ordens->hasMorePages())
                    <a
                        href="{{
                            $ordens
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
