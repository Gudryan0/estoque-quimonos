@extends('errors.layout')

@section('title', 'Sessão expirada')

@section('code', '419')

@section('heading', 'Sua sessão expirou')

@section('message')
    Por segurança, a sessão ou o formulário expirou.
    Volte ao sistema e tente realizar a operação novamente.
@endsection
