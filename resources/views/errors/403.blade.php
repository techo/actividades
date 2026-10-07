@extends('errors.layout')

@section('title', __('errors.e403.title'))
@section('code', __('errors.code', ['n' => 403]))

@section('message')
    @php
        // Solo los 403 "explicados" muestran su motivo (ver AccesoExplicadoException).
        $explicado = isset($exception) && $exception->getPrevious() instanceof \App\Exceptions\AccesoExplicadoException
            ? $exception->getMessage() : null;
    @endphp
    {{ $explicado ?: __('errors.e403.message') }}
@endsection
