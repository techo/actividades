@extends('main')

@section('main_content')
    <div class="row">
        <div class="col-md-8  offset-md-2">
            <div class="card w-100">
                <div class="card-header">
                    <p class="card-title">{{ __('frontend.reset_password') }}</p>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form class="form-inline" method="POST" action="{{ route('password.email') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('mail') ? ' has-error' : '' }} col-md-8">
                            <label for="mail" class="col-md-3 control-label">{{ __('frontend.mail') }}</label>

                            <div class="col-md-3">
                                <input id="mail" type="email" class="form-control" name="mail" value="{{ old('mail') }}" required>

                                @if ($errors->has('mail'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('mail') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">
                                {{ __('frontend.send_link') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
