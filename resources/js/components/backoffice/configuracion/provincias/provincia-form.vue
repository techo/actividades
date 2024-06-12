<template>
    <div class="provincia-component">
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
                                    <label for="nombre">{{ $t('backend.name') }}</label>
                                    <input id="nombre" type="text" class="form-control" v-model="data.nombre"
                                        :disabled="this.readonly">
                                </div>
                            </div>
                        </div>    
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="idOficina">{{ $t('backend.office') }}</label>
                                    <v-select :disabled="this.readonly" :options="dataOficinas" label="descripcion" placeholder="Seleccione"
                                        name="oficina" id="oficina" v-model="oficinaSeleccionado"
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
    name: "provincia-form",
    props: ['provincia', 'edicion'],
    components: {},
    data() {
        return {
            data: {
                nombre: "",
                idOficina: null,
            },
            guardado: false,
            mensajeGuardado: '',
            validationErrors: {},
            dataOficinas: [],
            oficinaSeleccionado: {},
            readonly: !this.edicion,
            errors: {},
        }
    },
    created() {
        this.getOficinas();
        if (this.provincia) {
            this.data = this.provincia;
            this.data.nombre = this.provincia.provincia
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
        oficinaSeleccionado: function () {
            if (this.oficinaSeleccionado)
                this.data.idOficina = this.oficinaSeleccionado.id;
        },
    },

    methods: {
        editar() {
            this.oficinaSeleccionado = Object.values(this.dataOficinas).find(oficina => oficina.id === this.data.idOficina);
            this.readonly = false;
        },
        getOficinas: function () {
            axios.get("/admin/ajax/oficinas")
                .then((respuesta) => {
                    this.dataOficinas = respuesta.data
                    for (let [i, o] of this.dataOficinas.entries()) {
                        this.dataOficinas[i].descripcion = o.nombre
                    }
                    if (this.data.idOficina)
                     this.oficinaSeleccionado = Object.values(this.dataOficinas).find(oficina => oficina.id === this.data.idOficina);
                })
                .catch(() => { debugger });
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
            if(this.data.id){
                this.update();
            } else {
                this.crear();
            }
        },
        update() {
            let url;
            this.mostrarLoadingAlert();
            this.validationErrors = [];
            url = `/admin/configuracion/provincias/`+this.data.id;
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
            url = `/admin/configuracion/provincias/registrar`;
            axios.post(url, this.data)
                .then((respuesta) => {
                    this.data = respuesta.data;
                    this.mensajeGuardado = 'Registro guardado correctamente';
                    this.guardado = true;
                    this.validationErrors = [];
                    this.$refs.loading.justCloseSimplert();
                    this.readonly = true;
                    window.location.replace('/admin/configuracion/provincias/' + respuesta.data.id);
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
    .provincia-component {
        margin-bottom: 120px;
    }
}
</style>