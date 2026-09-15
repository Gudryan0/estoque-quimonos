@extends('layouts.app')

@section('title', 'Editar Item')

@section('content')

<div class="mb-4">
    <h1 class="mb-1">
        Editar item
    </h1>

    <p class="text-muted mb-0">
        Ordem:
        <strong class="text-body">
            {{ $ordem->codigo }}
        </strong>
    </p>
</div>

<div class="row">
    <div class="col-xl-9 col-xxl-8">
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
                            'ordens.itens.update',
                            [
                                $ordem,
                                $item,
                            ]
                        )
                    }}"
                    method="POST"
                >
                    @csrf
                    @method('PUT')

                    {{-- Produto --}}
                    <div class="mb-4">
                        <label
                            for="id_variacao"
                            class="form-label"
                        >
                            Produto / Variação
                            <span class="text-danger">
                                *
                            </span>
                        </label>

                        <select
                            name="id_variacao"
                            id="id_variacao"
                            class="
                                form-select

                                @error('id_variacao')
                                    is-invalid
                                @enderror
                            "
                            required
                        >
                            @foreach(
                                $variacoes
                                as $variacao
                            )
                                <option
                                    value="{{
                                        $variacao
                                            ->id_variacao
                                    }}"
                                    @selected(
                                        old(
                                            'id_variacao',
                                            $item
                                                ->id_variacao
                                        )
                                        ==
                                        $variacao
                                            ->id_variacao
                                    )
                                >
                                    {{
                                        $variacao
                                            ->produto
                                            ->nome_produto
                                    }}

                                    ·

                                    {{ $variacao->tamanho }}

                                    /

                                    {{ $variacao->cor }}
                                </option>
                            @endforeach
                        </select>

                        @error('id_variacao')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="form-text">
                            O produto e a variação precisam
                            continuar ativos.
                        </div>
                    </div>

                    {{-- Quantidade --}}
                    <div class="mb-4">
                        <label
                            for="quantidade"
                            class="form-label"
                        >
                            Quantidade
                            <span class="text-danger">
                                *
                            </span>
                        </label>

                        <div class="input-group">
                            <input
                                type="number"
                                name="quantidade"
                                id="quantidade"
                                class="
                                    form-control

                                    @error('quantidade')
                                        is-invalid
                                    @enderror
                                "
                                value="{{
                                    old(
                                        'quantidade',
                                        $item->quantidade
                                    )
                                }}"
                                min="1"
                                max="1000000"
                                step="1"
                                required
                            >

                            <span class="input-group-text">
                                un.
                            </span>

                            @error('quantidade')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="mb-4">
                        <h2 class="h5 mb-1">
                            Especificações da produção
                        </h2>

                        <p class="text-muted small mb-0">
                            Ajuste as instruções deste item
                            antes de iniciar a produção.
                        </p>
                    </div>

                    {{-- Bordado --}}
                    <div class="mb-4">
                        <label
                            for="descricao_bordado"
                            class="form-label"
                        >
                            Bordado
                        </label>

                        <textarea
                            name="descricao_bordado"
                            id="descricao_bordado"
                            class="
                                form-control

                                @error('descricao_bordado')
                                    is-invalid
                                @enderror
                            "
                            rows="3"
                            maxlength="2000"
                        >{{
                            old(
                                'descricao_bordado',
                                $item
                                    ->descricao_bordado
                            )
                        }}</textarea>

                        @error('descricao_bordado')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label
                                for="cor_linha"
                                class="form-label"
                            >
                                Cor da linha
                            </label>

                            <input
                                type="text"
                                name="cor_linha"
                                id="cor_linha"
                                class="
                                    form-control

                                    @error('cor_linha')
                                        is-invalid
                                    @enderror
                                "
                                value="{{
                                    old(
                                        'cor_linha',
                                        $item->cor_linha
                                    )
                                }}"
                                maxlength="255"
                            >

                            @error('cor_linha')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label
                                for="responsavel_costura"
                                class="form-label"
                            >
                                Responsável pela costura
                            </label>

                            <input
                                type="text"
                                name="responsavel_costura"
                                id="responsavel_costura"
                                class="
                                    form-control

                                    @error(
                                        'responsavel_costura'
                                    )
                                        is-invalid
                                    @enderror
                                "
                                value="{{
                                    old(
                                        'responsavel_costura',
                                        $item
                                            ->responsavel_costura
                                    )
                                }}"
                                maxlength="255"
                            >

                            @error('responsavel_costura')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                    </div>

                    <div class="row g-3 mt-1">

                        <div class="col-md-6">
                            <label
                                for="etiqueta"
                                class="form-label"
                            >
                                Etiqueta / Estampa
                            </label>

                            <input
                                type="text"
                                name="etiqueta"
                                id="etiqueta"
                                class="
                                    form-control

                                    @error('etiqueta')
                                        is-invalid
                                    @enderror
                                "
                                value="{{
                                    old(
                                        'etiqueta',
                                        $item->etiqueta
                                    )
                                }}"
                                maxlength="255"
                            >

                            @error('etiqueta')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label
                                for="posicao_etiqueta"
                                class="form-label"
                            >
                                Posição da etiqueta / estampa
                            </label>

                            <input
                                type="text"
                                name="posicao_etiqueta"
                                id="posicao_etiqueta"
                                class="
                                    form-control

                                    @error(
                                        'posicao_etiqueta'
                                    )
                                        is-invalid
                                    @enderror
                                "
                                value="{{
                                    old(
                                        'posicao_etiqueta',
                                        $item
                                            ->posicao_etiqueta
                                    )
                                }}"
                                maxlength="255"
                            >

                            @error('posicao_etiqueta')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                    </div>

                    {{-- Observação --}}
                    <div class="mt-4 mb-4">
                        <label
                            for="observacao"
                            class="form-label"
                        >
                            Observações do item
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
                            rows="3"
                            maxlength="2000"
                        >{{
                            old(
                                'observacao',
                                $item->observacao
                            )
                        }}</textarea>

                        @error('observacao')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
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
                        Estas informações só podem ser
                        alteradas enquanto a ordem estiver
                        <strong>planejada</strong>.

                        Depois que a produção for iniciada,
                        este item ficará bloqueado para edição.
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
