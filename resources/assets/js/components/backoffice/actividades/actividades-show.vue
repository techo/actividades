<template>
    <span>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">Información General</h3>
            <span v-html="$options.filters.visibilidad(this.inscripcionInterna)"></span>
            <span v-html="$options.filters.estado(this.estadoConstruccion)"></span>&nbsp;
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="estadoActividad">Estado de la Actividad</label>
                        <p>
                            <v-switch
                                    v-model="dataActividad.estadoConstruccion"
                                    type-bold="true"
                                    text-enabled="Abierto"
                                    text-disabled="Cerrada"
                                    theme="bootstrap"
                                    color="primary"
                                    id="estadoActividad"
                                    name="estadoActividad"
                                    :disabled="readonly"
                            >
                        </v-switch>
                        </p>

                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="categoria">Categoría</label>
                        <v-select
                                :options="categorias"
                                label="nombre"
                                placeholder="Seleccione"
                                name="categoria"
                                id="categoria"
                                v-model="categoriaSeleccionada"
                                v-bind:disabled="this.readonly"
                                :onChange=this.getTiposDeActividad()
                        >
                        </v-select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tiposDeActividad">Tipo de Actividad</label>
                        <v-select
                                :options="tiposDeActividad"
                                label="nombre"
                                placeholder="Seleccione"
                                name="tiposDeActividad"
                                id="tiposDeActividad"
                                v-model="tipoSeleccionado"
                                v-bind:disabled="this.readonly"
                        >
                        </v-select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="unidad_organizacional">Oficina</label>
                        <v-select
                                :options="unidadesOrganizacionales"
                                label="nombre"
                                placeholder="Seleccione"
                                name="unidad_organizacional"
                                id="unidad_organizacional"
                                v-model="unidadSeleccionada"
                                v-bind:disabled="this.readonly"
                        >
                        </v-select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="nombreActividad">Nombre de Actividad</label>
                        <input id="nombreActividad"
                               type="text"
                               class="form-control"
                               v-model="dataActividad.nombreActividad"
                               :disabled="readonly"
                        >
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="fechaInicio">Fecha de Inicio De La Actividad</label>
                        <datepicker
                                placeholder="Seleccione una fecha"
                                v-model="dataActividad.fechaInicio"
                                id="fechaInicio"
                                name="fechaInicio"
                                language="es"
                                :disabled-picker="readonly"
                        ></datepicker>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="fechaFin">Fecha de Fin De La Actividad</label>
                        <datepicker
                                placeholder="Seleccione una fecha"
                                v-model="dataActividad.fechaFin"
                                id="fechaFin"
                                name="fechaFin"
                                language="es"
                                :disabled-picker="readonly"
                        ></datepicker>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea
                                name="descripcion"
                                id="descripcion"
                                cols="30"
                                rows="5"
                                class="form-control"
                                v-bind:disabled="readonly"
                                v-model="dataActividad.descripcion"
                        >
                            {{ dataActividad.descripcion }}
                        </textarea>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.box-body -->
    </div>
        <!-- /.box -->
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">Ubicación De La Actividad</h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="pais">País</label>
                        <v-select
                                :options="dataPaises"
                                label="nombre"
                                placeholder="Seleccione"
                                name="pais"
                                id="pais"
                                v-model="paisSeleccionado"
                                v-bind:disabled="this.readonly"
                                :onChange=this.getProvincias()
                        >
                        </v-select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="provincia">Provincia</label>
                        <v-select
                                :options="dataProvincias"
                                label="provincia"
                                placeholder="Seleccione"
                                name="provincia"
                                id="provincia"
                                v-model="provinciaSeleccionada"
                                v-bind:disabled="this.readonly"
                                :onChange=this.getLocalidades()
                        >
                        </v-select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="localidad">Localidad</label>
                        <v-select
                                :options="dataLocalidades"
                                label="localidad"
                                placeholder="Seleccione"
                                name="localidad"
                                id="localidad"
                                v-model="localidadSeleccionada"
                                v-bind:disabled="this.readonly"
                        >
                        </v-select>
                    </div>
                </div>
            </div>

        </div>
        <!-- /.box-body -->
    </div>
        <!-- /.box -->
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">Puntos De Encuentro</h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <punto-encuentro
                    :readonly="readonly"
                    :puntos-encuentro="dataActividad.puntos_encuentro"
                    :paises="dataPaises"
            ></punto-encuentro>
        </div>
        <!-- /.box-body -->
    </div>
        <!-- /.box -->
    <div class="box">
    <div class="box-header with-border">
        <h3 class="box-title">Inscripciones</h3>
    </div>
        <!-- /.box-header -->
    <div class="box-body">
        <div class="row">
            <div class="col-md-4">
            </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="fechaInicioInscripciones">Fecha de Inicio De La Inscripción</label>
                        <datepicker
                                placeholder="Seleccione una fecha"
                                v-model="dataActividad.fechaInicioInscripciones"
                                id="fechaInicioInscripciones"
                                name="fechaInicioInscripciones"
                                language="es"
                                :disabled-picker="readonly"
                        ></datepicker>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="fechaFinInscripciones">Fecha de Fin De La Inscripción</label>
                        <datepicker
                                placeholder="Seleccione una fecha"
                                v-model="dataActividad.fechaFinInscripciones"
                                id="fechaFinInscripciones"
                                name="fechaFinInscripciones"
                                language="es"
                                :disabled-picker="readonly"
                        ></datepicker>
                    </div>
                </div>
        </div>
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="mensajeInscripcion">Mensaje De Inscripción</label>
                        <textarea
                                name="mensajeInscripcion"
                                id="mensajeInscripcion"
                                cols="30"
                                rows="4"
                                class="form-control"
                                :disabled="readonly"
                                v-model="dataActividad.descripcion"
                        >
                            {{ dataActividad.descripcion }}
                        </textarea>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="inscripcionInterna">Visibilidad de las Inscripciones</label>
                                <br>
                                <v-switch
                                        v-model="dataActividad.inscripcionInterna"
                                        theme="bootstrap"
                                        color="primary"
                                        id="inscripcionInterna"
                                        name="inscripcionInterna"
                                        type-bold="true"
                                        text-enabled="Privadas"
                                        text-disabled="Públicas"
                                        :disabled="readonly"
                                >
                                </v-switch>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="limiteInscripciones">Límite</label>
                                <input id="limiteInscripciones"
                                       type="number"
                                       class="form-control"
                                       v-bind:disabled="readonly"
                                       v-model="dataActividad.limiteInscripciones"
                                >
                            </div>
                        </div>
                        <div class="col-md-8">
                            <p style="margin-top: 3em">voluntarios permitidos</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                </div>
                <div class="col-md-2"></div>

            </div>

    </div><!-- /.box-body -->
</div>

    <div class="box" v-if="esConstruccion">
    <div class="box-header with-border">
        <h3 class="box-title">Construcción</h3>
    </div>
        <!-- /.box-header -->
    <div class="box-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="costo">Costo ({{ dataActividad.moneda }})</label>
                    <input id="costo" name="costo"
                           type="number"
                           class="form-control"
                           v-bind:disabled="readonly"
                           v-model="dataActividad.costo"
                    >
                </div>
            </div>
            <div class="col-md-8">
                <div class="form-group">
                    <label for="linkPago">Link de Pago</label>
                    <input
                            type="url"
                            class="form-control"
                            id="linkPago"
                            name="linkPago"
                            :disabled="readonly"
                            v-model="dataActividad.LinkPago"
                    >
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="idFormulario">Evaluaciones (Id FOBU)</label>
                    <input
                            type="url"
                            class="form-control"
                            id="idFormulario"
                            name="idFormulario"
                            :disabled="readonly"
                            v-model="dataActividad.idFormulario"
                    >
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="fechaInicioEvaluaciones">Desde</label>
                    <input
                            type="datetime-local"
                            class="form-control"
                            id="fechaInicioEvaluaciones"
                            name="fechaInicioEvaluaciones"
                            :disabled="readonly"
                            v-model="dataActividad.fechaInicioEvaluaciones"
                    >
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="fechaFinEvaluaciones">Hasta</label>
                    <input
                            type="datetime-local"
                            class="form-control"
                            id="fechaFinEvaluaciones"
                            name="fechaFinEvaluaciones"
                            :disabled="readonly"
                            v-model="dataActividad.fechaFinEvaluaciones"
                    >
                </div>
            </div>
        </div>
    </div>
        <!-- /.box-body -->
</div>
    </span>

</template>

<script>
    import axios from 'axios';
    import PuntoEncuentro from './punto-encuentro'

    export default {
        name: "actividades-show",
        props: ['actividad', 'coordinadores', 'paises', 'provincias', 'localidades', 'edicion'],
        components: { 'punto-encuentro': PuntoEncuentro},
        data() {
            return {
                dataActividad: {},
                readonly: !this.edicion,
                dataPaises: [],
                paisSeleccionado: {},
                dataProvincias: [],
                provinciaSeleccionada: {},
                dataLocalidades: [],
                localidadSeleccionada: {},
                categorias: [],
                categoriaSeleccionada: {},
                tiposDeActividad: [],
                tipoSeleccionado: {},
                unidadesOrganizacionales: [],
                unidadSeleccionada: {},
                estadoConstruccion: false,
                inscripcionInterna: false,
                dataCoordinadores: this.coordinadores
            }
        },
        created() {
            this.dataActividad = JSON.parse(this.actividad);
            this.dataPaises = JSON.parse(this.paises);
            this.dataProvincias = this.provincias === '' ? [] : JSON.parse(this.provincias);
            this.dataLocalidades = this.localidades === '' ? [] : JSON.parse(this.localidades);
            this.categoriaSeleccionada = this.dataActividad.tipo.categoria;
            this.tipoSeleccionado = this.dataActividad.tipo;
            this.unidadSeleccionada = this.dataActividad.unidad_organizacional;
            this.localidadSeleccionada = this.dataActividad.localidad;
            this.provinciaSeleccionada = this.dataActividad.provincia;
            this.paisSeleccionado = this.dataActividad.pais;
            this.dataActividad.estadoConstruccion = (this.dataActividad.estadoConstruccion === "Abierta");
            this.estadoConstruccion = this.dataActividad.estadoConstruccion;
            this.inscripcionInterna = this.dataActividad.inscripcionInterna;
            this.getCategorias();
            this.getTiposDeActividad();
            this.getUnidadesOrganizacionales();

            //Eventos
            Event.$on('editar', this.editar);
            Event.$on('cancelar', this.cancelar);
        },
        computed: {
            esConstruccion: function() {
                return this.dataActividad.tipo.flujo === 'CONSTRUCCION';
            },
        },
        filters: {
            estado: function (value) {
                if (value) {
                    return '<span class="label label-success pull-right">Abierta</span>';
                }

                return '<span class="label label-danger pull-right">Cerrada</span>';
            },

            visibilidad: function (value) {
                if (value) {
                    return '<span class="label label-dark pull-right">Privado</span>';
                }

                return '<span class="label label-info pull-right">Público</span>';
            }
        },
        methods: {
            getProvincias() {
                if (this.paisSeleccionado !== undefined && this.dataActividad.pais !== this.paisSeleccionado) {
                    this.dataActividad.pais = this.paisSeleccionado;
                    this.axiosGet('/ajax/paises/' + this.paisSeleccionado.id + '/provincias',
                        function (data, self) {
                            self.dataProvincias = data;
                            self.provinciaSeleccionada = '';
                            self.localidadSeleccionada = ''
                        });
                }
            },
            getLocalidades() {
                if (this.provinciaSeleccionada !== '' && this.dataActividad.provincia !== this.provinciaSeleccionada) {
                    this.dataActividad.provincia = this.provinciaSeleccionada;
                    this.axiosGet('/ajax/paises/' + this.paisSeleccionado.id + '/provincias/' + this.provinciaSeleccionada.id + '/localidades',
                        function (data, self) {
                            self.dataLocalidades = data;
                            self.localidadSeleccionada = '';
                        });
                }
            },
            getCategorias() {
                this.axiosGet('/ajax/categorias/',
                    function (data, self) {
                    self.categorias = data;
                });

            },
            getTiposDeActividad() {
                if (this.dataActividad.tipo.categoria !== this.categoriaSeleccionada) {
                    this.dataActividad.tipo.categoria = this.categoriaSeleccionada;
                    this.axiosGet('/ajax/categorias/' + this.dataActividad.tipo.categoria.id + '/tipos',
                        function (data, self) {
                            self.tiposDeActividad = data;
                            self.tipoSeleccionado = null;
                        }
                    );
                }
            },
            getUnidadesOrganizacionales() {
                this.axiosGet('/admin/ajax/unidadesOrganizacionales/',
                    function (data, self) {
                        self.unidadesOrganizacionales = data;
                    });
            },
            axiosGet(url, fCallback, params = []) {
                axios.get(url, params)
                    .then(response => {
                        fCallback(response.data, this)
                    })
                    .catch((error) => {
                        // Error
                        console.error('Error en: ' + url);
                        if (error.response) {
                            // The request was made and the server responded with a status code
                            // that falls out of the range of 2xx
                            console.error(error.response.data);
                            console.error(error.response.status);
                            console.error(error.response.headers);
                        } else if (error.request) {
                            // The request was made but no response was received
                            // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
                            // http.ClientRequest in node.js
                            console.error(error.request);
                        } else {
                            // Something happened in setting up the request that triggered an Error
                            console.error('Error', error.message);
                        }
                        console.error(error.config);
                    });

            },
            editar() {
                this.readonly = false;
            },
            cancelar(){
                this.readonly = true;
            },

        }
    }
</script>

<style scoped>

</style>