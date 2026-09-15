@extends('layouts.app')

@section('title', 'Editar Variação')

@section('content')

<div class="mb-4">
    <h1 class="mb-1">
        Editar variação
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
                            'produtos.variacoes.update',
                            [
                                $produto,
                                $variacao,
                            ]
                        )
                    }}"
                    method="POST"
                >
                    @csrf
                    @method('PUT')

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
                                    old(
                                        'tamanho',
                                        $variacao->tamanho
                                    )
                                }}"
                                maxlength="30"
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
                                    old(
                                        'cor',
                                        $variacao->cor
                                    )
                                }}"
                                maxlength="60"
                                required
                            >

                            @error('cor')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                    </div>

                    <div class="row g-3 mt-1">

                        <div class="col-md-6">
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
                                    number_format(
                                        $variacao->quantidade,
                                        0,
                                        ',',
                                        '.'
                                    )
                                }}
                                un.
                            </div>

                            <div class="form-text">
                                Alterado somente por movimentações
                                e conclusão de produção.
                            </div>
                        </div>

                        <div class="col-md-6">
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

                                        @error(
                                            'estoque_minimo'
                                        )
                                            is-invalid
                                        @enderror
                                    "
                                    value="{{
                                        old(
                                            'estoque_minimo',
                                            $variacao
                                                ->estoque_minimo
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
                                    <div
                                        class="invalid-feedback"
                                    >
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-text">
                                Gera alerta quando o estoque
                                atingir ou ficar abaixo
                                deste valor.
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
                        O estoque atual não pode ser alterado
                        diretamente nesta tela.
                        Utilize <strong>Movimentações</strong>
                        para entradas e saídas manuais.
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
                                    'produtos.variacoes.index',
                                    [
                                        'produto' =>
                                            $produto,

                                        'status' =>
                                            $variacao->ativo
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

@endsection
