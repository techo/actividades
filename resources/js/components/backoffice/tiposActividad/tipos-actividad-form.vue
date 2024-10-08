<template>
    <div class="tipos-actividad-component">
        <div v-show="guardado" class="callout callout-success">
            <h4>{{ mensajeGuardado }}</h4>
        </div>
        <simplert ref="loading"></simplert>

        <div v-show="tieneErrores" class="callout callout-danger">
            <h4>{{ $t('backend.errors') }}:</h4>
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
                                    <label for="nombre">{{ $t('backend.name') }}</label>
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
                                    <label for="pais">{{ $t('backend.category') }}</label>
                                    <v-select
                                            :options="dataCategorias"
                                            label="traduccion"
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
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="pais">{{ $t('backend.image') }}</label>
                                    <div>
                                        <img v-if="tipoActividad.imagen != null" :src="tipoActividad.imagen" alt="imagen actividad">          
                                    </div>
                                    <button v-if="!readonly" class="btn btn-light" @click="updateArchivo = true" ><i class="fa fa-edit"></i></button>
                                    <p v-if="(tipoActividad.imagen == null || updateArchivo)" class="help-block ml-2">{{ $t('backend.image_dimensions_exact') }} 380 x 248.</p>
                                    <input v-if="(tipoActividad.imagen == null || updateArchivo)" type="file" class="form-control" @change="guardar_archivo" ref="imagen">
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
                    imagen: null,
                    imagenNew: null,
                },
                readonly: !this.edicion,
                guardado: false,
                mensajeGuardado: '',
                updateArchivo: false,
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
                    .then((respuesta) => {
                        this.dataCategorias = respuesta.data
                        for (let [i,o] of this.dataCategorias.entries()){
                            this.dataCategorias[i].traduccion = this._i18n.t('frontend.'+o.nombre)
                        }
                    })
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
                const data = new FormData();

                data.append('idTipo', this.tipoActividad.idTipo);
                data.append('nombre', this.tipoActividad.nombre);
                data.append('idCategoria', (this.categoriaSeleccionado)?this.categoriaSeleccionado.id:null);

                if (this.tipoActividad.imagenNew != null)
                    data.append('imagen', this.tipoActividad.imagenNew);

                const headers = { 'Content-Type': 'multipart/form-data' };

                axios.post(url, data, { headers })
                    .then((respuesta) => {
                        this.tipoActividad = respuesta.data;
                        this.mensajeGuardado = 'Registro guardado correctamente';
                        this.guardado = true;
                        this.validationErrors = [];
                        this.$refs.loading.justCloseSimplert();
                        this.readonly = true;
                        window.location.replace('/admin/configuracion/tipos-actividad/'+this.tipoActividad.idTipo);
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
            guardar_archivo(event) {
                this.tipoActividad.imagenNew = this.$refs.imagen.files[0];
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