@extends('main')

@section('page_title')
    Verificar email
@endsection

@section('main_image')
@endsection

@section('main_content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h3>{{ __('Verificar dirección de email') }}</h3>

            <div>
                @if (session('resent'))
                    <div class="alert alert-success" role="alert">
                        {{ __('Se envió un enlace de verificación a tu casilla de email.') }}
                    </div>
                @endif
                <br>
                <p>
                    {{ __('Para poder seguir, verificá tu casilla de email y hacé click en el enlace de verificación que te enviamos.') }}
                </p>
                <p>
                    {{ __('Si no recibiste el mail') }} 
                    <br>
                    <br>
                    <a href="{{ route('verification.resend') }}" class="btn btn-primary">{{ __('hacé click para solicitar otro') }} </a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer')
    @include('partials.footer')
@endsection

@section('additional_scripts')
@endsection