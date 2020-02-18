@extends('emails.template')

@section('content')

	<h1>@lang('email.account_registration_title')</h1>

    <p style="font-size: larger">
        @lang('frontend.hello') {{$persona->nombres}},
    </p>

    <p>
        @lang('email.account_registration_1')
    </p>

    <a href="{{ $url_verificacion }}" style="font-family: Montserrat, sans-serif;text-decoration: none; display: inline-block; font-weight: 700; text-align: center; vertical-align: middle; padding: 0.375rem 0.75rem; font-size: 1rem; line-height: 1.5; border-radius: 0.25rem; color: #fff; background-color: #0092DD; border-color: #0092DD;" target="_blank">@lang('email.email_verification')</a>

    <p>
        @lang('email.account_registration_2')<a href="{{ url('/actividades') }}">web</a>.
    </p>

    <p>
        @lang('email.account_registration_3')
        <ul>
        	<li>@lang('email.account_registration_4')</li>
        	<li>@lang('email.account_registration_5')</li>
        	<li>@lang('email.account_registration_6')</li>
        	<li>@lang('email.account_registration_7')</li>
        </ul>
    </p>

    <p>
    	@lang('email.account_registration_8') <a href="{{ url('/perfil') }}">@lang('email.profile')</a> @lang('email.account_registration_9') <a href="{{ url('/perfil/actividades') }}">@lang('email.account_registration_10')</a>.
    </p>
@endsection