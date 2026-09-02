@extends('emails.template')

@section('content')

    <p style="font-size: larger">
        @lang('frontend.hello'){{ $nombre ? ' ' . $nombre : '' }},
    </p>

    {{-- Cuerpo HTML compuesto por el admin en el editor enriquecido (TinyMCE). --}}
    <div>{!! $mensaje !!}</div>

    <p>
        <strong>{{ $campaign->nombre }}</strong>
    </p>

    @php($urlCampania = $pais ? url($pais->abreviacion . '/campania/' . $campaign->id) : null)

    @if($urlCampania)
        <p>
            <a href="{{ $urlCampania }}"
               style="display:inline-block; background-color:#0092dd; color:#ffffff; text-decoration:none; padding:12px 24px; border-radius:4px; font-weight:700; font-family: Fredoka, Montserrat, sans-serif;">
                {{ $campaign->nombre }}
            </a>
        </p>
        <p>
            <a href="{{ $urlCampania }}">{{ $urlCampania }}</a>
        </p>
    @endif

    <p>
        <strong>@lang('email.greetings')</strong>
    </p>

@endsection
