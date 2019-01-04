@extends('main')

@section('page_title')
    Listado de Actividades
@endsection

@section('main_image')
    <div class="techo-hero actividades">
        <!-- <img src="{{ asset('/img/hero-slim.jpg') }}" alt="hero image" height="210"> -->
        <h2 class="text-uppercase">Si te da lo mismo, estás haciendo mal las cuentas <br>
            Anotate y participá</h2>
    </div>
@endsection

@section('main_content')

    <filtro
        categoria_seleccionada = "{{ ($categoriaSeleccionada)? $categoriaSeleccionada->id : null }}"
        categorias="{{ $categorias }}"
    >
    </filtro>

    <contenedor-de-tarjetas ref="contenedor"></contenedor-de-tarjetas>
@endsection

@section('additional_scripts')

@endsection
