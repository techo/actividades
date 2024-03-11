@extends('emails.template')

@section('content')
    <p>@lang('frontend.hello')</p>
    <p>@lang('email.forgot_password_1')
    </p>

    <p>
        <a href="{{ url('password/reset', $token) }}" style="font-family: Fredoka, Montserrat, sans-serif;text-decoration: none; display: inline-block; font-weight: 700; text-align: center; vertical-align: middle; padding: 0.375rem 0.75rem; font-size: 1rem; line-height: 1.5; border-radius: 0.25rem; color: #fff; background-color: #0092DD; border-color: #0092DD;" target="_blank" >@lang('email.forgot_password_link')</a>
    </p>

    <p>
        @lang('email.forgot_password_2')
    </p>
@endsection

