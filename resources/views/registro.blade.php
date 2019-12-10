@extends('main')

@section('page_title')
    {{ __('frontend.register') }}
@endsection

@section('main_image')
@endsection

@section('main_content')
	@if(isset($persona))
		<registro
				nombre="{{$persona->nombre}}"
                apellido="{{$persona->apellido}}"
                sexo="{{$persona->sexo}}"
                email="{{$persona->email}}"
                facebook_id="{{$persona->facebook_id}}"
                google_id="{{$persona->google_id}}"
                linkear={{isset($linkear)?$linkear:''}}
        ></registro>
	@else
        @if(isset($mensaje))
            <div class="alert alert-danger">
                <strong>{{$mensaje}}</strong>
            </div>
        @endif
		<registro></registro>
	@endif
@endsection

@section('footer')
    @include('partials.footer')
@endsection

@section('additional_scripts')
@endsection
