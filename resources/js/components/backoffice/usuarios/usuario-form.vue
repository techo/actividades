<template>
    <div class="usuario-component">
        <div v-show="guardado" class="callout callout-success">
            <h4>{{ mensajeGuardado }}</h4>
        </div>
        <simplert ref="loading"></simplert>
        <fusionar-modal ref="fusionar" :persona="usuario"></fusionar-modal>

        <div v-show="tieneErrores" class="callout callout-danger">
            <h4>Errores:</h4>
            <ul>
                <li v-for="error in validationErrors">{{ error }}</li>
            </ul>
        </div>
        <div class="box">
            <!--<div class="box-header with-border">-->
            <!--<h3 class="box-title">Registrar usuario</h3>-->
            <!--</div>-->
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-5 text-center">
                                <img v-if="usuario.photo" class="imagen-perfil-redonda" :src="'/'+usuario.photo" alt="Foto">
                                <img v-else src="/bower_components/admin-lte/dist/img/user_avatar.png" class="imagen-perfil-redonda" alt="User Image">
                            </div>
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label for="nombre">{{ $t('backend.name') }}</label>
                                    <input id="nombre"
                                           type="text"
                                           class="form-control"
                                           v-model="usuario.nombre"
                                           :disabled="readonly"
                                    >
                                </div>
                                <div class="form-group">
                                    <label for="apellido">{{ $t('backend.last_name') }}</label>
                                    <input id="apellido"
                                           type="text"
                                           class="form-control"
                                           v-model="usuario.apellido"
                                           :disabled="readonly"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="mail">{{ $t('frontend.email') }}</label>
                                    <input id="mail"
                                           type="mail"
                                           class="form-control"
                                           :disabled="readonly"
                                    >
                                </div>
                                <div class="form-group">
                                    <label>{{ $t('backend.identifications') }}</label>
                                    <input type="text"
                                           class="form-control"
                                           v-model="usuario.dni"
                                           name="dni"
                                           id="dni"
                                           :disabled="readonly"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nacimiento">{{ $t('backend.birthdate') }}</label>
                                    <br>
                                    <p v-if="readonly">{{ fechaNacimiento }}</p>
                                    <date-picker v-else
                                            v-model="usuario.nacimiento"
                                            name="nacimiento"
                                            id="nacimiento"
                                            lang="es"
                                            format="DD/MM/YYYY"
                                            :placeholder="$t('backend.select_date')"
                                    ></date-picker>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="genero">{{ $t('backend.gender') }}</label>
                                    <br>
                                    <v-select
                                            :options="dataGeneros"
                                            label="genero"
                                            :placeholder="$t('backend.select')"
                                            name="genero"
                                            id="genero"
                                            v-model="usuario.genero"
                                            v-bind:disabled="readonly"
                                            :filterable=false
                                    >
                                    <span slot="no-options"></span>
                                    </v-select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="telefono">{{ $t('backend.phone') }}</label>
                                    <input type="text"
                                           class="form-control"
                                           name="telefono"
                                           id="telefono"
                                           v-model="usuario.telefono"
                                           :disabled="readonly"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="rol">{{ $t('backend.role') }}</label>
                                    <v-select
                                            :options="dataRoles"
                                            label="rol"
                                            :placeholder="$t('backend.select')"
                                            name="rol"
                                            id="rol"
                                            v-model="usuario.rol"
                                            :disabled="this.readonly"
                                    >
                                    <span slot="no-options"></span>
                                    </v-select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="estadoPersona">{{ $t('backend.state') }}</label>
                                    <v-select
                                            :options="dataEstados"
                                            label="estadoPersona"
                                            :placeholder="$t('backend.select')"
                                            name="estadoPersona"
                                            id="estadoPersona"
                                            v-model="usuario.estadoPersona"
                                            :disabled="this.readonly"
                                    >
                                    <span slot="no-options"></span>
                                    </v-select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="pais">{{ $t('backend.country') }}</label>
                                    <v-select
                                            :options="dataPaises"
                                            label="nombre"
                                            :placeholder="$t('backend.select')"
                                            name="pais"
                                            id="pais"
                                            v-model="paisSeleccionado"
                                            v-bind:disabled="this.readonly"
                                    >
                                    <span slot="no-options"></span>
                                    </v-select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="provincia">{{ $t('backend.province') }}</label>
                                    <v-select
                                            :options="dataProvincias"
                                            label="provincia"
                                            :placeholder="$t('backend.select')"
                                            name="provincia"
                                            id="provincia"
                                            v-model="provinciaSeleccionada"
                                            v-bind:disabled="this.readonly"
                                    >
                                    <span slot="no-options"></span>
                                    </v-select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="localidad">{{ $t('backend.location') }}</label>
                                    <v-select
                                            :options="dataLocalidades"
                                            label="localidad"
                                            :placeholder="$t('backend.select')"
                                            name="localidad"
                                            id="localidad"
                                            v-model="localidadSeleccionada"
                                            v-bind:disabled="this.readonly"
                                    >
                                    <span slot="no-options"></span>
                                    </v-select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="canal_contacto">{{ $t('backend.contact_channel') }}</label>
                                    <select  :placeholder="$t('backend.select')" :disabled="this.readonly" id="canal_contacto" v-model="usuario.canal_contacto" class="form-control">
                                        <option v-bind:value="$t('frontend.social_networks')"> {{ $t('frontend.social_networks') }} </option>
                                        <option v-bind:value="$t('frontend.advertisement_traditional_media')"> {{ $t('frontend.advertisement_traditional_media') }} </option>
                                        <option v-bind:value="$t('frontend.outdoor_advertising')"> {{ $t('frontend.outdoor_advertising') }} </option>
                                        <option v-bind:value="$t('frontend.website')"> {{ $t('frontend.website') }} </option>
                                        <option v-bind:value="$t('frontend.known_person')"> {{ $t('frontend.known_person') }} </option>
                                        <option v-bind:value="$t('frontend.email_campaign')"> {{ $t('frontend.email_campaign') }} </option>
                                        <option v-bind:value="$t('frontend.street_intervention')"> {{ $t('frontend.street_intervention') }} </option>
                                        <option v-bind:value="$t('frontend.event_collection_volunteer_campaign')"> {{ $t('frontend.event_collection_volunteer_campaign') }} </option>    
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="localidad">{{ $t('backend.verification') }}</label>
                                    <br>
                                    <v-switch
                                        theme="bootstrap" 
                                        color="primary"
                                        v-bind:disabled="readonly"
                                        v-model="usuario.email_verified_at"
                                    ></v-switch>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import DatePicker from 'vue2-datepicker';
    import moment from 'moment';
    import vSwitch from 'vue-switches';
    import fusionarModal from '../../../components/backoffice/usuarios/usuarios-fusionar-modal';

    export default {
        name: "usuario-form",
        props: ['propUsuario', 'edicion'],
        components: {'date-picker': DatePicker, 'v-switch': vSwitch, 'fusionar-modal': fusionarModal},
        data(){
            return {
                usuario: {
                    idUsuario: null,
                    email: "",
                    nombre: "",
                    apellido: "",
                    genero: "",
                    nacimiento: "",
                    telefono: "",
                    pais: null,
                    provincia: null,
                    localidad: null,
                    canal_contacto: "",
                    dni: "",
                    rol: null,
                    email_verified_at: null,
                    canal_contacto: null,
                },
                dataGeneros: [
                    {id: "M", genero: "Masculino"},
                    {id: "F", genero: "Femenino"},
                    {id: "X", genero: "Otro"},
                ],
                dataEstados: [
                    "Habilitado",
                    "Suspendido",
                    "Desvinculado",
                ],
                dataPaises: [],
                dataRoles: [],
                dataProvincias: [],
                dataLocalidades: [],
                paisSeleccionado: null,
                provinciaSeleccionada: null,
                localidadSeleccionada: null,
                readonly: !this.edicion,
                guardado: false,
                mensajeGuardado: '',
                validationErrors: {}
            }
        },
        created(){
            let data = {};

            this.getPaises();
            this.getRoles();

            if(this.propUsuario){
                this.usuario = JSON.parse(this.propUsuario);

                for (var i in Object.keys(this.dataGeneros)) 
                {
                    if(this.dataGeneros[i].id == this.usuario.genero) 
                        this.usuario.genero = this.dataGeneros[i];
                }

                this.paisSeleccionado = this.usuario.pais;
                this.provinciaSeleccionada = this.usuario.provincia;
                this.localidadSeleccionada = this.usuario.localidad;
            }



            Event.$on('guardar', this.guardar);
            Event.$on('eliminar', this.eliminar);
            Event.$on('fusionar', this.fusionar);
            Event.$on('editar', this.editar);
        },
        computed: {
            tieneErrores: function () {
                return (this.validationErrors.length > 0);
            },
            fechaNacimiento: function () {
                return moment(this.usuario.nacimiento).locale("es").format("LL");
            }
        },
        watch: {
            paisSeleccionado: function (pais, paisAnterior) {
                if (pais !== null) {
                    this.axiosGet('/ajax/paises/' + pais.id + '/provincias',
                        function (data, self) {
                            self.dataProvincias = data;
                        });
                    if(paisAnterior != null)
                        this.provinciaSeleccionada = null;
                    this.usuario.pais = this.paisSeleccionado;
                } else {
                    this.provinciaSeleccionada = null;
                    this.dataProvincias = [];
                }
            },
            provinciaSeleccionada: function (provincia) {
                if (provincia !== null) {
                    this.axiosGet('/ajax/paises/' + this.paisSeleccionado.id + '/provincias/' + provincia.id + '/localidades',
                        function (data, self) {
                            self.dataLocalidades = data;
                        });
                    this.usuario.provincia = this.provinciaSeleccionada;
                } else {
                    this.usuario.provincia = null;
                    this.localidadSeleccionada = null;
                    this.dataLocalidades = [];
                }
            },
            localidadSeleccionada: function (localidad) {
                if (localidad !== null) {
                    this.usuario.localidad = this.localidadSeleccionada;
                }
                else
                    this.usuario.localidad = null;
            }
        },
        methods: {
            editar(){
                this.readonly = false;
            },
            getPaises: function () {
                let url = "/ajax/paises";
                this.axiosGet(
                    url,
                    function (data, self) {
                        self.dataPaises = data;
                    }
                )
            },
            getRoles: function() {
              let url = window.location.origin + "/admin/ajax/roles";
              this.axiosGet(
                  url,
                  function (data, self) {
                      self.dataRoles = data;
                  }
              );
            },
            mostrarLoadingAlert() {
                this.$refs.loading.openSimplert({
                    title: 'Espera...',
                    message: "<i class=\"fa fa-spinner fa-spin fa-4x\"></i>",
                    hideAllButton: true,
                    isShown: true,
                    disableOverlayClick: true,
                    type: ''
                })
            },
            ocultarLoadingAlert: function () {
                this.$refs.loading.justCloseSimplert();
            },
            guardar(){
                let url;
                this.mostrarLoadingAlert();
                this.validationErrors = [];
                window.scrollTo(0, 0);

                if (this.usuario.idUsuario === undefined || this.usuario.idUsuario === null) {
                    url = `/admin/usuarios/registrar`;
                } else {
                    url = `/admin/usuarios/${encodeURI(this.usuario.idUsuario)}/editar`;
                }

                this.axiosPost(url, //endpoint
                    function (data, self) { //handler de success
                        if (self.usuario.idUsuario === null) {
                            window.location.replace('/admin/usuarios');
                        }
                        self.mensajeGuardado = data[0];
                        self.guardado = true;
                        self.validationErrors = [];
                        self.$refs.loading.justCloseSimplert();
                    },
                    this.usuario, // request data
                    function (error, self) { //handler de error
                        self.ocultarLoadingAlert();
                        // Error
                        if (error.response) {
                            if (error.response.status === 422) {
                                self.validationErrors = Object.values(error.response.data);
                            }
                        }
                    });

            },
            eliminar(){
                let form = document.getElementById('formDelete');
                form.submit();
            },
            fusionar(){
                console.log("fusionar");
                this.$refs.fusionar.show();
            }
        }
    }
</script>

<style scoped>
    @media (max-width: 768px) {
        .usuario-component {
            margin-bottom: 120px;
        }
    }
</style>
