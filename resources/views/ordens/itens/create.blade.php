@extends('layouts.app')

@section('title', 'Adicionar Lote')

@section('content')

<div class="mb-4">
    <h1 class="mb-1">
        Adicionar lote
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
        @include(
            'ordens.itens._form',
            ['item' => null]
        )
    </div>
</div>

@endsection
