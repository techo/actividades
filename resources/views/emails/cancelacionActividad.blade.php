@extends('emails.template')

@section('content')
    <p style="font-size: larger">
        @lang('frontend.hello') {{$persona->nombres}},
    </p>
    <p>
        @lang('emails.activity_canceled_1') <strong>{{$actividad->nombreActividad}}</strong> de
        TECHO - {{$pais->nombre}}  @lang('emails.begins_on')
        {{$actividad->fechaInicio->format('d/m/Y')}}, @lang('emails.has_been') <strong>@lang('emails.cancelada')</strong>
    </p>
    <p>
        @lang('emails.activity_canceled_2')
    </p>
@endsection