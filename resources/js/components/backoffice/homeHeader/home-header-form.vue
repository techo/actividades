<template>
    <div class="home-header-component">
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
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="header">{{ $t('backend.header') }}</label>
                                    <input id="header"
                                           type="text"
                                           class="form-control"
                                           v-model="homeHeader.header"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="header">Sub Header</label>
                                    <input id="header"
                                           type="text"
                                           class="form-control"
                                           v-model="homeHeader.subHeader"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="pais">{{ $t('backend.image') }}</label>
                                    <div>
                                        <img class="img-responsive" v-if="homeHeader.imagen != null" :src="homeHeader.imagen" alt="imagen actividad">          
                                    </div>
                                    <button v-if="!readonly" class="btn btn-light" @click="updateArchivo = true" ><i class="fa fa-edit"></i></button>
                                    <p v-if="updateArchivo" class="help-block ml-2">{{ $t('backend.image_dimensions_exact') }} 1366 x 210.</p>
                                    
                                    <input v-if="updateArchivo" type="file" class="form-control" @change="guardar_archivo" ref="imagen">
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
        props: ['idHomeHeader','header', 'subHeader', 'imagen', 'edicion'],
        components: {},
        data(){
            return {
                homeHeader: {
                    idHomeHeader: null,
                    header: "",
                    subHeader: "",
                    imagen: null,
                },
                imagenNew: null,
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
            this.homeHeader.idHomeHeader = this.idHomeHeader;
            this.homeHeader.header = this.header;
            this.homeHeader.subHeader = this.subHeader;
            this.homeHeader.imagen = this.imagen;
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
                // this.mostrarLoadingAlert();
                this.validationErrors = [];
                if (this.homeHeader.idHomeHeader === undefined || this.homeHeader.idHomeHeader === null) {
                    url = `/admin/ajax/configuracion/home-header/registrar`;
                } else {
                    url = `/admin/ajax/configuracion/home-header/${encodeURI(this.homeHeader.idHomeHeader)}/editar`;
                }
                const data = new FormData();

                data.append('idHomeHeader', this.homeHeader.idHomeHeader);
                data.append('header', this.homeHeader.header);
                data.append('subHeader', this.homeHeader.subHeader);

                if (this.imagenNew != null)
                    data.append('imagen', this.imagenNew);

                const headers = { 'Content-Type': 'multipart/form-data' };

                axios.post(url, data, { headers })
                    .then((respuesta) => {
                        this.homeHeader = respuesta.data;
                        this.mensajeGuardado = 'Registro guardado correctamente';
                        this.guardado = true;
                        this.validationErrors = [];
                        this.$refs.loading.justCloseSimplert();
                        this.ocultarLoadingAlert();
                        window.location.replace('/admin/configuracion/home-header');
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
                this.imagenNew = this.$refs.imagen.files[0];
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