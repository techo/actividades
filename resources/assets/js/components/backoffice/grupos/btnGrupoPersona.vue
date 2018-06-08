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
                                    :filterable=false
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
                loading: false,
                nombreGrupo: '',
                listadoInscriptos: [],
                inscripto: null,
                rol: ''
            }
        },
        created: function() {
            Event.$on('guardado', this.confirmarGuardado)
        },
        methods: {
            guardarGrupo: function () {
                this.mostrarLoadingAlert();
                Event.$emit('guardar-grupo', this.nombreGrupo);
            },
            guardarInscripto: function () {
                this.mostrarLoadingAlert();
                let payload = {
                  inscripto: this.inscripto,
                  rol: this.rol
                };
                Event.$emit('guardar-inscripto', payload);
            },
            confirmarGuardado: function () {
                this.nombreGrupo = '';
                this.rol = '';
                this.inscripto = null;
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
            onSearch: function (text, loading) {
                loading(true);
                this.search(loading, text, this);
            },
            search: _.debounce((loading, search, vm) => {
                fetch(
                    `/ajax/coordinadores?coordinador=${encodeURI(search)}`
                ).then(res => {
                    res.json().then(json => (vm.listadoInscriptos = json.data));
                    loading(false);
                });
            }, 1000),
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