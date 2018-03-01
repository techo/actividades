@extends('main')

@section('page_title')
    Listado de Actividades
@endsection

@section('main_image')
    <div class="techo-hero">
        <img src="/img/hero-slim.jpg" alt="hero image" height="210">
        <h2>Inscríbete y acompáñanos con tu voluntariado</h2>
    </div>
@endsection

@section('main_content')
    <div class="row" id="filtro">
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-4">
                    <select name="categorias" v-on:change="verActividades">
                        <option value="1">Actividades en Asentamientos</option>
                        <option value="2">Eventos Especiales</option>
                        <option value="3">Actividades en Oficina</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="tipos">
                        <option value="">Todas las actividades</option>
                        <option v-for="actividad in actividades"value="{{{ actividad.idTipo }}}">{{{ actividad.nombre }}}</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="zonas">
                        <option value="">Todas las regiones</option>
                        <option value="1">Buenos Aires</option>
                        <option value="2">Capital Federal</option>
                        <option value="3">Otro</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="row">
                <div class="col-md-6">
                    <button class="btn btn-danger btn-sm">Borra Filtros</button>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="card-deck mb-3 text-center">
            <tarjeta v-for="act in actividades" v-bind:actividad="act"></tarjeta>
        </div>
    </div>
    <div v-show="loading">Cargando...</div>
@endsection

@section('additional_scripts')
<script>
    var filtro = new Vue({
        el: '#filtro',
        data: {
            actividades: []
        },
        methods: {
            verActividades: function () {
                axios.get('/ajax/tipos/'+ $('select[name="categorias"]').val())
                    .then(response => {
                        console.log(response);
                    });
            }
        },
        created(){
            this.verActividades();
        }
    })
</script>
@endsection
