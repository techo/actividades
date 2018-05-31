@extends('backoffice.main')

@section('page_title', 'Asignación de Rol')

@section('content')
    <asignacion-de-rol
        roles="{{$roles}}"
    ></asignacion-de-rol>
@endsection