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

    <suscribe
        :pais="{{ json_encode($pais) }}"
        :campaign="{{ json_encode($campaign) }}"
        :preguntas="{{ json_encode($campaign->preguntas) }}"
    ></suscribe>

@endsection
