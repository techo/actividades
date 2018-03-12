@extends('main')

@section('page_title')
    Home
@endsection

@section('main_image')
    <div class="techo-hero">
        <img src="{{ asset('/img/hero.jpg') }}" alt="hero image">
        <h1>Tu ayuda comienza acá</h1>
    </div>
@endsection

@section('main_content')
    <h1 class="mt-1 techo-h1">¿En qué actividad quieres participar?</h1>
    <div class="row">
        @while($categoriaActividad->count() > 0)
            @php($i = 0)
            @while ( ($i <= 3) AND ($categoriaActividad->count() > 0))
                @php($categoria = $categoriaActividad->shift())
                <div class="col-md-4 col-sm-12">
                    <div class="card">
                        <img class="card-img-top" src="{{ asset('/img/tarjeta-1.jpg') }}" alt="Card image cap" >
                        <div class="card-body px-0" style="overflow-x: scroll; ">
                            <h5 class="card-title">{{ $categoria->nombre }}</h5>
                            <p class="card-text">{{ $categoria->descripcion }}</p>
                            <p>
                                <a class="techo-h6 techo-blue" data-toggle="collapse" href="#collapse_{{ $categoria->id }}" role="button">
                                    Actividades a Realizar <i class="fas fa-chevron-down"></i>

                                </a>
                            </p>
                            <div class="collapse" id="collapse_{{ $categoria->id }}">
                                <ul>
                                    @foreach($categoria->tipos as $tipo)
                                        <li>{{ $tipo->nombre }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <a href="/actividades?categoria={{ $categoria->id }}" class="btn techo-btn-azul">Quiero Participar</a>
                        </div>
                    </div>
                </div>
                @php($i++)
            @endwhile
        @endwhile
    </div>

@endsection

@section('footer')
    @include('partials.footer')
@endsection