@extends('layouts.app')

@section('title', 'Nova Ordem de Produção')

@section('content')

<div class="mb-4">
    <h1 class="mb-1">
        Nova ordem de produção
    </h1>

    <p class="text-muted mb-0">
        Cadastre as informações gerais da ordem.
        Os itens serão adicionados na próxima etapa.
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
                    action="{{ route('ordens.store') }}"
                    method="POST"
                >
                    @csrf

                    <div class="mb-4">
                        <label
                            for="id_cliente"
                            class="form-label"
                        >
                            Cliente
                        </label>

                        <select
                            name="id_cliente"
                            id="id_cliente"
                            class="
                                form-select

                                @error('id_cliente')
                                    is-invalid
                                @enderror
                            "
                        >
                            <option value="">
                                Sem cliente definido
                            </option>

                            @foreach(
                                $clientes
                                as $cliente
                            )
                                <option
                                    value="{{
                                        $cliente
                                            ->id_cliente
                                    }}"
                                    @selected(
                                        old('id_cliente')
                                        ==
                                        $cliente
                                            ->id_cliente
                                    )
                                >
                                    {{
                                        $cliente
                                            ->nome_cliente
                                    }}
                                </option>
                            @endforeach
                        </select>

                        @error('id_cliente')
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
                            for="data_prevista"
                            class="form-label"
                        >
                            Data prevista
                        </label>

                        <input
                            type="date"
                            name="data_prevista"
                            id="data_prevista"
                            class="
                                form-control

                                @error('data_prevista')
                                    is-invalid
                                @enderror
                            "
                            value="{{
                                old(
                                    'data_prevista'
                                )
                            }}"
                        >

                        @error('data_prevista')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="form-text">
                            Campo opcional.
                            Quando informada, a data deve ser
                            hoje ou uma data futura.
                        </div>
                    </div>

                    <div class="mb-4">
                        <label
                            for="observacao"
                            class="form-label"
                        >
                            Observações
                        </label>

                        <textarea
                            name="observacao"
                            id="observacao"
                            class="
                                form-control

                                @error('observacao')
                                    is-invalid
                                @enderror
                            "
                            rows="4"
                            maxlength="2000"
                            placeholder="Informações adicionais sobre esta ordem"
                        >{{ old('observacao') }}</textarea>

                        @error('observacao')
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
                            alert
                            bg-primary-subtle
                            text-primary-emphasis
                            border
                            border-primary-subtle
                            mb-4
                        "
                    >
                        Depois de criar a ordem,
                        você poderá adicionar as variações
                        e quantidades que serão produzidas.

                        A produção só será iniciada depois
                        dessa etapa.
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Criar ordem
                        </button>

                        <a
                            href="{{ route('ordens.index') }}"
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
