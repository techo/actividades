<template>
    <div class="row">
        <div class="col-md-8" v-if="!mostrarFichaMedica">
          <div v-if="rolAplicado && tipoInscriptoAplicado && estudiosAplicado">
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
                   
                    <input type="hidden" name="inscripciones_aplicadas" v-bind:value="convertToJSONInscripciones()">

                <div class="row" v-for="(item, index) in puntosActivos">
                    <div class="col-md-12">
                        <input type="radio" name="punto_encuentro" v-bind:value="item.idPuntoEncuentro"  v-bind:checked="index == 0 ? 'checked' : ''" style="margin: 0px 6px;">
                      {{item.punto}}, 
                      <template v-if="item.localidad">{{ item.localidad.localidad }}, </template> 
                      {{ item.provincia.provincia }} - {{item.horario | format_time}}hs

                      
                        
                    </div>
                </div>
                <hr>
                <div class="row text-center justify-content-center">
                    <input type="hidden" name="_token" v-bind:value="csrf_token">
                    <input type="hidden" name="idActividad" id="idActividad" v-bind:value="actividad.idActividad">
                    <!-- <div class="col-md-2 text-primary">
                        <p>
                            <a v-bind:href="'/actividades/'+actividad.idActividad" class="btn btn-link"> {{ $t('frontend.go_back') }}</a>
                        </p>
                    </div> -->
                    <div class="col-md-3"><input type="submit" v-bind:value="$t('frontend.continue')" class="btn btn-primary" v-if="validateForm()"></div>
                </div>
                </form>
            </div>
            <div v-else-if="!rolAplicado">

                <h2 class="card-title text-center">{{ $t('frontend.apply_for_rol') }}</h2>
            
                <div class="card-body"> 
                    <p>{{ $t('frontend.whats_a_rol') }}</p>   
                    
                    <vue-tags-input
                        v-model="tag"
                        :tags="rolesAplicado"
                        placeholder=""
                        class="h-100"
                        autocomplete="new-password"
                        add-only-from-autocomplete
                        :autocomplete-items="actividad.roles_tags"
                        @tags-changed="newTags => rolesAplicado = newTags"
                    /> 
                    <b>{{ $t('frontend.leave_blank') }}</b>    
                </div>

                <div class="card-footer">
                    <div class="row justify-content-center  text-center">
                        <!-- <div class="col-md-3 text-primary">
                            <p>
                                <a v-bind:href="'/actividades/'+actividad.idActividad" class="btn btn-link"> {{ $t('frontend.go_back') }}</a>
                            </p>
                        </div> -->
                        <div class="col-md-3">
                            <button type="button" 
                                class="btn btn-primary" 
                                data-dismiss="modal" 
                                aria-label="Close" 
                                @click="if(validateForm())rolAplicado=true;" >
                                <span aria-hidden="true">
                                    {{ $t('frontend.continue') }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else-if="!tipoInscriptoAplicado"x>
                <h2 class="card-title text-center">{{ $t('frontend.type_of_inscription') }}</h2>

                <div class="card-body">
                    <p>{{ $t('frontend.whats_a_type_inscription') }}</p>   
                    <vue-tags-input
                        v-model="tag2"
                        :tags="tipoInscriptoTags"
                        placeholder=""
                        autocomplete="new-password"
                        add-only-from-autocomplete
                        :open-on-focus="true"
                        :autocomplete-items="actividad.tipo_inscriptos_tag"
                        @tags-changed="newTags => tipoInscriptoTags = newTags"
                    />
                </div>
                <div class="card-footer">
                    <div class="row justify-content-center  text-center">
                        <div class="col-md-3">
                            <!-- <button type="button" 
                                class="btn btn-link" 
                                data-dismiss="modal" 
                                aria-label="Close" 
                                @click="rolAplicado=false" >
                                <span aria-hidden="true">
                                    {{ $t('frontend.go_back') }}
                                </span>
                            </button> -->
                        </div>
                        <div class="col-md-3">
                            <button type="button" 
                                class="btn btn-primary" 
                                data-dismiss="modal" 
                                aria-label="Close" 
                                @click="if(validateForm()) tipoInscriptoAplicado=true" >
                                <span aria-hidden="true">
                                    {{ $t('frontend.continue') }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else-if="!estudiosAplicado">
                <h2 class="card-title text-center">{{ $t('frontend.estudios') }}</h2>

                <div class="card-body">
                    <p>{{ $t('frontend.review_estudios') }}</p>   
                    <estudios ref="estudios" :estudios="actividad.estudios" :idPersona="actividad.idPersona"/>

                </div>
                <div class="card-footer">
                    <div class="row alert alert-info m-2" v-show='showCompleteEstudios'>
                            <strong>{{ $t('frontend.complete_education') }}</strong>
                        </div>
                    <div class="row justify-content-center  text-center">
                        <!-- <div class="col-md-3">
                            <button type="button" 
                                class="btn btn-link" 
                                data-dismiss="modal" 
                                aria-label="Close" 
                                @click="tipoInscriptoAplicado=false" >
                                <span aria-hidden="true">
                                    {{ $t('frontend.go_back') }}
                                </span>
                            </button>
                        </div> -->
                        <div class="col-md-3">
                            <button type="button" 
                                class="btn btn-primary" 
                                data-dismiss="modal" 
                                aria-label="Close" 
                                @click="if(validateForm())  estudiosRevisados();" >
                                <span aria-hidden="true">
                                    {{ $t('frontend.continue') }}
                                </span>
                            </button>
                        </div>
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
                    <ficha-medica ref="fichaMedica" :fichaMedica="actividad.fichaMedica" :campos="actividad.ficha_medica_campos" @guardado="validateForm();mostrarFichaMedica = false;"/>
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
                
                <div v-if="actividad.show_dates || actividad.show_location"  class="row">
                    <hr>
                    <div v-if="actividad.show_dates" class="col-md-4"><i class="far fa-calendar"></i>
                        <span>{{ actividad.fecha}}</span></div>
                    <div v-if="actividad.show_dates" class="col-md-4"><i class="far fa-clock"></i>
                        <span>{{ actividad.hora }}</span></div>
                    <div v-if="actividad.show_location"  class="col-md-4">
                        <i class="fas fa-map-marker-alt"></i> <span>{{ actividad.ubicacion }}</span>
                    </div>
                </div>
                <!-- <hr>
                <div class="row">
                    <div class="col-md-12">{{ actividad.descripcion }}</div>
                </div> -->

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
            tipoInscriptoAplicado: false,
            estudiosAplicado: true,
            showCompleteEstudios: false,
            tag: "",
            tag2: "",
            rolesAplicado: [],
            tipoInscriptoTags: [],
            mostrarFichaMedica: false,
            localidad: {},
            imagen: '',
            dummyInput: '',
          }
        },
        
        watch: {
            rolAplicado: function(newVal, oldVal) {
                if (newVal  && this.tipoInscriptoAplicado && this.estudiosAplicado && !this.mostrarFichaMedica){
                    this.checkSubmit();
                }
            },
            tipoInscriptoAplicado: function(newVal, oldVal) {
                if (newVal  && this.rolAplicado && this.estudiosAplicado && !this.mostrarFichaMedica){
                    this.checkSubmit();
                }
            },
            estudiosAplicado: function(newVal, oldVal) {
                if (newVal  && this.rolAplicado && this.tipoInscriptoAplicado && !this.mostrarFichaMedica){
                    this.checkSubmit();
                }
            },
            mostrarFichaMedica: function(newVal, oldVal) {
                if (!newVal  && this.tipoInscriptoAplicado && this.estudiosAplicado && this.rolAplicado){
                    this.checkSubmit();
                }
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
            if(self.actividad.roles_tags == null || self.actividad.roles_tags.length == 0)
                self.rolAplicado = true;
            if(self.actividad.tipo_inscriptos_tag == null || self.actividad.tipo_inscriptos_tag.length == 0)
                self.tipoInscriptoAplicado = true;
            if(self.actividad.requiere_estudios)
                self.estudiosAplicado = false;
            self.ubicacion = self.actividad.ubicacion;
            self.es_inscripto(self.actividad.idActividad);
            self.imagen = self.actividad.tipo.imagen;
          });
          this.validateForm();
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
                // event.preventDefault();
                this.mostrarLogin();
                return false
                }
                return true;
            },
            checkSubmit: function() {
                if(this.actividad.puntosEncuentro.length == 1){
                    const formData = new FormData();
                    formData.append('roles_aplicados', this.convertToJSON());
                    formData.append('aplica_rol', this.aplicaRol);
                    formData.append('inscripciones_aplicadas', this.convertToJSONInscripciones());
                
                    formData.append('punto_encuentro',  this.actividad.puntosEncuentro[0].idPuntoEncuentro);
                    const headers = { 'Content-Type': 'multipart/form-data' };

                    axios.post('/inscripciones/actividad/' + this.actividad.idActividad + '/confirmar', formData).then(response => {
                        this.validateForm();
                        document.getElementById('app').innerHTML = response.data; // Suponiendo que 'app' es el ID de tu contenedor principal en el que se muestra la vista
                
                    }).catch((error) => {
                    });
                }
                
                        
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
            },
            convertToJSONInscripciones: function() {
                return JSON.stringify(this.tipoInscriptoTags);
            },
            estudiosRevisados: function () {
                axios.get('/admin/ajax/usuarios/'+this.actividad.idPersona+'/estudios').then(response => {
                    if(response.data) {
                        if(response.data.total>0)
                            this.estudiosAplicado = true;
                        else
                            this.showCompleteEstudios = true;
                    }
                });
            },
            
        }
    }
</script>

<style scoped>
    .card {
        border: none;
    }

    .img-tarjeta {
        margin-bottom: 1em;
        width: 100%;
    }

    .prev h6 {
        font-weight: 700 !important;
    }
</style>