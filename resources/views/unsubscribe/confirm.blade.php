@extends('main')

@section('page_title')
    Confirmar desuscripción
@endsection


@section('main_content')
    <div class="row">
        <div class="col-md-8  offset-md-2">
            <div class="card w-100">
                <div class="card-header">
                    <p class="card-title">Confirmar desuscripción de mails</p>
                </div>

                <div class="card-body">
                    <form action="{{ route('unsubscribe.confirmar', ['uuid' => $token]) }}" method="POST">
                        {{ csrf_field() }}
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="email" class="control-label">Correo Electrónico</label>
                                    <input id="email" type="email" class="form-control" name="mail" value="{{ old('mail') }}" required>
                                </div>
                                @if ($errors->has('mail'))
                                    <p class="text-danger"><small>{{ $errors->first('mail') }}</small></p>
                                @endif
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="form-group">
                                    <br>
                                    <button type="submit" class="btn btn-primary">
                                        Confirmar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('partials.footer')
@endsection
