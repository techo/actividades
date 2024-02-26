@extends('main')

@section('page_title')
    {{ __('frontend.activity_detail') }}
@endsection


@section('main_image')
@endsection

@section('main_content')
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
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <h4>{{ __('frontend.meeting_points') }}</h4>
                </div>
            </div>
            <form action="/inscripciones/actividad/{{$actividad->idActividad}}/gracias" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="punto_encuentro" value="{{ $punto_encuentro->idPuntoEncuentro }}">
                <input type="hidden" name="roles_aplicados" value="{{ $roles_aplicados }}">

                <div class="row">
                    <div class="col-md-12">
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
                    </div>
                </div>
                @php
                    $rolesAplicados = json_decode($roles_aplicados, true);
                @endphp
                @if ($aplica_rol == 'true')
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
                            {{ __('frontend.accept') }} <a href="/carta-voluntariado" target="_blank">{{ __('frontend.terms_and_conditions') }}</a>
                        </label>
                        @if($mensaje = Session::get('status'))
                            <p class="text-danger">{{ $mensaje }}</p>
                        @endif
                    </div>
                </div>
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
                <img src="{{ $actividad->tipo->imagen }}" style="margin-bottom: 1em;">
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
                <div class="row">
                    <div class="col-md-12">
                        {!! $actividad->descripcion !!}
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection
@section('footer')
    @include('partials.footer')
@endsection