@extends('layouts.app')

@section('title', 'Editar Fornecedor')

@section('content')

<div class="mb-4">
    <h1 class="mb-1">
        Editar fornecedor
    </h1>

    <p class="text-muted mb-0">
        Atualize os dados cadastrais
        deste fornecedor.
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
                            'fornecedores.update',
                            $fornecedor
                        )
                    }}"
                    method="POST"
                >
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label
                            for="nome_fornecedor"
                            class="form-label"
                        >
                            Nome
                            <span class="text-danger">
                                *
                            </span>
                        </label>

                        <input
                            type="text"
                            name="nome_fornecedor"
                            id="nome_fornecedor"
                            class="
                                form-control

                                @error(
                                    'nome_fornecedor'
                                )
                                    is-invalid
                                @enderror
                            "
                            value="{{
                                old(
                                    'nome_fornecedor',
                                    $fornecedor
                                        ->nome_fornecedor
                                )
                            }}"
                            maxlength="120"
                            autocomplete="organization"
                            required
                        >

                        @error('nome_fornecedor')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label
                            for="telefone"
                            class="form-label"
                        >
                            Telefone
                        </label>

                        <input
                            type="text"
                            name="telefone"
                            id="telefone"
                            class="
                                form-control

                                @error('telefone')
                                    is-invalid
                                @enderror
                            "
                            value="{{
                                old(
                                    'telefone',
                                    $fornecedor->telefone
                                )
                            }}"
                            maxlength="30"
                            inputmode="tel"
                            autocomplete="tel"
                            placeholder="Ex.: (19) 99999-9999"
                        >

                        @error('telefone')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="form-text">
                            Campo opcional.
                        </div>
                    </div>

                    <div class="mb-4">
                        <label
                            for="endereco"
                            class="form-label"
                        >
                            Endereço
                        </label>

                        <input
                            type="text"
                            name="endereco"
                            id="endereco"
                            class="
                                form-control

                                @error('endereco')
                                    is-invalid
                                @enderror
                            "
                            value="{{
                                old(
                                    'endereco',
                                    $fornecedor->endereco
                                )
                            }}"
                            maxlength="255"
                            autocomplete="street-address"
                            placeholder="Ex.: Rua Exemplo, 123"
                        >

                        @error('endereco')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="form-text">
                            Campo opcional.
                        </div>
                    </div>

                    <div
                        class="
                            d-flex
                            flex-wrap
                            gap-2
                            pt-2
                        "
                    >
                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Salvar alterações
                        </button>

                        <a
                            href="{{
                                route(
                                    'fornecedores.index'
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
