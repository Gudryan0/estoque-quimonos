@extends('layouts.app')

@section('title', 'Cancelar Ordem de Produção')

@section('content')

@php
    $planejada =
        $ordem->status === 'planejada';
@endphp

<div class="mb-3">
    <a
        href="{{ route('ordens.show', $ordem) }}"
        class="btn btn-outline-secondary btn-sm"
    >
        ← Voltar para a ordem
    </a>
</div>

<div class="mb-4">
    <h1 class="mb-1">
        Cancelar ordem de produção
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

        {{-- Resumo --}}
        <div class="card mb-4">
            <div class="card-body p-4">
                <div class="row g-4">

                    <div class="col-sm-6 col-lg-3">
                        <div class="small text-muted mb-1">
                            Cliente
                        </div>

                        <strong>
                            {{
                                $ordem->cliente_nome
                                ?? 'Sem cliente definido'
                            }}
                        </strong>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="small text-muted mb-1">
                            Status atual
                        </div>

                        @if($planejada)
                            <span
                                class="
                                    badge
                                    bg-secondary-subtle
                                    text-secondary-emphasis
                                    border
                                    border-secondary-subtle
                                "
                            >
                                Planejada
                            </span>
                        @else
                            <span
                                class="
                                    badge
                                    bg-primary-subtle
                                    text-primary-emphasis
                                    border
                                    border-primary-subtle
                                "
                            >
                                Em produção
                            </span>
                        @endif
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="small text-muted mb-1">
                            Itens
                        </div>

                        <strong>
                            {{ $ordem->itens_count }}

                            {{
                                $ordem->itens_count === 1
                                    ? 'item'
                                    : 'itens'
                            }}
                        </strong>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="small text-muted mb-1">
                            Data prevista
                        </div>

                        @if($ordem->data_prevista)
                            <strong>
                                {{
                                    $ordem
                                        ->data_prevista
                                        ->format('d/m/Y')
                                }}
                            </strong>
                        @else
                            <span class="text-muted">
                                Sem previsão
                            </span>
                        @endif
                    </div>

                </div>
            </div>
        </div>

        @if($planejada)

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
                <strong>
                    Esta ordem ainda não foi iniciada.
                </strong>

                O cancelamento não alterará o estoque
                de materiais ou produtos.
            </div>

        @else

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
                <strong>
                    Atenção: esta ordem já está em produção.
                </strong>

                Os materiais consumidos quando a OP foi
                iniciada <strong>não serão devolvidos
                automaticamente</strong> ao estoque.

                Esse comportamento preserva o consumo físico
                que já ocorreu na confecção.
            </div>

        @endif

        {{-- Formulário --}}
        <div class="card">
            <div class="card-body p-4">

                @if($errors->any())
                    <div class="alert alert-danger mb-4">
                        <strong>
                            Verifique os dados informados.
                        </strong>

                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $erro)
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
                            'ordens.cancelar',
                            $ordem
                        )
                    }}"
                    method="POST"
                >
                    @csrf

                    <div class="mb-4">
                        <label
                            for="motivo_cancelamento"
                            class="form-label"
                        >
                            Motivo do cancelamento
                            <span class="text-danger">
                                *
                            </span>
                        </label>

                        <textarea
                            name="motivo_cancelamento"
                            id="motivo_cancelamento"
                            class="
                                form-control

                                @error(
                                    'motivo_cancelamento'
                                )
                                    is-invalid
                                @enderror
                            "
                            rows="5"
                            minlength="3"
                            maxlength="2000"
                            required
                            placeholder="Explique por que esta ordem está sendo cancelada"
                        >{{ old('motivo_cancelamento') }}</textarea>

                        @error('motivo_cancelamento')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="form-text">
                            O motivo ficará registrado
                            permanentemente para consulta
                            e auditoria.
                        </div>
                    </div>

                    <div
                        class="
                            border
                            rounded
                            p-3
                            mb-4
                        "
                    >
                        <div class="form-check">
                            <input
                                type="checkbox"
                                name="confirmar_cancelamento"
                                value="1"
                                id="confirmar_cancelamento"
                                class="
                                    form-check-input

                                    @error(
                                        'confirmar_cancelamento'
                                    )
                                        is-invalid
                                    @enderror
                                "
                                required
                            >

                            <label
                                for="confirmar_cancelamento"
                                class="form-check-label"
                            >
                                Confirmo que desejo cancelar
                                esta ordem de produção e
                                compreendo as consequências
                                descritas acima.
                            </label>

                            @error('confirmar_cancelamento')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button
                            type="submit"
                            class="btn btn-danger"
                        >
                            Confirmar cancelamento
                        </button>

                        <a
                            href="{{
                                route(
                                    'ordens.show',
                                    $ordem
                                )
                            }}"
                            class="btn btn-outline-secondary"
                        >
                            Voltar sem cancelar
                        </a>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>

@endsection
