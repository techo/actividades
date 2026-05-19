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
@if($inscripcion->voucherURL && !$inscripcion->pago)
<div class="container py-5 text-center" style="max-width:520px;margin:0 auto;">
    <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4"
         style="background:#F4A345;width:80px;height:80px;">
        <i class="fa fa-credit-card" style="font-size:32px;color:white;"></i>
    </div>
    <h2 class="font-weight-bold mb-3" style="color:#1A3A6B;">
        ¡Proceso completado!<br>Ya estas pre-inscripto/a
    </h2>
    <p class="text-muted mb-4">
        {{ __('frontend.payment_in_process') }}
    </p>
    <div class="card mb-4" style="background:#F5F5F5;border:none;border-radius:12px;">
        <div class="card-body text-left">
            <h6 class="font-weight-bold mb-3" style="color:#F4A345;">Resumen de la operación</h6>
            <ul class="list-unstyled mb-0">
                <li class="mb-2">
                    <strong>Actividad:</strong>
                    <span class="text-muted ml-1">{{ $actividad->nombreActividad }}</span>
                </li>
                @if($actividad->descripcionPago)
                <li class="mb-2">
                    <strong>Método:</strong>
                    <span class="text-muted ml-1">{{ strip_tags($actividad->descripcionPago) }}</span>
                </li>
                @endif
                <li>
                    <strong style="color:#F4A345;">Comprobante en proceso de validación</strong>
                </li>
            </ul>
        </div>
    </div>
    <a href="/actividades" class="btn btn-secondary btn-block" style="border-radius:8px;padding:12px;">
        {{ __('frontend.my_activities') }}
    </a>
</div>
@else
<div class="container-fluid card">
    <div class="card-body">
    <div class="row">
            <div class="col-md-12">
                <h2 class="card-subtitle"> {{ __('frontend.last_step_confirm_by_donation') }}</h2>
            </div>
            <hr>
        </div>

        <div class="row">
            <div class="col-md-12">
                <h3 class="card-title">
                    <br>
                    {{ __('frontend.you_are_pre_registered') }}
                    <a href="/actividades/{{$actividad->idActividad}}">
                        {{ $actividad->nombreActividad }}
                    </a>
                </h3>
            </div>
        </div>

        <h3>
            {{ __('frontend.complete_registration') }}
        </h3>

        
        <div class="row justify-content-start">
            <div class="col-md-9">
                <p>
                    {{ __('frontend.mail_sended') }}</a>
                </p>
                
                <br>
                @if($actividad->pago == 1)
                <div class="row">
                                <div class="col-md-12">
                                    <p class="font-weight-bold"> {{ __('frontend.make_donation') }}
                                        
                                    {!! $actividad->descripcionPago !!}
                                    </p>

                                    <p>
                                    
                                    <div class=" p-1">
                                    @if ($actividad->montoMax === '0.00')
                                    {{ __('frontend.suggested_donation') . $actividad->montoMin}} ({{$actividad->moneda}}{{ __('frontend.coin_name') }})
                                    @else
                                    {{__('frontend.suggested_donation_between') . $actividad->montoMin . __('frontend.and') . $actividad->montoMax }} ({{$actividad->moneda}}{{ __('frontend.coin_name') }})
                                    @endif
                                    
                                    </p>
                                    <confirmacion-pago id="{{$inscripcion->idInscripcion}}" voucher="{{$inscripcion->voucherUrl}}"  csrf_token="{{ csrf_token() }}"></confirmacion-pago>
                                    <!-- <a href="{{ $actividad->linkPago }}" class="btn btn-link" target="_blank">FORM</a> -->
                                    @if ($inscripcion->voucherUrl)
                                    {{ __('frontend.payment_in_process') }}
                                    @endif

                                    <br>
                                    </div>
                                </div>
                            </div>
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
                                    <input type="number" class="form-control" placeholder="{{ $actividad->moneda }}" name="monto" min="1" required step="0.1">
                                    @if ($actividad->montoMax === '0.00')
                                    <p>{{ __('frontend.suggested_donation') . $actividad->montoMin}} ({{$actividad->moneda}}{{ __('frontend.coin_name') }})</p>
                                    @else
                                    <p> {{__('frontend.suggested_donation_between') . $actividad->montoMin . __('frontend.and') . $actividad->montoMax }} ({{$actividad->moneda}}{{ __('frontend.coin_name') }})</p>
                                    @endif
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
                            <a href="/" class="btn btn-link">{{ __('frontend.go_back') }}</a>
                            @else
                            <a href="{{ action('InscripcionesController@puntoDeEncuentro', ['id' => $actividad->idActividad]) }}" class="btn btn-link">{{ __('frontend.go_back') }}</a>
                            @endif
                            @if($actividad->pais->id == 9999)
                            rea
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
@endif
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