@extends('layouts.app')

@section('title', 'Editar Produto')

@section('content')

<div class="mb-4">
    <h1 class="mb-1">
        Editar produto
    </h1>

    <p class="text-muted mb-0">
        Atualize as informações gerais
        deste produto.
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
                            'produtos.update',
                            $produto
                        )
                    }}"
                    method="POST"
                >
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label
                            for="nome_produto"
                            class="form-label"
                        >
                            Nome
                            <span class="text-danger">
                                *
                            </span>
                        </label>

                        <input
                            type="text"
                            name="nome_produto"
                            id="nome_produto"
                            class="
                                form-control

                                @error('nome_produto')
                                    is-invalid
                                @enderror
                            "
                            value="{{
                                old(
                                    'nome_produto',
                                    $produto->nome_produto
                                )
                            }}"
                            maxlength="120"
                            required
                        >

                        @error('nome_produto')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label
                            for="categoria"
                            class="form-label"
                        >
                            Categoria
                            <span class="text-danger">
                                *
                            </span>
                        </label>

                        <input
                            type="text"
                            name="categoria"
                            id="categoria"
                            class="
                                form-control

                                @error('categoria')
                                    is-invalid
                                @enderror
                            "
                            value="{{
                                old(
                                    'categoria',
                                    $produto->categoria
                                )
                            }}"
                            maxlength="80"
                            list="categoriasExistentes"
                            required
                        >

                        <datalist id="categoriasExistentes">
                            @foreach(
                                $categorias
                                as $categoria
                            )
                                <option
                                    value="{{ $categoria }}"
                                ></option>
                            @endforeach
                        </datalist>

                        @error('categoria')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="form-text">
                            Você pode selecionar uma categoria
                            existente ou informar uma nova.
                        </div>
                    </div>

                    <div class="mb-4">
                        <label
                            for="descricao"
                            class="form-label"
                        >
                            Descrição
                        </label>

                        <textarea
                            name="descricao"
                            id="descricao"
                            class="
                                form-control

                                @error('descricao')
                                    is-invalid
                                @enderror
                            "
                            rows="4"
                            maxlength="2000"
                            placeholder="Informações adicionais sobre o produto"
                        >{{
                            old(
                                'descricao',
                                $produto->descricao
                            )
                        }}</textarea>

                        @error('descricao')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="form-text">
                            Campo opcional.
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
                                    'produtos.index',
                                    [
                                        'status' =>
                                            $produto->ativo
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
