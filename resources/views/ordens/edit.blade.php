@extends('layouts.app')

@section('title', 'Editar Ordem de Produção')

@section('content')

<div class="mb-4">
    <h1 class="mb-1">
        Editar ordem de produção
    </h1>

    <p class="text-muted mb-0">
        Ordem:
        <strong class="text-body">
            {{ $ordem->codigo }}
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
                            'ordens.update',
                            $ordem
                        )
                    }}"
                    method="POST"
                >
                    @csrf
                    @method('PUT')

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
                                        old(
                                            'id_cliente',
                                            $ordem
                                                ->id_cliente
                                        )
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
                                    'data_prevista',
                                    $ordem
                                        ->data_prevista
                                        ?->format(
                                            'Y-m-d'
                                        )
                                )
                            }}"
                        >

                        @error('data_prevista')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="form-text">
                            Se a previsão atual já estiver
                            vencida, ela pode permanecer
                            inalterada.

                            Ao escolher uma nova data,
                            utilize hoje ou uma data futura.
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
                        >{{
                            old(
                                'observacao',
                                $ordem->observacao
                            )
                        }}</textarea>

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
                            bg-warning-subtle
                            text-warning-emphasis
                            border
                            border-warning-subtle
                            mb-4
                        "
                    >
                        Depois que a produção for iniciada,
                        cliente, previsão e observações
                        cadastrais desta ordem não poderão
                        mais ser alterados.
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
                                    'ordens.show',
                                    $ordem
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
