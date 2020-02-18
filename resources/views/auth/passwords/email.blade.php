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

                        <div class="form-group{{ $errors->has('mail') ? ' has-error' : '' }}">
                            <label for="mail" class="col-md-4 control-label">{{ __('frontend.mail') }}</label>

                            <div class="col-md-6">
                                <input id="mail" type="email" class="form-control" name="mail" value="{{ old('mail') }}" required>

                                @if ($errors->has('mail'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('mail') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('frontend.send_link') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
