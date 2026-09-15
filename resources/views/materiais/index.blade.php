@extends('layouts.app')

@section('title', 'Materiais')

@section('content')

@php
    $formatarQuantidade =
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
            Materiais
        </h1>

        <p class="text-muted mb-0">
            Controle as matérias-primas
            utilizadas na produção.
        </p>
    </div>

    <a
        href="{{ route('materiais.create') }}"
        class="btn btn-primary"
    >
        Novo material
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
                'materiais.index',
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
                'materiais.index',
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
            action="{{ route('materiais.index') }}"
        >
            <input
                type="hidden"
                name="status"
                value="{{ $status }}"
            >

            <div class="row g-3 align-items-end">

                <div class="col-lg-5">
                    <label
                        for="q"
                        class="form-label"
                    >
                        Buscar material
                    </label>

                    <input
                        type="search"
                        name="q"
                        id="q"
                        class="form-control"
                        value="{{ $busca }}"
                        placeholder="Nome, tipo, cor ou fornecedor"
                    >
                </div>

                <div class="col-md-4 col-lg-3">
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
                            as $valor => $nome
                        )
                            <option
                                value="{{ $valor }}"
                                @selected(
                                    $categoria
                                    === $valor
                                )
                            >
                                {{ $nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($status === 'ativos')
                    <div class="col-md-4 col-lg-2">
                        <label
                            for="situacao"
                            class="form-label"
                        >
                            Estoque
                        </label>

                        <select
                            name="situacao"
                            id="situacao"
                            class="form-select"
                        >
                            <option value="">
                                Todos
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
                            $categoria !== ''
                            ||
                            $situacao !== ''
                        )
                            <a
                                href="{{
                                    route(
                                        'materiais.index',
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
    {{ $materiais->count() }}

    {{
        $materiais->count() === 1
            ? 'material encontrado'
            : 'materiais encontrados'
    }}
</div>

@if($materiais->isEmpty())

    <div class="card">
        <div
            class="
                card-body
                text-center
                py-5
            "
        >
            <h2 class="h5 mb-2">
                Nenhum material encontrado
            </h2>

            <p class="text-muted mb-3">
                @if(
                    $busca !== ''
                    ||
                    $categoria !== ''
                    ||
                    $situacao !== ''
                )
                    Não existem materiais
                    correspondentes aos filtros atuais.

                @elseif($status === 'inativos')
                    Não existem materiais inativos.

                @else
                    Ainda não existem materiais
                    ativos cadastrados.
                @endif
            </p>

            @if(
                $busca !== ''
                ||
                $categoria !== ''
                ||
                $situacao !== ''
            )
                <a
                    href="{{
                        route(
                            'materiais.index',
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
                    href="{{
                        route(
                            'materiais.create'
                        )
                    }}"
                    class="btn btn-primary"
                >
                    Novo material
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
                            Material
                        </th>

                        <th>
                            Categoria
                        </th>

                        <th>
                            Estoque
                        </th>

                        <th>
                            Mínimo
                        </th>

                        <th>
                            Fornecedor
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
                        $materiais
                        as $material
                    )

                        @php
                            if (!$material->ativo) {
                                $situacaoTexto =
                                    'Inativo';

                                $situacaoClasse =
                                    'bg-secondary-subtle '
                                    . 'text-secondary-emphasis '
                                    . 'border '
                                    . 'border-secondary-subtle';

                            } elseif (
                                (float) $material->quantidade
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
                                (float) $material
                                    ->estoque_minimo
                                > 0
                                &&
                                (float) $material
                                    ->quantidade
                                <=
                                (float) $material
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

                            $motivoExclusao = null;

                            if (
                                (float)
                                $material->quantidade
                                > 0
                            ) {
                                $motivoExclusao =
                                    'Este material possui estoque disponível.';

                            } elseif (
                                $material
                                    ->movimentacoes_count
                                > 0
                            ) {
                                $motivoExclusao =
                                    'Este material possui movimentações registradas.';

                            } elseif (
                                $material
                                    ->composicoes_count
                                > 0
                            ) {
                                $motivoExclusao =
                                    'Este material é utilizado em uma ficha de consumo.';
                            }
                        @endphp

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

                                    <small class="text-muted">
                                        {{ $material->cor }}
                                    </small>
                                @endif
                            </td>

                            <td>
                                <strong class="fw-medium">
                                    {{
                                        $categorias[
                                            $material
                                                ->categoria
                                        ]
                                        ??
                                        ucfirst(
                                            $material
                                                ->categoria
                                        )
                                    }}
                                </strong>

                                @if(
                                    $material
                                        ->tipo_material
                                )
                                    <br>

                                    <small class="text-muted">
                                        {{
                                            $material
                                                ->tipo_material
                                        }}
                                    </small>
                                @endif
                            </td>

                            <td class="text-nowrap">
                                {{
                                    $formatarQuantidade(
                                        $material
                                            ->quantidade,
                                        $material
                                            ->unidade_medida
                                    )
                                }}
                            </td>

                            <td class="text-nowrap">
                                {{
                                    $formatarQuantidade(
                                        $material
                                            ->estoque_minimo,
                                        $material
                                            ->unidade_medida
                                    )
                                }}
                            </td>

                            <td>
                                @if($material->fornecedor)
                                    {{
                                        $material
                                            ->fornecedor
                                            ->nome_fornecedor
                                    }}
                                @else
                                    <span class="text-muted">
                                        Não definido
                                    </span>
                                @endif
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
                                            'materiais.edit',
                                            $material
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

                                @if($material->ativo)

                                    <form
                                        action="{{
                                            route(
                                                'materiais.inativar',
                                                $material
                                            )
                                        }}"
                                        method="POST"
                                        class="d-inline"
                                        data-confirm="Deseja inativar este material? Ele deixará de estar disponível para novos usos enquanto permanecer inativo."
                                        data-confirm-title="Inativar material"
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

                                @else

                                    <form
                                        action="{{
                                            route(
                                                'materiais.reativar',
                                                $material
                                            )
                                        }}"
                                        method="POST"
                                        class="d-inline"
                                        data-confirm="Deseja reativar este material? Ele voltará a ficar disponível para uso no sistema."
                                        data-confirm-title="Reativar material"
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

                                @if($motivoExclusao)

                                    <span
                                        class="d-inline-block"
                                        tabindex="0"
                                        title="Excluir indisponível — {{ $motivoExclusao }}"
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
                                                'materiais.destroy',
                                                $material
                                            )
                                        }}"
                                        method="POST"
                                        class="d-inline"
                                        data-confirm="Deseja realmente excluir definitivamente este material? Esta ação não poderá ser desfeita."
                                        data-confirm-title="Excluir material"
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
