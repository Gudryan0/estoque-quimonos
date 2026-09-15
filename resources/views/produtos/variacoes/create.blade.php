@extends('layouts.app')

@section('title', 'Nova Variação')

@section('content')

<div class="mb-4">
    <h1 class="mb-1">
        Nova variação
    </h1>

    <p class="text-muted mb-0">
        Produto:
        <strong class="text-body">
            {{ $produto->nome_produto }}
        </strong>
    </p>
</div>

<div class="row">
    <div class="col-xl-8 col-xxl-7">
        <div class="card">
            <div class="card-body p-4">

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
                            'produtos.variacoes.store',
                            $produto
                        )
                    }}"
                    method="POST"
                >
                    @csrf

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label
                                for="tamanho"
                                class="form-label"
                            >
                                Tamanho
                                <span class="text-danger">
                                    *
                                </span>
                            </label>

                            <input
                                type="text"
                                name="tamanho"
                                id="tamanho"
                                class="
                                    form-control

                                    @error('tamanho')
                                        is-invalid
                                    @enderror
                                "
                                value="{{
                                    old('tamanho')
                                }}"
                                maxlength="30"
                                placeholder="Ex.: A1, A2, A3"
                                required
                            >

                            @error('tamanho')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label
                                for="cor"
                                class="form-label"
                            >
                                Cor
                                <span class="text-danger">
                                    *
                                </span>
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
                                required
                            >

                            @error('cor')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                    </div>

                    <div class="mt-4 mb-4">
                        <label
                            for="estoque_minimo"
                            class="form-label"
                        >
                            Estoque mínimo
                            <span class="text-danger">
                                *
                            </span>
                        </label>

                        <div class="input-group">
                            <input
                                type="number"
                                name="estoque_minimo"
                                id="estoque_minimo"
                                class="
                                    form-control

                                    @error('estoque_minimo')
                                        is-invalid
                                    @enderror
                                "
                                value="{{
                                    old(
                                        'estoque_minimo',
                                        0
                                    )
                                }}"
                                min="0"
                                step="1"
                                required
                            >

                            <span class="input-group-text">
                                un.
                            </span>

                            @error('estoque_minimo')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-text">
                            O sistema avisará quando o estoque
                            atingir ou ficar abaixo deste valor.
                        </div>
                    </div>

                    <div
                        class="
                            alert
                            bg-primary-subtle
                            text-primary-emphasis
                            border
                            border-primary-subtle
                            mb-4
                        "
                    >
                        O estoque inicial será
                        <strong>0 un.</strong>
                        Entradas e saídas devem ser registradas
                        através de
                        <strong>Movimentações</strong>
                        para preservar o histórico do estoque.
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Cadastrar variação
                        </button>

                        <a
                            href="{{
                                route(
                                    'produtos.variacoes.index',
                                    $produto
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

@endsection
