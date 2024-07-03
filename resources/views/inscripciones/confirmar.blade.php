@extends('main')

@section('page_title')
    {{ __('frontend.activity_detail') }}
@endsection


@section('main_image')
@endsection

@section('main_content')

<div class="container-fluid card" >
    <div class="card-body">
    <div class="row">
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-12">
                    @if( $actividad->confirmar == 1 ||
                    $actividad->pago == 1 )
                        <h2 class="card-title">{{ __('frontend.finish_pre_registration') }}</h2>
                    @else
                        <h2 class="card-title">{{ __('frontend.finish_registration') }}</h2>
                    @endif
                </div>
            </div>
            <div class="row">
            @if( $actividad->show_location)
                <hr>
                <div class="col-md-12">
                    <h4>{{ __('frontend.meeting_points') }}</h4>
                </div>
            @endif
        </div>

            <form action="/inscripciones/actividad/{{$actividad->idActividad}}/gracias" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="punto_encuentro" value="{{ $punto_encuentro->idPuntoEncuentro }}">
                <input type="hidden" name="roles_aplicados" value="{{ $roles_aplicados }}">
                <input type="hidden" name="inscripciones_aplicadas" value="{{ $inscripciones_aplicadas }}">

                <div class="row">
                    <div class="col-md-12">
                        @if( $actividad->show_location)
                            <p>
                                @if ($punto_encuentro->localidad)
                                    {{ $punto_encuentro->punto }}{{', '. $punto_encuentro->localidad->localidad . ', ' . $punto_encuentro->provincia->provincia }}
                                @else
                                    {{ $punto_encuentro->punto }}{{', ' . $punto_encuentro->provincia->provincia }}
                                @endif
                            </p>
                            <p>
                                <h4>{{ __('frontend.at') }}</h4>
                                {{ \Illuminate\Support\Carbon::parse($punto_encuentro->horario)->format('H:i') }} hs
                            </p>
                        @endif
                    </div>
                </div>
                @php
                    $rolesAplicados = json_decode($roles_aplicados, true);
                    $inscripcionesAplicadas = json_decode($inscripciones_aplicadas, true);
                @endphp

                @if ($inscripcionesAplicadas)
                
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <h4>{{ __('frontend.type_of_inscription') }}</h4>
                        </div>
                    </div>
                    <div class="row">
                    
                    @foreach($inscripcionesAplicadas as $rol)
                        <span class="ml-2 text-white rounded-pill p-2 techo-btn-azul">
                            {{ $rol['text'] }}
                        </span>
                    @endforeach
                    </div>
                @endif
               
                @if ($rolesAplicados)
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <h4>{{ __('frontend.roles_aplicados') }}</h4>
                        </div>
                    </div>
                    <div class="row">
                    
                    @foreach($rolesAplicados as $rol)
                        <span class="ml-2 text-white rounded-pill p-2 techo-btn-azul">
                            {{ $rol['text'] }}
                        </span>
                    @endforeach
                    </div>
                @endif

                
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <label for="aceptar_terminos">
                            <input
                                    type="checkbox"
                                    name="aceptar_terminos"
                                    id="aceptar_terminos"
                                    value="1"
                                    required
                            >
                            {{ __('frontend.accept') }} 
                            @if($actividad->idPais == 33)
                            <a href="/carta-voluntariado-brasil" target="_blank">
                            @else
                            <a href="/carta-voluntariado" target="_blank">
                            @endif
                                {{ __('frontend.terms_and_conditions') }}</a>
                        </label>
                        @if($mensaje = Session::get('status'))
                            <p class="text-danger">{{ $mensaje }}</p>
                        @endif
                    </div>
                </div>
                @if($actividad->acuerdo_especifico_url)
                    <div class="row">
                        <div class="col-md-12">
                            <label for="acuerdo_especifico_url">
                                <input
                                        type="checkbox"
                                        name="acuerdo_especifico_url"
                                        id="acuerdo_especifico_url"
                                        required
                                >
                                {{ __('frontend.accept') }} 
                                
                                <a href="{{ $actividad->acuerdo_especifico_url }}" target="_blank">
                                    {{ __('frontend.acuerdo_especifico') }}</a>
                            </label>
                            @if($mensaje = Session::get('status'))
                                <p class="text-danger">{{ $mensaje }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                @if($actividad->acuerdo_menores_url && $edad < 18)
                    <div class="row">
                        <div class="col-md-12">
                            <label for="acuerdo_menores_url">
                                <input
                                        type="checkbox"
                                        name="acuerdo_menores_url"
                                        id="acuerdo_menores_url"
                                        required
                                >
                                {{ __('frontend.accept') }} 
                                
                                <a href="{{ $actividad->acuerdo_menores_url }}" target="_blank">
                                    {{ __('frontend.acuerdo_menores') }}
                                </a>
                                {{ __('frontend.compromiso_firma') }}

                            </label>
                            @if($mensaje = Session::get('status'))
                                <p class="text-danger">{{ $mensaje }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                <hr>
                <div class="row  align-middle">
                    <div class="col-md-2 text-primary">
                        <a href="/inscripciones/actividad/{{$actividad->idActividad}}" class="btn btn-link"> {{ __('frontend.go_back') }}</a>
                    </div>
                    <div class="col-md-3">
                        <input type="submit" value="{{ __('frontend.finish') }}" class="btn btn-primary">
                    </div>
                </div>
            </form>
        </div>
        <div class="col-md-4 prev">
            <div class="card d-none d-lg-block" style="border: none">
                <img src="{{ $actividad->tipo->imagen }}" style="margin-bottom: 1em; width: 100%;">
                <div class="row">
                    <div class="col-md-12" >
                        <h6 style="color: {{$actividad->tipo->categoria->color}}; font-weight: 700 !important;" >{{ $actividad->tipo->nombre }}</h6>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <h5>{{ $actividad->nombreActividad }}</h5>
                    </div>
                </div>
                <hr>

                @if($actividad->show_dates)     
                    <div class="row">
                        <div class="col-md-4"><i class="far fa-calendar"></i>
                            <span>{{ $actividad->fechaInicio->format('d/m/y') }}</span></div>
                        <div class="col-md-4"><i class="far fa-clock"></i>
                            <span>{{ $actividad->fechaInicio->format('h:m') }}</span></div>
                        <div class="col-md-4"><i class="fas fa-map-marker-alt"></i> 
                            <span>
                                @if ($actividad->idLocalidad)
                                    {{ $actividad->localidad->localidad }}, {{ $actividad->provincia->provincia }}
                                @else
                                    {{ $actividad->provincia->provincia }}
                                @endif
                            </span>
                        </div>
                    </div>
                    <hr>
                @endif
                <div class="row">
                    <div class="col-md-12">
                        {!! $actividad->descripcion !!}
                    </div>
                </div>

            </div>
        </div>
    </div>
    </div>
    </div>

@endsection

@push('additional_scripts')
    <script>
        // Define la URL de la imagen de fondo
        var imagenFondo = '/img/background-perfil.png';
        // Selecciona el elemento con el ID "main-background" y establece la imagen de fondo
        document.getElementById('main-background').style.backgroundImage = 'url(' + imagenFondo + ')';
        document.getElementById('main-background').style.backgroundSize = 'cover';
    </script>
@endpush

@section('footer')
    @include('partials.footer')
@endsection