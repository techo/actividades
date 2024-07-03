@extends('backoffice.main')

@section('page_title', __('backend.role_assignment'))

@section('content')
    <asignacion-de-rol
        roles="{{$roles}}"
    ></asignacion-de-rol>
@endsection