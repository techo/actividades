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
            <div class="card">
                <div class="card-header">{{ __('Verificar dirección de email') }}</div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('Se envió un enlace de verificación a tu casilla de email.') }}
                        </div>
                    @endif

                    {{ __('Antes de seguir, verificá tu email por un enlace de verificación.') }}
                    {{ __('Si no recibiste el mail') }}, <a href="{{ route('verification.resend') }}">{{ __('hacé click para solicitar otro') }}</a>.
                </div>
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