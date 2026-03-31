<template>
    <div class="comunidad-component">
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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nombre">{{ $t('backend.name') }}</label>
                                    <input id="nombre" type="text" class="form-control" v-model="data.nombre"
                                        :disabled="this.readonly">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pais">{{ $t('backend.office') }}</label>
                                    <v-select :disabled="this.readonly" :options="dataOficinas" label="descripcion" placeholder="Seleccione"
                                        name="oficina" id="oficina" v-model="oficinaSeleccionado"
                                        >
                                        <span slot="no-options"></span>
                                    </v-select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group" >
                                    <label for="provincia">{{ $t('backend.province') }}</label>
                                    <select name="idProvincia" class="form-control" v-model="data.idProvincia" required @change="getLocalidades($event)" :disabled="this.readonly">
                                        <option v-text="provincia.provincia" v-bind:value="provincia.id" v-for="provincia in provincias" ></option>
                                    </select>
                                    <span class="help-block">{{ errors.idProvincia }}</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group" >
                                    <label for="localidad">{{ $t('backend.location') }}</label>
                                    <select name="idLocalidad" class="form-control" v-model="data.idLocalidad" required :disabled="this.readonly">
                                        <option v-text="localidad.localidad" v-bind:value="localidad.id" v-for="localidad in localidades" ></option>
                                    </select>
                                    <span class="help-block">{{ errors.idLocalidad }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                
                                <div class="form-group">
                                    <label for="estado">{{ $t('backend.state') }}</label>
                                    <select name="estado" class="form-control" v-model="data.activo" required
                                        :disabled="this.readonly">
                                        <option value="1" :selected="data.activo">{{ $t('backend.active') }}</option>
                                        <option value="0" :selected="data.activo">{{ $t('backend.finished') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="diagnostico">{{ $t('backend.enlace_diagnostico') }}</label>
                                    <textarea name="diagnostico" v-model="data.diagnostico" class="form-control" :disabled="this.readonly" >
                                        </textarea>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                
                                    <label for="fecha_diagnostico">{{ $t('backend.ejecutado_el') }}</label>

                                    <input type="date" class="form-control" v-model="data.fecha_diagnostico" :disabled="this.readonly">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="plan_de_accion">{{ $t('backend.enlace_plan_de_accion') }}</label>
                                    <textarea name="plan_de_accion" v-model="data.plan_de_accion" class="form-control" :disabled="this.readonly" >
                                        </textarea>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="fecha_plan_de_accion">{{ $t('backend.ejecutado_el') }}</label>
                                    <input type="date" class="form-control" v-model="data.fecha_plan_de_accion" :disabled="this.readonly">
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
    name: "comunidad-form",
    props: ['comunidad', 'edicion'],
    components: {},
    data() {
        return {
            data: {
                nombre: "",
                idOficina: null,
                activo: 1,
                idPais: null, 
                idProvincia: null, 
                idLocalidad: null, 
                diagnostico: null, 
                plan_de_accion: null, 
                fecha_diagnostico: '',
                fecha_plan_de_accion: ''
            },
            guardado: false,
            mensajeGuardado: '',
            validationErrors: {},
            dataOficinas: [],
            oficinaSeleccionado: {},
            provincias: [],
            localidades: [],
            readonly: !this.edicion,
            errors: {},
        }
    },
    created() {
        this.getOficinas();
        if (this.comunidad) {
            this.data = this.comunidad;
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
                this.data.idOficina = this.oficinaSeleccionado.id;
                this.data.idPais = this.oficinaSeleccionado.id_pais;
                this.getProvincias();
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
                    if (this.comunidad.idComunidad){
                        this.oficinaSeleccionado = Object.values(this.dataOficinas).find(oficina => oficina.id === this.data.idOficina);
                    }
                })
                .catch(() => { debugger });
        },
        getProvincias(){
                axios.get('/ajax/paises/' + this.data.idPais + '/provincias')
                    .then((datos) => { this.provincias = datos.data; }).catch((error) => { debugger; });
        },
        getLocalidades(){
            axios.get('/ajax/paises/' + this.data.idPais + '/provincias/' + this.data.idProvincia + '/localidades')
                .then((datos) => { this.localidades = datos.data; }).catch((error) => { debugger; });
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
            if(this.data.idComunidad){
                this.update();
            } else {
                this.crear();
            }
        },
        update() {
            let url;
            this.mostrarLoadingAlert();
            this.validationErrors = [];
            url = `/admin/ajax/comunidades/`+this.data.idComunidad;
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
            url = `/admin/ajax/comunidades/registrar`;
            axios.post(url, this.data)
                .then((respuesta) => {
                    this.data = respuesta.data;
                    this.mensajeGuardado = 'Registro guardado correctamente';
                    this.guardado = true;
                    this.validationErrors = [];
                    this.$refs.loading.justCloseSimplert();
                    this.readonly = true;
                    window.location.replace('/admin/comunidades/' + respuesta.data.idComunidad);
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
    .comunidad-component {
        margin-bottom: 120px;
    }
}
</style>