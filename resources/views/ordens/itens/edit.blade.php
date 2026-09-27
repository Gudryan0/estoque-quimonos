@extends('layouts.app')

@section('title', 'Editar Lote')

@section('content')

<div class="mb-4">
    <h1 class="mb-1">
        Editar lote
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
            ['item' => $item]
        )
    </div>
</div>

@endsection
