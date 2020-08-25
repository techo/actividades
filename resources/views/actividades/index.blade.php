@extends('main')

@section('page_title')
    Listado de Actividades
@endsection

@section('main_image')
    <div class="techo-hero actividades">
        <h2 class="text-uppercase">

            @if ( config('app.pais') == 13)
                {{ __('frontend.index_actividades_colecta') }} <br>
                        {{ __('frontend.index_actividades_colecta_2') }}
            @else 
                {{ __('frontend.index_actividades_text') }} <br>
                        {{ __('frontend.index_actividades_text_2') }}
            @endif

        </h2>
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
