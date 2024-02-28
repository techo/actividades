@extends('main')

@section('page_title')
    {{ __('frontend.last_step_confirm_by_donation') }}
@endsection


@section('main_image')
    <div class="techo-hero actividades">
        <h2></h2>
    </div>
@endsection

@section('main_content')   
<div class="container-fluid card" >
    <div class="card-body">
    <div class="row">
        <div class="col-md-12">
            <h2 class="card-subtitle"> {{ __('frontend.last_step_confirm_by_donation') }}</h2>
        </div>
        <hr>
    </div>
    <div class="row">
        <div class="col-md-9">
            <h3 class="card-title">
                <br>
                {{ __('frontend.you_are_pre_registered') }}
                <a href="/actividades/{{$actividad->idActividad}}">
                    {{ $actividad->nombreActividad }}
                </a>
            </h3>
        </div>
    </div>
    <div class="row justify-content-start">
        <div class="col-md-9">
            <p>
                {{ __('frontend.mail_sended') }}</a>
            </p>
            <h3>
                {{ __('frontend.complete_registration') }}
            </h3>
         {{--    <p>
                <ul>
                    <li>Completando el monto a donar en esta misma página.</li>
                    <li>Siguiendo las instrucciones del mail que recibiste.</li>
                </ul>
            </p> --}}
            <br>
            @if($actividad->pago == 1)

                    <form action="{{ action('InscripcionesController@donacionCheckout', ['id' => $actividad->idActividad]) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-8">
                            @if($actividad->pais->id == 99999)
                                <div class="row">
                                    <div class="col-md-12">
                                        <p class="font-weight-bold">{{ __('frontend.donation_ammount') }}</p>
                                    </div>
                                </div>


                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="number" class="form-control" placeholder="{{ $actividad->moneda }}" name="monto"
                                               min="1" required step="0.1">
                                        @if ($actividad->montoMax === '0.00')
                                            <p>{{ __('frontend.suggested_donation') . $actividad->montoMin}} ({{$actividad->moneda}}$)</p>
                                        @else
                                            <p> {{__('frontend.suggested_donation_between') . $actividad->montoMin . __('frontend.and') . $actividad->montoMax }} ({{$actividad->moneda}}$)</p>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="row">
                                        <div class="col-md-12">
                                           <p class="font-weight-bold"> {{ __('frontend.make_donation') }} 
                                            <a href="{{ $actividad->linkPago }}" class="btn btn-link" target="_blank">LINK</a>
                                           </p>
                                        
                                
                                    @if ($actividad->montoMax === '0.00')
                                            {{ __('frontend.suggested_donation') . $actividad->montoMin}} ({{$actividad->moneda}}$)
                                    @else
                                            {{__('frontend.suggested_donation_between') . $actividad->montoMin . __('frontend.and') . $actividad->montoMax }} ({{$actividad->moneda}}$)
                                    @endif
                                            <br>
                                        </div>
                                </div>
                            @endif
                            </div>
                            @if(!empty($actividad->beca))
                                <div class="col-md-4">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <p class="font-weight-bold">{{__('frontend.also_you_can') }}
                                                <a href="{{ $actividad->beca }}" class="btn btn-link" target="_blank">{{ __('frontend.ask_for_grant') }}</a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-4">
                                    @if(Auth::check() && Auth::user()->estaPreInscripto($actividad->idActividad))
                                        <a href="{{ action('ActividadesController@show', ['id' => $actividad->idActividad]) }}" class="btn btn-link">{{ __('frontend.go_back') }}</a>
                                    @else
                                        <a href="{{ action('InscripcionesController@puntoDeEncuentro', ['id' => $actividad->idActividad]) }}" class="btn btn-link">{{ __('frontend.go_back') }}</a>
                                    @endif
                                @if($actividad->pais->id == 9999)
                                    <button type="submit" class="btn btn-primary" style="margin: 0 16px;">{{ __('frontend.continue') }}</button>
                                @endif
                            </div>
                        </div>

                    </form>
            @endif
            <br>
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