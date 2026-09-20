@extends('errors.layout')

@section('title', 'Algo salió mal')
@section('code', 'Error 500')

@section('message')
    Tuvimos un problema de nuestro lado y no pudimos completar lo que estabas haciendo.
    Ya quedó registrado. Podés reintentar en un momento.
@endsection

@section('actions')
    @if (!empty($puedeReportar) && !empty($reportUrl))
        <a href="{{ $reportUrl }}" class="err-btn err-btn-primary">Reportar este problema</a>
    @endif
@endsection

@section('below')
    @if (!empty($puedeReportar))
        <p class="err-ref">Como coordinás/administrás el sistema, podés reportarlo para que el equipo lo revise.</p>
    @endif
@endsection
