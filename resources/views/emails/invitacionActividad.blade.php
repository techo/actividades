@extends('emails.template')

@section('content')

    <p style="font-size: larger">
        @lang('frontend.hello') {{ $persona->nombres }},
    </p>

    {{-- Cuerpo HTML compuesto por el admin en el editor enriquecido (TinyMCE). --}}
    <div>{!! $mensaje !!}</div>

    <p>
        <strong>{{ $actividad->nombreActividad }}</strong> - TECHO - {{ $actividad->pais->nombre }}
        @if($actividad->show_dates)
            · {{ $actividad->fechaInicio->format('d/m/Y') }}
        @endif
    </p>

    <p>
        <a href="{{ url('/actividades/' . $actividad->idActividad) }}"
           style="display:inline-block; background-color:#0092dd; color:#ffffff; text-decoration:none; padding:12px 24px; border-radius:4px; font-weight:700; font-family: Fredoka, Montserrat, sans-serif;">
            {{ $actividad->nombreActividad }}
        </a>
    </p>

    <p>
        <a href="{{ url('/actividades/' . $actividad->idActividad) }}">
            {{ url('/actividades/' . $actividad->idActividad) }}
        </a>
    </p>

    <p>
        <strong>@lang('email.greetings')</strong>
    </p>

@endsection
