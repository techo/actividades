@extends('main')

@section('page_title')
    {{ __('frontend.verify_email') }}
@endsection

@section('main_image')
@endsection

@section('main_content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h3>{{ __('frontend.confirm_your_email') }}</h3>

            <div>
                @if (session('resent'))
                    <div class="alert alert-success" role="alert">
                        {{ __('frontend.verify_email_message_1') }}
                    </div>
                @endif
                <br>
                <p>
                    {{ __('frontend.verify_email_message_2') }}
                </p>
                <p>
                    {{ __('frontend.verify_email_message_3') }} 
                    <br>
                    <br>
                    <a href="{{ route('verification.resend') }}" class="btn btn-primary">{{ __('frontend.verify_email_resend') }} </a>
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