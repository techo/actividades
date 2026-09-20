@extends('errors.layout')

@section('title', 'No tenés acceso a esto')
@section('code', 'Error 403')

@section('message')
    Esta sección requiere permisos que tu cuenta no tiene. Si creés que es un error,
    escribile a la persona que coordina tu equipo.
@endsection
