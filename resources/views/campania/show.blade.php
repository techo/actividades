@extends('main')

@section('page_title')
    {{ $campaign->nombre }}
@endsection

@section('main_content')

    @if($campaign->descripcion)
        <div class="container mt-3 mb-2">
            <p class="text-center text-muted">{{ $campaign->descripcion }}</p>
        </div>
    @endif

    <suscribe
        :pais="{{ json_encode($pais) }}"
        :campaign="{{ json_encode($campaign) }}"
        :preguntas="{{ json_encode($campaign->preguntas) }}"
    ></suscribe>

@endsection
