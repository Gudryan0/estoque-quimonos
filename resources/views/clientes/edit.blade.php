@extends('layouts.app')

@section('title', 'Editar Cliente')

@section('content')

<div class="mb-4">
    <h1 class="mb-1">
        Editar cliente
    </h1>

    <p class="text-muted mb-0">
        Atualize os dados cadastrais
        deste cliente.
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
                            'clientes.update',
                            $cliente
                        )
                    }}"
                    method="POST"
                >
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label
                            for="nome_cliente"
                            class="form-label"
                        >
                            Nome
                            <span class="text-danger">
                                *
                            </span>
                        </label>

                        <input
                            type="text"
                            name="nome_cliente"
                            id="nome_cliente"
                            class="
                                form-control

                                @error('nome_cliente')
                                    is-invalid
                                @enderror
                            "
                            value="{{
                                old(
                                    'nome_cliente',
                                    $cliente->nome_cliente
                                )
                            }}"
                            maxlength="120"
                            autocomplete="organization"
                            required
                        >

                        @error('nome_cliente')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="row g-3">

                        <div class="col-md-6">
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
                                        $cliente->telefone
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

                        <div class="col-md-6">
                            <label
                                for="email"
                                class="form-label"
                            >
                                E-mail
                            </label>

                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="
                                    form-control

                                    @error('email')
                                        is-invalid
                                    @enderror
                                "
                                value="{{
                                    old(
                                        'email',
                                        $cliente->email
                                    )
                                }}"
                                maxlength="255"
                                autocomplete="email"
                                placeholder="Ex.: contato@cliente.com"
                            >

                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="form-text">
                                Campo opcional.
                            </div>
                        </div>

                    </div>

                    <div class="mt-4 mb-4">
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
                                    $cliente->endereco
                                )
                            }}"
                            maxlength="255"
                            autocomplete="street-address"
                            placeholder="Ex.: Rua Exemplo, 100"
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

                    <div class="d-flex flex-wrap gap-2">
                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Salvar alterações
                        </button>

                        <a
                            href="{{ route('clientes.index') }}"
                            class="btn btn-outline-secondary"
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
