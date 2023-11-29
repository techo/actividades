<template>
    <div class="row">
        <div class="col-md-8" v-if="!mostrarFichaMedica">
          <div v-if="rolAplicado">
                <div class="row">
                    <div class="col-md-12">
                        <h2 class="card-title">{{ $t('frontend.select_a_meeting_point') }}</h2>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <p>{{ $t('frontend.whats_a_meeting_point') }}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <h5 class="card-title">{{ $t('frontend.meeting_points') }}</h5>
                    </div>
                </div>
                <form
                    v-bind:action="'/inscripciones/actividad/' + actividad.idActividad + '/confirmar'"
                    method="POST"
                    v-on:submit="validateForm"
                >
                    <input type="hidden" name="roles_aplicados" v-bind:value="convertToJSON()">

                    <input type="hidden" name="aplica_rol" v-bind:value="aplicaRol">

                <div class="row" v-for="(item, index) in puntosActivos">
                    <div class="col-md-12">
                        <input type="radio" name="punto_encuentro" v-bind:value="item.idPuntoEncuentro"  v-bind:checked="index == 0 ? 'checked' : ''" style="margin: 0px 6px;">
                      {{item.punto}}, 
                      <template v-if="item.localidad">{{ item.localidad.localidad }}, </template> 
                      {{ item.provincia.provincia }} - {{item.horario | format_time}}hs

                      
                        
                    </div>
                </div>
                <hr>
                <div class="row  align-middle">
                    <input type="hidden" name="_token" v-bind:value="csrf_token">
                    <input type="hidden" name="idActividad" id="idActividad" v-bind:value="actividad.idActividad">
                    <div class="col-md-2 text-primary">
                        <p>
                            <a v-bind:href="'/actividades/'+actividad.idActividad" class="btn btn-link"> {{ $t('frontend.go_back') }}</a>
                        </p>
                    </div>
                    <div class="col-md-3"><input type="submit" v-bind:value="$t('frontend.continue')" class="btn btn-primary"></div>
                </div>
                </form>
            </div>
            <div v-else>
                <div class="row">
                    <div class="col-md-12">
                        <h2 class="card-title">{{ $t('frontend.apply_for_rol') }}</h2>
                    </div>
                </div>
                <hr>
                <div class=" pl-0 form-check form-inline mb-2">
                    <input v-model="aplicaRol" class="form-check-input pl-0" type="checkbox"
                        id="aplica-rol">
                    <label class="form-check-label" for="aplica-rol">
                        {{ $t('frontend.yes') }}
                    </label>
                </div>

                <div v-if="aplicaRol" class="row">
                    <div class="col-md-12">
                        <p>{{ $t('frontend.whats_a_rol') }}</p>
                    </div>
                </div>
                <hr>
                <div v-if="aplicaRol" class="row">
                    <div class="col-md-7 mb-5">
                        <input type="text" v-model="dummyInput" style="display: none;"> <!-- Campo de texto invisible -->
        
                        <vue-tags-input
                            v-model="tag"
                            :tags="rolesAplicado"
                            placeholder=""
                            autocomplete="new-password"
                            add-only-from-autocomplete
                            :autocomplete-items="actividad.roles_tags"
                            @tags-changed="newTags => rolesAplicado = newTags"
                        />
                    </div>
                </div>
                <div class="row">
                  <div class="col-md-3 text-primary">
                        <p>
                            <a v-bind:href="'/actividades/'+actividad.idActividad" class="btn btn-link"> {{ $t('frontend.go_back') }}</a>
                        </p>
                  </div>
                  <div class="col-md-3">
                    <button type="button" 
                        class="btn btn-primary" 
                        data-dismiss="modal" 
                        aria-label="Close" 
                        @click="rolAplicado=true" >
                        <span aria-hidden="true">
                            {{ $t('frontend.continue') }}
                        </span>
                    </button>
                  </div>
                </div>
          </div>

        </div>
        <div v-else class="col-md-8" >
          <div class="row">
              <div class="col-md-12">
                  <h2 class="card-title">{{ $t('frontend.ficha_medica') }}</h2>
                  <p>{{ $t('frontend.ficha_medica_requerida') }}</p>
              </div>
          </div>
          <hr>
          <div class="row">
              <div class="col-md-12 px-4">
                <ficha-medica ref="fichaMedica" :fichaMedica="actividad.fichaMedica" :campos="actividad.ficha_medica_campos" @guardado="mostrarFichaMedica = false"/>
              </div>
          </div>
        </div>
        <hr>
        <div class="col-md-4 prev" >
            <div class="card d-none d-lg-block">
                <img :src="imagen" class="img-tarjeta">
                <div class="row">
                    <div class="col-md-12">
                        <h6 v-bind:style="{color:actividad.tipo.color}" >{{ actividad.tipo ? actividad.tipo.nombre : '' }}</h6>
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
                    <div class="col-md-4"><i class="fas fa-map-marker-alt"></i> <span>{{ actividad.ubicacion }}</span>
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
    import VueTagsInput from '@johmun/vue-tags-input';
    export default {
        name: "inscripcion",
        props: ['id','csrf_token'],
        components: { VueTagsInput },
        data: function(){
          return {
            actividad: {
                idActividad: '',
                nombreActividad: '',
                ubicacion: '',
                fecha: '',
                hora: '',
                puntosEncuentro: [],
                tipo: {
                    nombre: ''
                }
            },
            aplicaRol: false,
            rolAplicado: false,
            tag: "",
            rolesAplicado: [],
            mostrarFichaMedica: false,
            localidad: {},
            imagen: '',
            dummyInput: ''
          }
        },
        mounted: function() {
          var self = this;
          window.addEventListener('loggedIn', (event) => {
            this.es_inscripto($('#idActividad').val())
          });
          axios.get('/ajax/actividades/'+this.id).then(function(response){
            self.actividad = response.data.data;
            if(self.actividad.requiere_ficha_medica)
                self.mostrarFichaMedica = true;
            if(self.actividad.roles_tags == null)
                self.rolAplicado = true;
            self.ubicacion = self.actividad.ubicacion;
            self.es_inscripto(self.actividad.idActividad);
            self.imagen = self.actividad.tipo.imagen;
          })
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
        computed: {
            puntosActivos: function () {
                return this.actividad.puntosEncuentro.filter(function (punto) {
                    return punto.estado
                })
            },
            esConstruccion() {
                return this.actividad.tipo ? this.actividad.tipo.flujo == "CONSTRUCCION" : false;
            },
        },
        methods: {
          validateForm: function(event) {
            if(!this.$parent.$refs.login.authenticated) {
              event.preventDefault();
              this.mostrarLogin();
            }
            return true;
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
          },
            convertToJSON: function() {
                return JSON.stringify(this.rolesAplicado);
            }
        }
    }
</script>

<style scoped>
    .card {
        border: none;
    }

    .img-tarjeta {
        margin-bottom: 1em;
    }

    .prev h6 {
        font-weight: 700 !important;
    }
</style>