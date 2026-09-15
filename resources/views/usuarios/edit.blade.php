@extends('layouts.app')

@section('title', 'Editar Usuário')

@section('content')

<h1 class="mb-4">
    Editar usuário
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
    action="{{ route(
        'usuarios.update',
        $usuario
    ) }}"
    method="POST"
>

    @csrf
    @method('PUT')

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
            value="{{ old(
                'name',
                $usuario->name
            ) }}"
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
            value="{{ old(
                'email',
                $usuario->email
            ) }}"
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
                        $usuario->nivel_acesso
                    ) === 'funcionario'
                )
            >
                Funcionário
            </option>

            <option
                value="administrador"
                @selected(
                    old(
                        'nivel_acesso',
                        $usuario->nivel_acesso
                    ) === 'administrador'
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
            Nova senha
        </label>

        <input
            type="password"
            name="password"
            id="password"
            class="form-control"
            minlength="8"
        >

        <div class="form-text">
            Deixe em branco para manter a senha atual.
        </div>

    </div>

    <div class="mb-3">

        <label
            for="password_confirmation"
            class="form-label"
        >
            Confirmar nova senha
        </label>

        <input
            type="password"
            name="password_confirmation"
            id="password_confirmation"
            class="form-control"
            minlength="8"
        >

    </div>

    <button
        type="submit"
        class="btn btn-success"
    >
        Salvar alterações
    </button>

    <a
        href="{{ route('usuarios.index') }}"
        class="btn btn-secondary"
    >
        Cancelar
    </a>

</form>

@endsection
