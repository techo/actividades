<template>
    <div class="row">
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="card-title">Elegir un punto de encuentro</h2>
                </div>
            </div>
            <hr>

            <div class="row">
                <div class="col-md-12">
                    <h5 class="card-title">Que es un punto de encuentro?</h5>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                    tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <h5 class="card-title">Puntos de encuentro</h5>
                </div>
            </div>
            <form v-bind:action="'/inscripciones/actividad/'+actividad.idActividad+'/confirmar'" method="POST" v-on:submit="validateForm">
              <div class="row" v-for="(item, index) in actividad.puntosEncuentro">
                  <div class="col-md-1">
                      <input type="radio" name="punto_encuentro" v-bind:value="item.idPuntoEncuentro"  v-bind:checked="index == 0 ? 'checked' : ''">
                  </div>
                  <div class="col-md-11">
                    {{item.punto}} - {{item.horario | format_time}}
                  </div>
              </div>
              <div class="row  align-middle">
                  <input type="hidden" name="_token" v-bind:value="csrf_token">
                  <input type="hidden" name="idActividad" id="idActividad" v-bind:value="actividad.idActividad">
                  <div class="col-md-3 text-primary"><i class="fas fa-long-arrow-alt-left "></i><a v-bind:href="'/actividades/'+actividad.idActividad"> Volver</a></div>
                  <div class="col-md-3"><input type="submit" value="SIGUIENTE" class="btn btn-primary"></div>
              </div>
            </form>
        </div>
        <div class="col-md-4">
            <div class="card">
                <img src="https://placeholdit.co/i/555x150?bg=d3d3d3">
                <div class="row">
                    <div class="col-md-12">
                        <h6>{{ actividad.tipo ? actividad.tipo.nombre : '' }}</h6>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <h5>{{ actividad.nombreActividad }}</h5>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-4"><i class="far fa-calendar"></i>
                        <span>{{ actividad.fecha}}</span></div>
                    <div class="col-md-4"><i class="far fa-clock"></i>
                        <span>{{ actividad.hora }}</span></div>
                    <div class="col-md-4"><i class="fas fa-map-marker-alt"></i> <span>{{ actividad.lugar }}</span>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-12">{{ actividad.descripcion }}</div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    export default {
        name: "inscripcion",
        props: ['id','csrf_token'],
        data: function(){
          return {
            actividad: {},
            autenticado: false
          }
        },
        mounted: function() {
          var self = this;
          axios.get('/ajax/actividades/'+this.id).then(function(response){
            self.actividad = response.data.data;
            self.es_inscripto(self.actividad.idActividad);
          })
          //this.es_autenticado();
        },
        filters: {
          format_time: function(hora) {
            if(hora.match(/^\d+:\d/)) {
              var arr = hora.split(':');
              hora = arr[0]+':'+arr[1];
            }
            return hora
          }
        },
        methods: {
          validateForm: function(event) {
            if(!this.autenticado) {
              event.preventDefault();
              this.mostrarLogin();
            }
            return true;
          },
          es_autenticado: function () {
            axios.get('/autenticado').then(response => {
              window.addEventListener('loggedIn', (event) => {
                this.autenticado = true
                this.es_inscripto($('#idActividad').val())
              });
              if(response.data == 'no') {
                this.mostrarLogin()
              }
            })
          },
          mostrarLogin: function () {
            $('#btnShowModal').trigger('click')
          },
          es_inscripto: function (idActividad) {
            axios.get('/inscripciones/actividad/'+idActividad+'/inscripto').then(response => {
              if(response.data.idActividad) {
                window.location.href = '/actividades/' + response.data.idActividad
              }
            });
          }
        }
    }
</script>