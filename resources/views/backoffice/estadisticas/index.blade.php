@extends('backoffice.main')

@section('page_title', 'Generales')

@section('content')

@include('backoffice.estadisticas.filtros')

<estadisticas-generales></estadisticas-generales>

@endsection