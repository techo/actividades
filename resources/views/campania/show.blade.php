@extends('main')

@section('page_title')
    {{ $campaign->nombre }}
@endsection

@section('main_content')

    @if($campaign->imagen)
        <div style="width:100%; max-height:400px; overflow:hidden; background:#000;">
            <img src="{{ asset($campaign->imagen) }}" alt="{{ $campaign->nombre }}"
                 style="width:100%; object-fit:cover; max-height:400px; display:block; opacity:0.92;">
        </div>
    @endif

    @if($campaign->descripcion)
        <div class="container mt-3 mb-2">
            <p class="text-center text-muted">{{ $campaign->descripcion }}</p>
        </div>
    @endif

    @php
        $authUser = auth()->check() ? auth()->user() : null;
        $userData = $authUser ? [
            'idPersona' => $authUser->idPersona,
            'nombre'    => $authUser->nombres,
            'apellido'  => $authUser->apellidoPaterno,
            'mail'      => $authUser->mail,
            'telefono'  => $authUser->telefonoMovil,
        ] : null;
    @endphp

    <suscribe
        :pais="{{ json_encode($pais) }}"
        :campaign="{{ json_encode($campaign) }}"
        :preguntas="{{ json_encode($campaign->preguntas) }}"
        :user="{{ json_encode($userData) }}"
    ></suscribe>

@endsection
