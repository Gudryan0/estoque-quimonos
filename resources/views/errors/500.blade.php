@extends('errors.layout')

@section('title', 'Erro interno')

@section('code', '500')

@section('heading', 'Não foi possível concluir a operação')

@section('message')
    Ocorreu um erro inesperado no sistema.
    Nenhum detalhe técnico é exibido nesta página.
    Tente novamente em alguns instantes.
@endsection
