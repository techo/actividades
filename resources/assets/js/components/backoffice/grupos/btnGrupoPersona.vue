<template>
    <div>
        <simplert ref="loading"></simplert>
        <div class="btn-group" v-show="edit">
            <button
                    type="button"
                    class="btn btn-primary dropdown-toggle"
                    data-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
            >
                Agregar a este grupo <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li><a @click="verFormGrupo">Grupo</a></li>
                <li role="separator" class="divider"></li>
                <li><a @click="verFormInscripto">Voluntario Inscripto</a></li>
                <li><a @click="verFormNoInscripto">Voluntario No Inscripto</a></li>
            </ul>
        </div>
        <div  v-if="formGrupo" class="panel panel-info">
            <div class="panel-heading">Agregar Nuevo Grupo</div>
            <div class="panel-body">
                <form>
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group" :class="{'has-error': nombreGrupoError}">
                            <label for="nombre">Nombre </label>
                            <input
                                    type="text"
                                    class="form-control"
                                    id="nombre"
                                    placeholder="Escribe el nombre del grupo"
                                    v-model="nombreGrupo"

                            >
                            <p class="red" v-show="nombreGrupoError">Este campo es requerido</p>
                        </div>
                    </div>
                    <div class="col-md-2" style="padding-top: 1.5em">
                        <button type="button" class="btn btn-primary" @click="guardarGrupo">
                            <i class="fa fa-check"></i> Agregar
                        </button>
                        <button type="button" class="btn btn-default" @click="cancelar">
                            <i class="fa fa-ban"></i> Cancelar
                        </button>
                    </div>
                </div>
                </form>
            </div>
        </div>
        <div v-if="formInscripto" class="panel panel-info">
            <div class="panel-heading">Agregar Voluntario Inscripto</div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group" :class="{'has-error': inscriptoNombreError }">
                            <label for="nombre">Nombre </label>
                            <v-select
                                    :options="listadoInscriptos"
                                    label="nombre"
                                    placeholder="Escribe el nombre o apellido"
                                    name="inscripto"
                                    id="inscripto"
                                    v-model="inscripto"
                                    :filterable=true
                                    @search="onSearchInscripto"
                            >
                            </v-select>
                            <p class="red" v-show="inscriptoNombreError">Este campo es requerido</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="rol">Rol </label>
                            <input type="text" class="form-control" v-model="rol" id="rol">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <br>
                        <button type="button" class="btn btn-primary" @click="guardarInscripto">
                            <i class="fa fa-check"></i> Agregar
                        </button>
                        <button type="button" class="btn btn-default" @click="cancelar">
                            <i class="fa fa-ban"></i> Cancelar
                        </button>
                    </div>

                </div>
                <p class="text-danger" v-if="yaInscripto">
                    <i>Esta persona ya pertenece a otro equipo.</i>
                </p>
            </div>
        </div>
        <div v-if="formNoInscripto" class="panel panel-info">
            <div class="panel-heading">Agregar Voluntario No Inscripto</div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group" :class="{'has-error': noInscriptoNombreError}">
                            <label for="nombre">Nombre </label>
                            <v-select
                                    :options="listadoNoInscriptos"
                                    label="nombre"
                                    placeholder="Escribe el nombre, apellido o DNI"
                                    name="noInscripto"
                                    id="noInscripto"
                                    v-model="noInscripto"
                                    :filterable=true
                                    @search="onSearchNoInscripto"
                            >
                            </v-select>
                            <p class="red" v-show="noInscriptoNombreError">Este campo es requerido</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="rol">Rol </label>
                            <input type="text" class="form-control" v-model="rol" id="rol">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group" :class="{'has-error': noInscriptoPuntoError}">
                            <label for="puntoEncuentro">Punto de Encuentro </label>
                            <select class="form-control" v-model="idPuntoSeleccionado" id="puntoEncuentro">
                                <option v-for="punto in puntosEncuentro" :value="punto.id">{{ punto.punto }}, {{ punto.localidad}}</option>
                            </select>
                            <p class="red" v-show="noInscriptoPuntoError">Este campo es requerido</p>
                        </div>
                    </div>
                    <div class="col-md-2" style="padding-top: 1.5em">
                        <button type="button" class="btn btn-primary" @click="guardarNoInscripto">
                            <i class="fa fa-check"></i> Agregar
                        </button>
                        <button type="button" class="btn btn-default" @click="cancelar">
                            <i class="fa fa-ban"></i> Cancelar
                        </button>
                    </div>

                </div>
                <p class="text-danger" v-if="yaInscripto">
                    <i>Esta persona ya pertenece a otro equipo.</i>
                </p>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    export default {
        name: "btnGrupoPersona",
        props: {
          actividad: {
              default: ''
          }
        },
        data: function () {
            return {
                dataActividad: JSON.parse(this.actividad),
                formGrupo: false,
                formInscripto: false,
                formNoInscripto: false,
                loading: false,
                nombreGrupo: '',
                listadoInscriptos: [],
                listadoNoInscriptos: [],
                inscripto: null,
                noInscripto: null,
                rol: '',
                yaInscripto: false,
                puntosEncuentro: [],
                idPuntoSeleccionado: '',
                nombreGrupoError: false,
                inscriptoNombreError: false,
                noInscriptoNombreError: false,
                noInscriptoPuntoError: false,
            }
        },
        created: function() {
            // cargar puntos de encuentro
            Event.$on('Miembros:guardado', this.confirmarGuardado);
            Event.$on('Miembros:voluntario-duplicado', this.voluntarioDuplicado);
        },
        methods: {
            guardarGrupo: function () {
                if (this.nombreGrupo.length > 0) {
                    this.nombreGrupoError = false;
                    this.mostrarLoadingAlert();
                    Event.$emit('btnGrupoPersona:guardar-grupo', this.nombreGrupo);
                } else {
                    this.nombreGrupoError = true;
                }
            },
            guardarInscripto: function () {
                if (this.inscripto) {
                    this.mostrarLoadingAlert();
                    let payload = {
                        inscripto: this.inscripto,
                        rol: this.rol
                    };
                    Event.$emit('btnGrupoPersona:guardar-inscripto', payload);
                } else {
                    this.inscriptoNombreError = true;
                }
            },
            guardarNoInscripto: function () {
                if (this.noInscripto && this.idPuntoSeleccionado) {
                    this.mostrarLoadingAlert();
                    let payload = {
                        noInscripto: this.noInscripto,
                        rol: this.rol,
                        idPuntoEncuentro: this.idPuntoSeleccionado
                    };
                    Event.$emit('btnGrupoPersona:guardar-no-inscripto', payload);
                }

                if (!this.noInscripto) {
                    this.noInscriptoNombreError = true;
                }

                if (!this.idPuntoSeleccionado) {
                    this.noInscriptoPuntoError = true;
                }
            },
            confirmarGuardado: function () {
                this.nombreGrupo = '';
                this.nombreGrupoError = false;
                this.rol = '';
                this.inscripto = null;
                this.noInscripto = null;
                this.listadoInscriptos = [];
                this.listadoNoInscriptos = [];
                this.yaInscripto = false;
                this.inscriptoNombreError = false;
                this.noInscriptoNombreError = false;
                this.noInscriptoPuntoError = false;
                this.ocultarLoadingAlert();
            },
            verFormGrupo: function () {
                this.formGrupo = true;
                this.formInscripto = false;
                this.formNoInscripto = false;
            },
            verFormInscripto: function () {
                this.formGrupo = false;
                this.formInscripto = true;
                this.formNoInscripto = false;
            },
            verFormNoInscripto: function () {
                let url = '/admin/ajax/actividades/'+ this.dataActividad.idActividad +'/puntos';
                this.axiosGet(url, function(response, self) {
                    self.puntosEncuentro = response;
                });
                this.formGrupo = false;
                this.formNoInscripto = true;
                this.formInscripto = false;
            },
            cancelar: function () {
                this.formGrupo = false;
                this.formInscripto = false;
                this.formNoInscripto = false;
                this.nombreGrupo = '';
                this.nombreGrupoError = false;
                this.inscripto = null;
                this.noInscripto = null;
                this.listadoInscriptos = [];
                this.idPuntoSeleccionado = '';
                this.rol = '';
                this.inscriptoNombreError = false;
                this.noInscriptoNombreError = false;
                this.noInscriptoPuntoError = false;
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
            voluntarioDuplicado: function (grupo) {
                this.yaInscripto = true;
                this.$refs.loading.justCloseSimplert();
            },
            onSearchInscripto: function (text, loading) {
                loading(true);
                this.searchInscripto(loading, text, this);
            },
            searchInscripto: function(loading, text, vm) {
                if (text.length > 2) {
                    let url = '/admin/ajax/actividades/' + encodeURI(this.dataActividad.idActividad) + '/grupos/getInscriptos?inscriptos=' + encodeURI(text);
                    this.axiosGet(url, function (response, self){
                        self.listadoInscriptos = [];
                        for (let i = 0, len = response.length; i < len; i++) {
                            let nombre = response[i].nombres + ' ' + response[i].apellidoPaterno;
                            let id = response[i].idPersona;
                            self.listadoInscriptos.unshift({idPersona: id, nombre: nombre});
                        }
                        loading(false);
                    });
                }
            },
            onSearchNoInscripto: function (text, loading) {
                loading(true);
                this.searchNoInscripto(loading, text, this);
            },
            searchNoInscripto: function(loading, text, vm) {
                if (text.length > 2) {
                    let url = "/ajax/coordinadores?coordinador=" + encodeURI(text);
                    this.axiosGet(url, function (response, vm){
                        vm.listadoNoInscriptos = [];
                        for (let i = 0, len = response.data.length; i < len; i++) {
                            vm.listadoNoInscriptos.unshift(
                                    {idPersona: response.data[i].idPersona, nombre: response.data[i].nombre}
                                );
                        }
                        loading(false);
                    });
                }
            },
            axiosGet(url, fCallback, params = []) {
                this.loading = true;
                axios.get(url, params)
                    .then(response => {
                        fCallback(response.data, this);
                        this.loading = false;

                    })
                    .catch((error) => {
                        this.loading = false;
                        // Error
                        console.error('Error en el get: ' + url);
                        if (error.response) {
                            // The request was made and the server responded with a status code
                            // that falls out of the range of 2xx
                            console.error(error.response.data);
                            console.error(error.response.status);
                            console.error(error.response.headers);
                        } else if (error.request) {
                            // The request was made but no response was received
                            // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
                            // http.ClientRequest in node.js
                            console.error(error.request);
                        } else {
                            // Something happened in setting up the request that triggered an Error
                            console.error('Error', error.message);
                        }
                        console.error(error.config);
                    });
            },
        },
        computed: {
            edit: function () {
                return ( !this.formGrupo && !this.formInscripto && !this.formNoInscripto);
            }
        }
    }
</script>

<style scoped>
.red {
    color: red;
    font-size: smaller;
}
</style>