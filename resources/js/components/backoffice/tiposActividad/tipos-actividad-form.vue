<template>
    <div class="tipos-actividad-component">
        <div v-show="guardado" class="callout callout-success">
            <h4>{{ mensajeGuardado }}</h4>
        </div>
        <simplert ref="loading"></simplert>

        <div v-show="tieneErrores" class="callout callout-danger">
            <h4>Errores:</h4>
            <ul>
                <li v-for="error in validationErrors">{{ error[0] }}</li>
            </ul>
        </div>
        <div class="box">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="nombre">Nombre</label>
                                    <input id="nombre"
                                           type="text"
                                           class="form-control"
                                           v-model="tipoActividad.nombre"
                                           :disabled="readonly"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="pais">Categoría</label>
                                    <v-select
                                            :options="dataCategorias"
                                            label="nombre"
                                            placeholder="Seleccione"
                                            name="categoria"
                                            id="categoria"
                                            v-model="categoriaSeleccionado"
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
    import vSwitch from 'vue-switches';
    export default {
        name: "tipos-actividad-form",
        props: ['id', 'edicion'],
        components: {},
        data(){
            return {
                tipoActividad: {
                    idTipo: null,
                    nombre: "",
                    idCategoria: null,
                },
                readonly: !this.edicion,
                guardado: false,
                mensajeGuardado: '',
                validationErrors: {},
                dataCategorias: [],
                categoriaSeleccionado: {},
            }
        },
        created(){
            let data = {};
            this.getCategorias();
            if(this.id)
            {
                this.getTipoActividad(this.id);
            }
            Event.$on('guardar', this.guardar);
            Event.$on('eliminar', this.eliminar);
            Event.$on('editar', this.editar);
        },
        computed: {
            tieneErrores: function () {
                return (this.validationErrors.length > 0);
            },
        },
        watch: {
            tipoActividad: function() {
                this.categoriaSeleccionado = this.dataCategorias.find((o) => {
                    return o.id == this.tipoActividad.idCategoria
                });
            }
        },
        methods: {
            editar(){
                this.readonly = false;
            },
            getCategorias: function () {
                axios.get("/ajax/categorias")
                    .then((respuesta) => {this.dataCategorias = respuesta.data})
                    .catch(() => {debugger});
            },
            getTipoActividad(id){
                axios.get("/admin/ajax/configuracion/tipos-actividad/"+id)
                    .then((respuesta) => {this.tipoActividad = respuesta.data})
                    .catch(() => {debugger});
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
                if (this.tipoActividad.idTipo === undefined || this.tipoActividad.idTipo === null) {
                    url = `/admin/ajax/configuracion/tipos-actividad/registrar`;
                } else {
                    url = `/admin/ajax/configuracion/tipos-actividad/${encodeURI(this.tipoActividad.idTipo)}/editar`;
                }
                this.tipoActividad.idCategoria = (this.categoriaSeleccionado)?this.categoriaSeleccionado.id:null;
                axios.post(url, this.tipoActividad)
                    .then((respuesta) => {
                        this.tipoActividad = respuesta.data;
                        this.mensajeGuardado = 'Registro guardado correctamente';
                        this.guardado = true;
                        this.validationErrors = [];
                        this.$refs.loading.justCloseSimplert();
                        this.readonly = true;
                        window.location.replace('/admin/configuracion/tipos-actividad');
                    })
                    .catch((error) => { 
                        this.ocultarLoadingAlert();
                        if (error.response) {
                            if (error.response.status === 422) {
                                this.validationErrors = Object.values(error.response.data.errors);
                                Event.$emit('error');
                               
                            }
                        }});
            },
            eliminar(){
                let form = document.getElementById('formDelete');
                form.submit();
            },
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