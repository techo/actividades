<template>
    <div class="institucion-educativa-component">
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
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="nombre">Nombre</label>
                                    <input id="nombre" type="text" class="form-control" v-model="data.nombre"
                                        :disabled="this.readonly">
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
    name: "institucion-educativa-form",
    props: ['institucionEducativa', 'edicion'],
    components: {},
    data() {
        return {
            data: {
                nombre: "",
            },
            guardado: false,
            mensajeGuardado: '',
            validationErrors: {},
            readonly: !this.edicion,
            errors: {},
        }
    },
    created() {
        if (this.institucionEducativa) {
            this.data = this.institucionEducativa;
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

    methods: {
        editar() {
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
        guardar() {
            if(this.data.idInstitucionEducativa){
                this.update();
            } else {
                this.crear();
            }
        },
        update() {
            let url;
            this.mostrarLoadingAlert();
            this.validationErrors = [];
            url = `/admin/configuracion/institucionEducativa/`+this.data.idInstitucionEducativa;
            axios.put(url, this.data)
                .then((respuesta) => {
                    this.mensajeGuardado = 'Registro guardado correctamente';
                    this.guardado = true;
                    this.validationErrors = [];
                    this.$refs.loading.justCloseSimplert();
                    this.readonly = true;
                })
                .catch((error) => {
                    this.ocultarLoadingAlert();
                    if (error.response) {
                        if (error.response.status === 422) {
                            this.validationErrors = Object.values(error.response.data.errors);
                            Event.$emit('error');

                        }
                    }
                });
        },
        crear() {
            let url;
            this.mostrarLoadingAlert();
            this.validationErrors = [];
            url = `/admin/configuracion/institucionEducativa/registrar`;
            axios.post(url, this.data)
                .then((respuesta) => {
                    this.data = respuesta.data;
                    this.mensajeGuardado = 'Registro guardado correctamente';
                    this.guardado = true;
                    this.validationErrors = [];
                    this.$refs.loading.justCloseSimplert();
                    this.readonly = true;
                    window.location.replace('/admin/configuracion/institucionEducativa/' + respuesta.data.idInstitucionEducativa);
                })
                .catch((error) => {
                    this.ocultarLoadingAlert();
                    if (error.response) {
                        if (error.response.status === 422) {
                            this.validationErrors = Object.values(error.response.data.errors);
                            Event.$emit('error');

                        }
                    }
                });
        },
        eliminar() {
            let form = document.getElementById('formDelete');
            form.submit();
        },
    }
}
</script>

<style scoped>
@media (max-width: 768px) {
    .institucion-educativa-component {
        margin-bottom: 120px;
    }
}
</style>