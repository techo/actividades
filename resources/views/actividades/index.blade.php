@extends('main')

@section('page_title')
    Listado de Actividades
@endsection

@section('main_image')
    <div class="techo-hero">
        <img src="{{ asset('/img/hero-slim.jpg') }}" alt="hero image" height="210">
        <h2>Inscríbete y acompáñanos con tu voluntariado</h2>
    </div>
@endsection

@section('main_content')
    <filtro
        categoria_seleccionada = "{{ $categoriaSeleccionada->id }}"
        categorias="{{ $categorias }}"
        provincias="{{ $provincias }}"
    >
    </filtro>
    <div class="row">
        <div class="card-deck mb-3 text-center">
            <tarjeta v-for="act in actividades" v-bind:actividad="act"></tarjeta>
        </div>
    </div>
    <div v-show="loading">Cargando...</div>
@endsection

@section('additional_scripts')

@endsection
