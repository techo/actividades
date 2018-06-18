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
                Agregar <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li><a @click="verFormGrupo">Grupo</a></li>
                <li role="separator" class="divider"></li>
                <li><a @click="verFormInscripto">Voluntario Inscripto</a></li>
            </ul>
        </div>
        <div  v-if="formGrupo" class="panel panel-info">
            <div class="panel-heading">Agregar Nuevo Grupo</div>
            <div class="panel-body">
                <form class="form-inline">
                    <div class="form-group">
                        <label for="nombre">Nombre </label>
                        <input
                                type="text"
                                class="form-control"
                                id="nombre"
                                placeholder="Escribe el nombre del grupo"
                                v-model="nombreGrupo"
                        >
                    </div>
                    <button type="button" class="btn btn-primary" @click="guardarGrupo">
                        <i class="fa fa-check"></i> Agregar
                    </button>
                    <button type="button" class="btn btn-default" @click="cancelar">
                        <i class="fa fa-ban"></i> Cancelar
                    </button>
                </form>
            </div>
        </div>
        <div v-if="formInscripto" class="panel panel-info">
            <div class="panel-heading">Agregar Voluntario Inscripto</div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="nombre">Nombre </label>
                            <v-select
                                    :options="listadoInscriptos"
                                    label="nombre"
                                    placeholder="Escribe el nombre o apellido"
                                    name="inscripto"
                                    id="inscripto"
                                    v-model="inscripto"
                                    :filterable=true
                                    @search="onSearch"
                            >
                            </v-select>
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
                loading: false,
                nombreGrupo: '',
                listadoInscriptos: [],
                inscripto: null,
                rol: '',
                yaInscripto: false
            }
        },
        created: function() {
            Event.$on('Miembros:guardado', this.confirmarGuardado);
            Event.$on('Miembros:voluntario-duplicado', this.voluntarioDuplicado);
        },
        methods: {
            guardarGrupo: function () {
                this.mostrarLoadingAlert();
                Event.$emit('btnGrupoPersona:guardar-grupo', this.nombreGrupo);
            },
            guardarInscripto: function () {
                this.mostrarLoadingAlert();
                let payload = {
                  inscripto: this.inscripto,
                  rol: this.rol
                };
                Event.$emit('btnGrupoPersona:guardar-inscripto', payload);
            },
            confirmarGuardado: function () {
                this.nombreGrupo = '';
                this.rol = '';
                this.inscripto = null;
                this.listadoInscriptos = [];
                this.yaInscripto = false;
                this.ocultarLoadingAlert();
            },
            verFormGrupo: function () {
                this.formGrupo = true;
                this.formInscripto = false;
            },
            verFormInscripto: function () {
                this.formGrupo = false;
                this.formInscripto = true;
            },
            cancelar: function () {
                this.formGrupo = false;
                this.formInscripto = false;
                this.nombreGrupo = '';
                this.inscripto = null;
                this.listadoInscriptos = [];
                this.rol = '';
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
                this.yaInscripto = true; console.info(grupo);
                this.$refs.loading.justCloseSimplert();
            },
            onSearch: function (text, loading) {
                loading(true);
                this.search(loading, text, this);
            },
            search: function(loading, text, vm) {
                if (text.length >= 3) {
                    let url = '/admin/ajax/actividades/' + encodeURI(this.dataActividad.idActividad) + '/grupos/getInscriptos?inscriptos=' + encodeURI(text);
                    this.axiosGet(url, function (response, self){
                        self.listadoInscriptos = [];
                        for (var i = 0, len = response.length; i < len; i++) {
                            let nombre = response[i].nombres + ' ' + response[i].apellidoPaterno;
                            let id = response[i].idPersona;
                            self.listadoInscriptos.unshift({idPersona: id, nombre: nombre});
                        }
                        loading(false)
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

/*
            search: _.debounce((loading, search, vm) => { console.info('search:' + search);
                //let url = '/admin/ajax/actividades/' + encodeURI(vm.dataActividad.idActividad) + '/grupos/getInscriptos?inscriptos=' + encodeURI(search);
                //console.info('url:' + url);
                fetch(
                    //'/admin/ajax/actividades/' + encodeURI(vm.dataActividad.idActividad) + '/grupos/getInscriptos?inscriptos=' + encodeURI(search)
                    '/admin/ajax/actividades/12983/grupos/getInscriptos?inscriptos=sara'
                    //url
                ).then(res => { console.log(JSON.stringify(res)); debugger;
                    res.json().then(json => (vm.listadoInscriptos = json.data));
                    loading(false);
                });
            }, 1000),
*/
        },
        computed: {
            edit: function () {
                return ( !this.formGrupo && !this.formInscripto);
            }
        }
    }
</script>

<style scoped>

</style>