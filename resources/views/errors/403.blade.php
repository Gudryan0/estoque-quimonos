@extends('errors.layout')

@section('title', 'Acesso não permitido')

@section('code', '403')

@section('heading', 'Acesso não permitido')

@section('message')
    Você não possui permissão para acessar
    esta área ou executar esta operação.
@endsection
