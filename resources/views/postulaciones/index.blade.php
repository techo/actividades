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
        <div class="text-center">
            <p>
            {{ __('frontend.application_description') }}
            </p>

            <p>
            {{ __('frontend.application_descripcion_call') }}
            </p>
        </div>
        <div class="row">
            <div class="col-md-12">
                <contenedor-de-tarjetas ref="contenedor" horizontal="true"></contenedor-de-tarjetas>
            </div>
        </div>
        

        <div class="row bg-techo-cool-gray p-3 mx-0" style="border-radius: 20px 20px 20px 20px;">
            <div class="col-md-10 mx-auto">
                <p class="mb-2 font-weight-bold text-left lead techo-blue">{{ __('frontend.autotest_pregunta') }}</p>
                <a class="btn bg-techo-yellow font-weight-bold text-white rounded-pill px-4 py-2 w-100 d-block" href="/autotest">
                    {{ __('frontend.autotestTituloBoton') }} <br>
                    <span class="techo-violet font-weight-bold lead">QUIZ</span>
                </a>
            </div>
        </div>


    </div>
    <!-- <aviso-modal></aviso-modal> -->



@endsection

@section('footer')
    @include('partials.footer')
@endsection
