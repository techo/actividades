@extends('main')

@section('page_title')
    Listado de Actividades
@endsection

@section('main_image')
    <div class="techo-hero actividades">
        <!-- <img src="{{ asset('/img/hero-slim.jpg') }}" alt="hero image" height="210"> -->
        <h2 class="text-uppercase">{{ __('frontend.index_actividades_text') }} <br>
            {{ __('frontend.index_actividades_text_2') }}</h2>
    </div>
@endsection

@section('main_content')

    <filtro
        categoria_seleccionada = "{{ ($categoriaSeleccionada)? $categoriaSeleccionada->id : null }}"
        categorias="{{ $categorias }}"
    >
    </filtro>

    <contenedor-de-tarjetas ref="contenedor"></contenedor-de-tarjetas>

    <!-- <aviso-modal></aviso-modal> -->



@endsection
