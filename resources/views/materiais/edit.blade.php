@extends('layouts.app')

@section('title', 'Editar Material')

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

    $unidadeSelecionada =
        old(
            'unidade_medida',
            $material->unidade_medida
        );
@endphp

<div class="mb-4">
    <h1 class="mb-1">
        Editar material
    </h1>

    <p class="text-muted mb-0">
        Atualize os dados cadastrais
        e o nível mínimo deste material.
    </p>
</div>

<div class="row">
    <div class="col-12 col-xxl-10">
        <div class="card">
            <div class="card-body p-4">

                <h2 class="h5 mb-4">
                    Dados do material
                </h2>

                @if($errors->any())
                    <div
                        class="
                            alert
                            alert-danger
                            mb-4
                        "
                    >
                        <strong>
                            Verifique os dados informados.
                        </strong>

                        <ul class="mb-0 mt-2">
                            @foreach(
                                $errors->all()
                                as $erro
                            )
                                <li>
                                    {{ $erro }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form
                    action="{{
                        route(
                            'materiais.update',
                            $material
                        )
                    }}"
                    method="POST"
                >
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label
                            for="nome_material"
                            class="form-label"
                        >
                            Nome
                            <span class="text-danger">
                                *
                            </span>
                        </label>

                        <input
                            type="text"
                            name="nome_material"
                            id="nome_material"
                            class="
                                form-control

                                @error(
                                    'nome_material'
                                )
                                    is-invalid
                                @enderror
                            "
                            value="{{
                                old(
                                    'nome_material',
                                    $material
                                        ->nome_material
                                )
                            }}"
                            maxlength="120"
                            required
                        >

                        @error('nome_material')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-1">

                        <div class="col-md-4">
                            <label
                                for="categoria"
                                class="form-label"
                            >
                                Categoria
                                <span class="text-danger">
                                    *
                                </span>
                            </label>

                            <select
                                name="categoria"
                                id="categoria"
                                class="
                                    form-select

                                    @error('categoria')
                                        is-invalid
                                    @enderror
                                "
                                required
                            >
                                @foreach(
                                    $categorias
                                    as $valor => $nome
                                )
                                    <option
                                        value="{{ $valor }}"
                                        @selected(
                                            old(
                                                'categoria',
                                                $material
                                                    ->categoria
                                            ) === $valor
                                        )
                                    >
                                        {{ $nome }}
                                    </option>
                                @endforeach
                            </select>

                            @error('categoria')
                                <div
                                    class="invalid-feedback"
                                >
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label
                                for="tipo_material"
                                class="form-label"
                            >
                                Tipo / especificação
                            </label>

                            <input
                                type="text"
                                name="tipo_material"
                                id="tipo_material"
                                class="
                                    form-control

                                    @error(
                                        'tipo_material'
                                    )
                                        is-invalid
                                    @enderror
                                "
                                value="{{
                                    old(
                                        'tipo_material',
                                        $material
                                            ->tipo_material
                                    )
                                }}"
                                maxlength="80"
                            >

                            @error('tipo_material')
                                <div
                                    class="invalid-feedback"
                                >
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="form-text">
                                Campo opcional.
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label
                                for="cor"
                                class="form-label"
                            >
                                Cor
                            </label>

                            <input
                                type="text"
                                name="cor"
                                id="cor"
                                class="
                                    form-control

                                    @error('cor')
                                        is-invalid
                                    @enderror
                                "
                                value="{{
                                    old(
                                        'cor',
                                        $material->cor
                                    )
                                }}"
                                maxlength="60"
                            >

                            @error('cor')
                                <div
                                    class="invalid-feedback"
                                >
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="form-text">
                                Campo opcional.
                            </div>
                        </div>

                    </div>

                    <div class="row g-3 mt-1">

                        <div class="col-md-4">
                            <label
                                for="unidade_medida"
                                class="form-label"
                            >
                                Unidade de medida
                                <span class="text-danger">
                                    *
                                </span>
                            </label>

                            @if($unidadeBloqueada)

                                <input
                                    type="hidden"
                                    name="unidade_medida"
                                    value="{{
                                        $material
                                            ->unidade_medida
                                    }}"
                                >

                                <select
                                    id="unidade_medida"
                                    class="form-select"
                                    disabled
                                >
                                    @foreach(
                                        $unidades
                                        as $valor => $nome
                                    )
                                        <option
                                            value="{{ $valor }}"
                                            @selected(
                                                $material
                                                    ->unidade_medida
                                                === $valor
                                            )
                                        >
                                            {{ $nome }}
                                        </option>
                                    @endforeach
                                </select>

                                <div class="form-text">
                                    A unidade não pode ser
                                    alterada porque este material
                                    já possui estoque, movimentações
                                    ou ficha de consumo.
                                </div>

                            @else

                                <select
                                    name="unidade_medida"
                                    id="unidade_medida"
                                    class="
                                        form-select

                                        @error(
                                            'unidade_medida'
                                        )
                                            is-invalid
                                        @enderror
                                    "
                                    required
                                >
                                    @foreach(
                                        $unidades
                                        as $valor => $nome
                                    )
                                        <option
                                            value="{{ $valor }}"
                                            @selected(
                                                old(
                                                    'unidade_medida',
                                                    $material
                                                        ->unidade_medida
                                                ) === $valor
                                            )
                                        >
                                            {{ $nome }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('unidade_medida')
                                    <div
                                        class="invalid-feedback"
                                    >
                                        {{ $message }}
                                    </div>
                                @enderror

                            @endif
                        </div>

                        <div class="col-md-4">
                            <label
                                for="estoque_minimo"
                                class="form-label"
                            >
                                Estoque mínimo
                                <span class="text-danger">
                                    *
                                </span>
                            </label>

                            <input
                                type="number"
                                name="estoque_minimo"
                                id="estoque_minimo"
                                class="
                                    form-control

                                    @error(
                                        'estoque_minimo'
                                    )
                                        is-invalid
                                    @enderror
                                "
                                min="0"
                                step="0.001"
                                value="{{
                                    old(
                                        'estoque_minimo',
                                        (float)
                                        $material
                                            ->estoque_minimo
                                    )
                                }}"
                                required
                            >

                            @error('estoque_minimo')
                                <div
                                    class="invalid-feedback"
                                >
                                    {{ $message }}
                                </div>
                            @enderror

                            <div
                                class="form-text"
                                id="ajudaEstoqueMinimo"
                            ></div>
                        </div>

                        <div class="col-md-4">
                            <label
                                for="id_fornecedor"
                                class="form-label"
                            >
                                Fornecedor
                            </label>

                            <select
                                name="id_fornecedor"
                                id="id_fornecedor"
                                class="
                                    form-select

                                    @error(
                                        'id_fornecedor'
                                    )
                                        is-invalid
                                    @enderror
                                "
                            >
                                <option value="">
                                    Sem fornecedor definido
                                </option>

                                @foreach(
                                    $fornecedores
                                    as $fornecedor
                                )
                                    <option
                                        value="{{
                                            $fornecedor
                                                ->id_fornecedor
                                        }}"
                                        @selected(
                                            (string)
                                            old(
                                                'id_fornecedor',
                                                $material
                                                    ->id_fornecedor
                                            )
                                            ===
                                            (string)
                                            $fornecedor
                                                ->id_fornecedor
                                        )
                                    >
                                        {{
                                            $fornecedor
                                                ->nome_fornecedor
                                        }}
                                    </option>
                                @endforeach
                            </select>

                            @error('id_fornecedor')
                                <div
                                    class="invalid-feedback"
                                >
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="form-text">
                                Campo opcional.
                            </div>
                        </div>

                    </div>

                    <div class="mt-4 mb-4">
                        <label class="form-label">
                            Estoque atual
                        </label>

                        <div
                            class="
                                form-control
                                bg-body-secondary
                            "
                        >
                            {{
                                $formatarQuantidade(
                                    $material
                                        ->quantidade,
                                    $material
                                        ->unidade_medida
                                )
                            }}
                        </div>

                        <div class="form-text">
                            O estoque só pode ser
                            alterado através de movimentações.
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Salvar alterações
                        </button>

                        <a
                            href="{{
                                route(
                                    'materiais.index',
                                    [
                                        'status' =>
                                            $material->ativo
                                                ? 'ativos'
                                                : 'inativos',
                                    ]
                                )
                            }}"
                            class="
                                btn
                                btn-outline-secondary
                            "
                        >
                            Cancelar
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const unidade =
        document.getElementById(
            'unidade_medida'
        );

    const minimo =
        document.getElementById(
            'estoque_minimo'
        );

    const ajuda =
        document.getElementById(
            'ajudaEstoqueMinimo'
        );

    if (!unidade || !minimo) {
        return;
    }

    function atualizarEstoqueMinimo() {
        const discreta = [
            'un',
            'rolo'
        ].includes(
            unidade.value
        );

        minimo.step =
            discreta
                ? '1'
                : '0.001';

        minimo.inputMode =
            discreta
                ? 'numeric'
                : 'decimal';

        if (ajuda) {
            ajuda.textContent =
                discreta
                    ? 'Para unidade e rolo, utilize apenas números inteiros.'
                    : 'Pode possuir até 3 casas decimais.';
        }
    }

    unidade.addEventListener(
        'change',
        atualizarEstoqueMinimo
    );

    atualizarEstoqueMinimo();
})();
</script>

@endsection
