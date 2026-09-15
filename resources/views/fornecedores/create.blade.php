@extends('layouts.app')

@section('title', 'Novo Fornecedor')

@section('content')

<div class="mb-4">
    <h1 class="mb-1">
        Novo fornecedor
    </h1>

    <p class="text-muted mb-0">
        Cadastre os dados de um novo
        fornecedor de materiais.
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
                            'fornecedores.store'
                        )
                    }}"
                    method="POST"
                >
                    @csrf

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
                                    'nome_fornecedor'
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
                                old('telefone')
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
                                old('endereco')
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
                            Cadastrar fornecedor
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
