@extends('layouts.app')

@section('title', 'Novo Material')

@section('content')

<div class="mb-4">
    <h1 class="mb-1">
        Novo material
    </h1>

    <p class="text-muted mb-0">
        Cadastre uma matéria-prima
        utilizada na produção.
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
                            'materiais.store'
                        )
                    }}"
                    method="POST"
                >
                    @csrf

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
                                    'nome_material'
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
                                <option value="">
                                    Selecione
                                </option>

                                @foreach(
                                    $categorias
                                    as $valor => $nome
                                )
                                    <option
                                        value="{{ $valor }}"
                                        @selected(
                                            old(
                                                'categoria'
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
                                        'tipo_material'
                                    )
                                }}"
                                maxlength="80"
                                placeholder="Ex.: Brim"
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
                                    old('cor')
                                }}"
                                maxlength="60"
                                placeholder="Ex.: Branco"
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
                                <option value="">
                                    Selecione
                                </option>

                                @foreach(
                                    $unidades
                                    as $valor => $nome
                                )
                                    <option
                                        value="{{ $valor }}"
                                        @selected(
                                            old(
                                                'unidade_medida'
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
                                        0
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
                            >
                                Informe o nível que
                                deve gerar alerta.
                            </div>
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
                                                'id_fornecedor'
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

                    <div
                        class="
                            alert
                            bg-primary-subtle
                            text-primary-emphasis
                            border
                            border-primary-subtle
                            mt-4
                            mb-4
                        "
                    >
                        O estoque inicial será
                        <strong>zero</strong>.
                        Utilize a tela de
                        <strong>Movimentações</strong>
                        para registrar a primeira
                        entrada deste material.
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Cadastrar material
                        </button>

                        <a
                            href="{{
                                route(
                                    'materiais.index'
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
