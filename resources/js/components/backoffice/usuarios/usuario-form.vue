<template>
    <div class="usuario-component">
        <div v-show="guardado" class="callout callout-success">
            <h4>{{ mensajeGuardado }}</h4>
        </div>
        <simplert ref="loading"></simplert>

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
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input id="email"
                                           type="email"
                                           class="form-control"
                                           v-model="usuario.email"
                                           :disabled="readonly"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>DNI / Pasaporte</label>
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
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="nombre">Nombre</label>
                                    <input id="nombre"
                                           type="text"
                                           class="form-control"
                                           v-model="usuario.nombre"
                                           :disabled="readonly"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="apellido">Apellido</label>
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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nacimiento">Fecha de nacimiento</label>
                                    <br>
                                    <p v-if="readonly">{{ fechaNacimiento }}</p>
                                    <date-picker v-else
                                            v-model="usuario.nacimiento"
                                            name="nacimiento"
                                            id="nacimiento"
                                            lang="es"
                                            format="DD/MM/YYYY"
                                    ></date-picker>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sexo">Género</label>
                                    <br>
                                    <v-select
                                            :options="dataGeneros"
                                            label="genero"
                                            placeholder="Seleccione"
                                            name="sexo"
                                            id="sexo"
                                            v-model="usuario.sexo"
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
                                    <label for="telefono">Teléfono</label>
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
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="rol">Rol</label>
                                    <v-select
                                            :options="dataRoles"
                                            label="rol"
                                            placeholder="Seleccione"
                                            name="rol"
                                            id="rol"
                                            v-model="usuario.rol"
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
                                    <label for="pais">País</label>
                                    <v-select
                                            :options="dataPaises"
                                            label="nombre"
                                            placeholder="Seleccione"
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
                                    <label for="provincia">Provincia</label>
                                    <v-select
                                            :options="dataProvincias"
                                            label="provincia"
                                            placeholder="Seleccione"
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
                                    <label for="localidad">Localidad</label>
                                    <v-select
                                            :options="dataLocalidades"
                                            label="localidad"
                                            placeholder="Seleccione"
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import DatePicker from 'vue2-datepicker';
    import moment from 'moment';

    export default {
        name: "usuario-form",
        props: ['propUsuario', 'edicion'],
        components: {'date-picker': DatePicker},
        data(){
            return {
                usuario: {
                    idUsuario: null,
                    email: "",
                    nombre: "",
                    apellido: "",
                    sexo: "",
                    nacimiento: "",
                    telefono: "",
                    pais: null,
                    provincia: null,
                    localidad: null,
                    dni: "",
                    rol: null,
                },
                dataGeneros: [
                    {id: "M", genero: "Masculino"},
                    {id: "F", genero: "Femenino"},
                    {id: "O", genero: "Prefiero no decirlo"},
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
                    if(this.dataGeneros[i].id == this.usuario.sexo) 
                        this.usuario.sexo = this.dataGeneros[i];
                }

                this.paisSeleccionado = this.usuario.pais;
                this.provinciaSeleccionada = this.usuario.provincia;
                this.localidadSeleccionada = this.usuario.localidad;
            }



            Event.$on('guardar', this.guardar);
            Event.$on('eliminar', this.eliminar);
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
                console.log(pais, paisAnterior);
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