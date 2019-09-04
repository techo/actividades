@extends('main')

@section('page_title')
    Cambiar email
@endsection

@section('main_image')
@endsection

@section('main_content')
	<div class="row">
                <div class="col-md-5">
                    <h5>Cambiar mi casilla de email</h5>
                </div>
            </div>
            <div class="row">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="mail" id="email" v-model="user.email">
                    <small class="form-text">Al cambiar la contraseña es necesario verificar la nueva dirección de email y se pierden las asociaciones a redes sociales.</small>  
                    <small class="form-text"></small>  
                </div>
                <div class="col-md-2">
                    <span v-bind:class="{'d-none':!validacion.email.valido}"><i class="fas fa-check text-success"></i></span>
                    <span v-bind:class="{'d-none':!validacion.email.invalido}"><i class="fas fa-times text-danger"></i></span>
                </div>

            </div>
@endsection

@section('footer')
    @include('partials.footer')
@endsection

@section('additional_scripts')
@endsection
