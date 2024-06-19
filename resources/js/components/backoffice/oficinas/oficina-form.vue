<template>
    <div class="oficina-component">
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
                                           v-model="oficina.nombre"
                                           :disabled="readonly"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="pais">{{ $t('backend.country') }}</label>
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
        name: "oficina-form",
        props: ['id', 'edicion'],
        components: {},
        data(){
            return {
                oficina: {
                    id: null,
                    nombre: "",
                    id_pais: null,
                },
                readonly: !this.edicion,
                guardado: false,
                mensajeGuardado: '',
                validationErrors: {},
                dataPaises: [],
                paisSeleccionado: {},
            }
        },
        created(){
            let data = {};

            this.getPaises();
            if(this.id)
            {
                this.getOficina(this.id);
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
            oficina: function() {
                this.paisSeleccionado = this.dataPaises.find((o) => {
                    return o.id == this.oficina.id_pais
                });
            }
        },
        methods: {
            editar(){
                this.readonly = false;
            },
            getPaises: function () {
                axios.get("/ajax/paises/propios")
                    .then((respuesta) => {this.dataPaises = respuesta.data})
                    .catch(() => {debugger});
            },
            getOficina(id){
                axios.get("/admin/ajax/configuracion/oficinas/"+id)
                    .then((respuesta) => {this.oficina = respuesta.data})
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

                if (this.oficina.id === undefined || this.oficina.id === null) {
                    url = `/admin/configuracion/oficinas/registrar`;
                } else {
                    url = `/admin/configuracion/oficinas/${encodeURI(this.oficina.id)}/editar`;
                }

                this.oficina.id_pais = (this.paisSeleccionado)?this.paisSeleccionado.id:null;

                axios.post(url, this.oficina)
                    .then((respuesta) => {
                        this.oficina = respuesta.data;
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
