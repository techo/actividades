@extends('main')

@section('page_title')
    Listado de Actividades
@endsection

@section('main_image')
    <div class="techo-hero actividades" style="
        background: url({{$homeHeader->imagen}}); 
        background-size: cover;
        max-width: 100%;">
        <h2 class="text-uppercase">
            {{ $homeHeader->header }} <br>
            {{ $homeHeader->subHeader }} 
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
