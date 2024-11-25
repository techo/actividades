@extends('main')

@section('page_title')
    Equipo TECHO
@endsection

@section('main_image')
    <div class="techo-hero actividades" style="
        background: url({{$homeHeader->imagen}}); 
        background-size: cover;
        max-width: 100%;">
        <h2 class="text-uppercase">
            EQUIPO TECHO
        </h2>
    </div>
@endsection

@push('additional_scripts')
    <script>
        // Define la URL de la imagen de fondo
        var imagenFondo = '/img/background-actividades.png';
        // Selecciona el elemento con el ID "main-background" y establece la imagen de fondo
        document.getElementById('main-background').style.backgroundImage = 'url(' + imagenFondo + ')';
        document.getElementById('main-background').style.backgroundSize = 'cover';
    </script>
@endpush

@section('main_content')
    <div class="container-fluid" >
        <div class="row"  style="display: none;">
            <div class="col-md-12">
                <filtro
                    categoria_seleccionada="{{ ($categoriaSeleccionada) ? $categoriaSeleccionada->id : null }}"
                    tipo_seleccionada="{{ ($tipoSeleccionada) ? $tipoSeleccionada->idTipo : null }}"
                    categorias="{{ $categorias }}"
                >
                </filtro>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <contenedor-de-tarjetas ref="contenedor" horizontal="true"></contenedor-de-tarjetas>
            </div>
        </div>
    </div>
    <!-- <aviso-modal></aviso-modal> -->



@endsection

@section('footer')
    @include('partials.footer')
@endsection
