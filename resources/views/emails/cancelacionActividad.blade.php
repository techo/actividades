@extends('emails.template')

@section('content')
    <p style="font-size: larger">
        @lang('frontend.hello') {{$persona->nombres}},
    </p>
    <p>
        @lang('email.activity_canceled_1') <strong>{{$actividad->nombreActividad}}</strong> de
        TECHO - {{$pais->nombre}}  

        @if($inscripcion->actividad->show_dates)
            @lang('email.begins_on')
            {{$actividad->fechaInicio->format('d/m/Y')}}, 
        @endif
        
        @lang('email.has_been') <strong>@lang('email.cancelada')</strong>
    </p>
    <p>
        @lang('email.activity_canceled_2')
    </p>
@endsection