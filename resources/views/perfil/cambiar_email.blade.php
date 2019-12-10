@extends('main')

@section('page_title')
    {{ __('change_email') }}
@endsection

@section('main_image')
@endsection

@section('main_content')
<div>
    <div class="row">
        <div class="col-md-12">
            <h2>{{ __('welcome') }}, {{ $usuario->nombres}} ({{ $usuario->mail }})</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            {{ __('frontend.change_email_title') }}
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-5">
        	<br>
            {{ __('frontend.change_email_title_2') }}
            <ul>
            	<li>{{ __('frontend.change_email_req_1') }}</li>
            	<li>{{ __('frontend.change_email_req_2') }}</li>  
            	<li>{{ __('frontend.change_email_req_3') }}</li>
            </ul>
            
        	<form method="POST" action="/perfil/actualizar_email" >
        		@csrf

                <label>{{ __('frontend.change_email_new_email') }}</label>
            	<input type="text" class="form-control" name="email" value="{{ old('email') }}">
                <br>
                <label>{{ __('frontend.change_email_new_email_confirmation') }}</label>
                <input type="text" class="form-control" name="email_confirmation" value="{{ old('email_confirmation') }}">
            	<br>
                <p>{{ __('frontend.change_email_are_you_sure') }}</p>
            	<input type="submit" class="btn btn-primary" name="enviar" value="{{ __('frontend.change_email_confirm_button') }}" > 
            </form>
            <br>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
    <br>
    
    
    
</div>
@endsection

@section('footer')
    @include('partials.footer')
@endsection

@section('additional_scripts')
@endsection
