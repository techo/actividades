@extends('main')

@section('page_title')
    Desuscripción confirmada
@endsection


@section('main_content')
    <div class="row">
        <div class="col-md-8  offset-md-2">
            <div class="card w-100">
                <div class="card-header">
                    <p class="card-title">Confirmar desuscripción de mails</p>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <p>Si quiere volver a suscribirse a los mails, por favor, acceda al sitio con su usuario y
                        contraseña, y vaya a la sección "Perfil" del menú</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('partials.footer')
@endsection