@extends('layouts.app')

@section('title', 'Novo Usuário')

@section('content')

<h1 class="mb-4">
    Novo usuário
</h1>

@if($errors->any())

    <div class="alert alert-danger">

        <ul class="mb-0">

            @foreach($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach

        </ul>

    </div>

@endif

<form
    action="{{ route('usuarios.store') }}"
    method="POST"
>

    @csrf

    <div class="mb-3">

        <label
            for="name"
            class="form-label"
        >
            Nome
        </label>

        <input
            type="text"
            name="name"
            id="name"
            class="form-control"
            value="{{ old('name') }}"
            required
        >

    </div>

    <div class="mb-3">

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
            class="form-control"
            value="{{ old('email') }}"
            required
        >

    </div>

    <div class="mb-3">

        <label
            for="nivel_acesso"
            class="form-label"
        >
            Nível de acesso
        </label>

        <select
            name="nivel_acesso"
            id="nivel_acesso"
            class="form-select"
            required
        >

            <option
                value="funcionario"
                @selected(
                    old(
                        'nivel_acesso',
                        'funcionario'
                    ) === 'funcionario'
                )
            >
                Funcionário
            </option>

            <option
                value="administrador"
                @selected(
                    old('nivel_acesso')
                    === 'administrador'
                )
            >
                Administrador
            </option>

        </select>

    </div>

    <div class="mb-3">

        <label
            for="password"
            class="form-label"
        >
            Senha
        </label>

        <input
            type="password"
            name="password"
            id="password"
            class="form-control"
            minlength="8"
            required
        >

        <div class="form-text">
            A senha deve possuir pelo menos 8 caracteres.
        </div>

    </div>

    <div class="mb-3">

        <label
            for="password_confirmation"
            class="form-label"
        >
            Confirmar senha
        </label>

        <input
            type="password"
            name="password_confirmation"
            id="password_confirmation"
            class="form-control"
            minlength="8"
            required
        >

    </div>

    <button
        type="submit"
        class="btn btn-success"
    >
        Salvar
    </button>

    <a
        href="{{ route('usuarios.index') }}"
        class="btn btn-secondary"
    >
        Cancelar
    </a>

</form>

@endsection
