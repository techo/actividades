@extends('main')

@section('page_title')
    TECHO: Bienvenido
@endsection

@section('main_image')
    @if ($mensaje = Session::get('mensaje'))
    <div class="alert alert-success alert-block" style="margin-top: 1rem;">
        <button type="button" class="close" data-dismiss="alert">×</button> 
            <strong>{{ $mensaje }}</strong>
    </div>
    @endif
    <div class="techo-hero">
        <picture>
          <source srcset="{{ asset('/img/hero-mobile.jpg') }}" media="(max-width: 720px)">
          <img src="{{ asset('/img/hero.jpg') }}" alt="hero image">
        </picture>
        <h1 class="text-uppercase">Transformemos esta realidad</h1>
        <estadisticas-publicas>   </estadisticas-publicas>
    </div>

    

@endsection

@section('main_content')
    <h1 class="text-primary font-weight-bold">¿En qué actividad quieres participar?</h1>
    <div class="card-deck home">
        @while($categoriaActividad->count() > 0)
            @php($i = 0)
            @while ( ($i <= 3) AND ($categoriaActividad->count() > 0))
                @php($categoria = $categoriaActividad->shift())
                    <div class="card border-light">
                        <a href="/actividades?categoria={{ $categoria->id }}">
                            <img
                                    class="card-img-top img-fluid"
                                    src="{{ $categoria->imagen }}"
                                    alt="{{ $categoria->nombre }}"
                                    width="380"
                            >
                        </a>
                        <div class="card-body px-0">
                            <h3 class="card-title">{{ $categoria->nombre }}</h3>
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
                            <a href="/actividades?categoria={{ $categoria->id }}" class="btn btn-primary">{{ $categoria->nombre == "Eventos Especiales" ? "Ver eventos" : "Quiero saber más"}}</a>
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