<template>
    <div class="equipo-component">
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
                                    <input id="nombre" type="text" class="form-control" v-model="data.nombre"
                                        :disabled="this.readonly">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="pais">Oficina</label>
                                    <v-select :disabled="this.readonly" :options="dataOficinas" label="descripcion" placeholder="Seleccione"
                                        name="oficina" id="oficina" v-model="oficinaSeleccionado"
                                        >
                                        <span slot="no-options"></span>
                                    </v-select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                <label for="fechaInicio">Fecha Inicio</label>
                                <div :class="{  'has-error': errors.fechaInicio }">
                                    <input name="fechaInicio" v-model="data.fechaInicio" type="date" class="form-control"
                                        required style="line-height: inherit;" :disabled="this.readonly">
                                    <span class="help-block">{{ errors.fechaInicio }}</span>
                                </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                <label for="fechaFin">Fecha Fin</label>
                                <div :class="{ 'has-error': errors.fechaFin }">
                                    <input name="fechaFin" v-model="data.fechaFin" type="date" class="form-control"
                                        required style="line-height: inherit;" :disabled="this.readonly">
                                    <span class="help-block">{{ errors.fechaFin }}</span>
                                </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="estado">Estado</label>
                                    <select name="estado" class="form-control" v-model="data.activo" required
                                        :disabled="this.readonly">
                                        <option value="1" :selected="data.activo">Activo</option>
                                        <option value="0" :selected="data.activo">Finalizado</option>
                                    </select>
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
    name: "equipos-form",
    props: ['equipo', 'edicion'],
    components: {},
    data() {
        return {
            data: {
                nombre: "",
                idOficina: null,
                fechaInicio: null,
                fechaFin: null,
                activo: null,
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
        if (this.equipo) {
            this.data = this.equipo;
      
            this.data.fechaInicio = moment(this.equipo.fechaInicio).format('YYYY-MM-DD');
            if (this.data.fechaFin )
                this.data.fechaFin = moment(this.equipo.fechaFin).format('YYYY-MM-DD');
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
                    if (this.equipo.idEquipo){
                        this.oficinaSeleccionado = Object.values(this.dataOficinas).find(oficina => oficina.id === this.data.idOficina);
                    }
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
            if(this.data.idEquipo){
                this.update();
            } else {
                this.crear();
            }
        },
        update() {
            let url;
            this.mostrarLoadingAlert();
            this.validationErrors = [];
            url = `/admin/equipos/`+this.data.idEquipo;
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
            url = `/admin/equipos/registrar`;
            axios.post(url, this.data)
                .then((respuesta) => {
                    this.data = respuesta.data;
                    this.mensajeGuardado = 'Registro guardado correctamente';
                    this.guardado = true;
                    this.validationErrors = [];
                    this.$refs.loading.justCloseSimplert();
                    this.readonly = true;
                    window.location.replace('/admin/equipos/' + respuesta.data.idEquipo);
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
    .equipo-component {
        margin-bottom: 120px;
    }
}
</style>