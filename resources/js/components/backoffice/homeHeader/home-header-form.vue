<template>
    <div class="home-header-component">
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
                                    <label for="header">Header</label>
                                    <input id="header"
                                           type="text"
                                           class="form-control"
                                           v-model="header.header"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="pais">Imagen {{ homeHeader.header }}</label>
                                    <div>
                                        <img v-if="homeHeader.imagen != null" :src="homeHeader.imagen" alt="imagen actividad">          
                                    </div>
                                    <button v-if="!readonly" class="btn btn-light" @click="updateArchivo = true" ><i class="fa fa-edit"></i></button>
                                    <input v-if="(homeHeader.imagen == null || updateArchivo)" type="file" class="form-control" @change="guardar_archivo" ref="imagen">
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
    export default {
        name: "home-header-form",
        props: ['homeHeader', 'edicion'],
        components: {},
        data(){
            return {
                tipoActividad: {
                    idHomeHeader: null,
                    header: "",
                    imagen: null,
                    imagenNew: null,
                },
                header: {
                    idHomeHeader: null,
                    header: "hols",
                    imagen: null,
                    imagenNew: null,
                },
                readonly: !this.edicion,
                guardado: false,
                mensajeGuardado: '',
                updateArchivo: false,
                validationErrors: {},
            }
        },
        created(){
            Event.$on('guardar', this.guardar);
            Event.$on('eliminar', this.eliminar);
            Event.$on('editar', this.editar);
           // this.header = this.homeHeader;
            console.log(this.homeHeader['header']);
            console.log(this.header.idPais);
        },
        computed: {
            tieneErrores: function () {
                return (this.validationErrors.length > 0);
            },
        },
        methods: {
            editar(){
                this.readonly = false;
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