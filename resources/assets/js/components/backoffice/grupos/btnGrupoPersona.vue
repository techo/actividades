<template>
    <div>
        <p class="alert alert-danger" v-if="mensajeError !== ''">{{ mensajeError }}</p>
        <simplert ref="loading"></simplert>
        <div class="btn-group" v-show="edit">
            <a class="btn btn-primary btn-sm" @click="verFormGrupo">Grupo</a>
            <a class="btn btn-primary btn-sm" @click="verFormInscripto">Voluntario Inscripto</a>
            <a class="btn btn-primary btn-sm" @click="verFormNoInscripto">Voluntario No Inscripto</a>
        </div>
        <div  v-if="formGrupo" class="panel panel-info">
            <div class="panel-heading">Agregar Nuevo Grupo</div>
            <div class="panel-body">
                <form>
                    <div class="row">
                        <div class="col-md-6">
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
                        <div class="col-md-6" style="padding-top: 1.5em">
                            <button
                                    type="button"
                                    class="btn btn-default pull-right  btnSeparador"
                                    @click="cancelar"
                            >
                                <i class="fa fa-ban"></i> Cancelar
                            </button>
                            <button
                                    type="button"
                                    class="btn btn-primary pull-right btnSeparador"
                                    @click="guardarGrupo"
                            >
                                <i class="fa fa-plus"></i> Incluir y Agregar Nuevo
                            </button>
                            <button
                                    type="button"
                                    class="btn btn-primary pull-right btnSeparador"
                                    @click="guardarGrupoCerrar"
                            >
                                <i class="fa fa-check"></i> Incluir y Cerrar
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
                    <div class="col-md-8">
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
                    <div class="row">
                        <div class="col-md-12 ">
                            <button type="button" class="btn btn-default pull-right  btnSeparador" @click="cancelar">
                                <i class="fa fa-ban"></i> Cancelar
                            </button>
                            <button type="button" class="btn btn-primary pull-right btnSeparador" @click="guardarInscripto">
                                <i class="fa fa-plus"></i> Incluir y Agregar Nuevo
                            </button>
                            <button type="button" class="btn btn-primary pull-right btnSeparador" @click="guardarInscriptoCerrar">
                                <i class="fa fa-check"></i> Incluir y Cerrar
                            </button>
                        </div>
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
                    <div class="col-md-6">
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
                </div>
                <div class="row">
                    <div class="col-md-12 ">
                        <button type="button" class="btn btn-default pull-right btnSeparador" @click="cancelar">
                            <i class="fa fa-ban"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-primary pull-right btnSeparador" @click="guardarNoInscripto">
                            <i class="fa fa-plus"></i> Incluir y Agregar Nuevo
                        </button>
                        <button type="button" class="btn btn-primary pull-right btnSeparador" @click="guardarNoInscriptoCerrar">
                            <i class="fa fa-check"></i> Incluir y Cerrar
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
                mensajeError: ''
            }
        },
        created: function() {
            // cargar puntos de encuentro
            Event.$on('Miembros:guardado', this.confirmarGuardado);
            Event.$on('Miembros:voluntario-duplicado', this.voluntarioDuplicado);
            Event.$on('error', this.error);
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
            guardarGrupoCerrar: function () {
              this.guardarGrupo();
              this.cancelar();
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
            guardarInscriptoCerrar: function() {
                this.guardarInscripto();
                this.cancelar();
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
            guardarNoInscriptoCerrar: function () { debugger;
                this.guardarNoInscripto();
                this.cancelar();
            },
            confirmarGuardado: function () {
                Event.$emit('vuetable-actualizarTabla');
                this.inicializar();
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
                let payload = '';
                this.axiosGet(url, function(response, self) {
                    self.puntosEncuentro = response;
                }, payload, function () {
                    this.mensajeError = 'Ocurrió un error al recuperar los puntos de encuentro. ' +
                        'Recarga la página o intentalo de nuevo maś tarde.  ' +
                        'Si el error persiste, comunícalo al administrador del sistema';
                });
                this.formGrupo = false;
                this.formNoInscripto = true;
                this.formInscripto = false;
            },
            cancelar: function () {
                this.formGrupo = false;
                this.formInscripto = false;
                this.formNoInscripto = false;
                this.inicializar();
            },
            inicializar: function() {
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
                this.mensajeError ='';
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
            voluntarioDuplicado: function () {
                this.yaInscripto = true;
                this.$refs.loading.justCloseSimplert();
            },
            onSearchInscripto: function (text, loading) {
                loading(true);
                this.searchInscripto(loading, text, this);
            },
            searchInscripto: function(loading, text) {
                if (text.length > 2) {
                    let url = '/admin/ajax/actividades/' + encodeURI(this.dataActividad.idActividad) + '/grupos/getInscriptos?inscriptos=' + encodeURI(text);
                    let payload = { inscriptos: encodeURI(text)};
                    this.axiosGet(url, function (response, self) {
                        self.listadoInscriptos = [];
                        self.mensajeError = '';
                        for (let i = 0, len = response.length; i < len; i++) {
                            let nombre = response[i].nombres + ' ' + response[i].apellidoPaterno;
                            let id = response[i].idPersona;
                            self.listadoInscriptos.unshift({idPersona: id, nombre: nombre});
                        }
                        loading(false);
                    }, payload, function (response, self) {
                        loading(false);
                        self.mensajeError = 'Ocurrió un error al recuperar el listado de inscriptos. ' +
                            'Recarga la página o intenta de nuevo más tarde, y si el error persiste, comunícalo al ' +
                            'administrador del sistema.';
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
                    let payload = { coordinador: encodeURI(text) };
                    this.axiosGet(url, function (response, vm){
                        vm.listadoNoInscriptos = [];
                        for (let i = 0, len = response.data.length; i < len; i++) {
                            vm.listadoNoInscriptos.unshift(
                                    {idPersona: response.data[i].idPersona, nombre: response.data[i].nombre}
                                );
                        }
                        loading(false);
                    }, payload, function (response, self) {
                        loading(false);
                        self.mensajeError = 'Ocurrió un error al recuperar el listado de voluntarios. ' +
                            'Recarga la página o intenta de nuevo más tarde, y si el error persiste, comunícalo al ' +
                            'administrador del sistema.';
                    });
                }
            },
            error() {
                this.$refs.loading.justCloseSimplert();
            }
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

    .btnSeparador {
        margin-left: 5px;
        margin-right: 5px
    }
</style>