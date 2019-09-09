@extends('main')

@section('page_title')
    Cambiar email
@endsection

@section('main_image')
@endsection

@section('main_content')
<div>
    <div class="row">
        <div class="col-md-12">
            <h2>Bienvenido, {{ $usuario->nombres}} ({{ $usuario->mail }})</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            Aquí podrás realizar cambios en tu casilla de email.
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-5">
        	<br>
            Tené en cuenta que:
            <ul>
            	<li>Es necesario verificar la nueva dirección de email</li>
            	<li>Se pierden las asociaciones a redes sociales.</li>  
            	<li>Tenés que volver a iniciar sesión.</li>
            </ul>
            
        	<form method="POST" action="/perfil/actualizar_email" >
        		@csrf

                <label>Nueva casilla</label>
            	<input type="text" class="form-control" name="email" value="{{ old('email') }}">
                <br>
                <label>Volvé a ingresar la casilla para confirmar</label>
                <input type="text" class="form-control" name="email_confirmation" value="{{ old('email_confirmation') }}">
            	<br>
                <p>¿Estás seguro?</p>
            	<input type="submit" class="btn btn-primary" name="enviar" value="Si, cambiar" > 
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
