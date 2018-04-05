<template>
    <span>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">Información General</h3>
            <span v-html="$options.filters.estado(this.dataActividad.estadoConstruccion)"></span>
            &nbsp;
            <span v-html="$options.filters.visibilidad(this.dataActividad.visibilidad)"></span>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="categoria">Categoría</label>
                        <select id="categoria" name="categoria"
                                class="form-control"
                                v-bind:disabled="readonly"
                                v-model="dataActividad.tipo.categoria.id"
                        >
                            <option v-for="cat in categorias" v-bind:value="cat.id">{{ cat.nombre }}</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tipo">Tipo de Actividad</label>
                        <select id="tipo" name="tipo"
                                class="form-control"
                                v-bind:disabled="readonly"
                                v-model="dataActividad.tipo.idTipo"
                        >
                            <option
                                    v-for="tipo in tiposDeActividad"
                                    v-bind:value="tipo.idTipo"
                            >
                                {{ tipo.nombre }}
                            </option>
                        </select>

                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="oficina">Oficina</label>
                        <select id="oficina" name="oficina"
                                class="form-control"
                                v-bind:disabled="readonly"
                                v-model="dataActividad.unidad_organizacional.idUnidadOrganizacional"
                        >
                            <option
                                    v-for="unidad in unidadesOrganizacionales"
                                    v-bind:value="unidad.idUnidadOrganizacional"
                            >
                                {{ unidad.nombre }}
                            </option>
                        </select>
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
                        <input
                            type="text"
                            class="form-control"
                            id="fechaInicio"
                            name="fechaInicio"
                            :value="dataActividad.fechaInicio"
                            :disabled="readonly"
                        >
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="fechaFin">Fecha de Fin De La Actividad</label>
                        <input
                            type="text"
                            class="form-control"
                            id="fechaFin"
                            name="fechaFin"
                            :value="dataActividad.fechaFin"
                            :disabled="readonly"
                        >
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
                                rows="3"
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
                        <select id="pais" name="pais"
                                class="form-control"
                                v-bind:disabled="readonly"
                                v-model="dataActividad.idPais"
                        >
                            <option disabled value="">Seleccione</option>
                            <option v-for="pais in paises" v-bind:value="pais.id">{{ pais.nombre }}</option>
                        </select>

                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="provincia">Provincia</label>
                        <select id="provincia" name="provincia"
                                class="form-control"
                                v-bind:disabled="readonly"
                                v-model="dataActividad.idProvincia"
                        >
                            <option disabled value="">Seleccione</option>
                            <option v-for="prov in provincias" v-bind:value="prov.id">{{ prov.provincia }}</option>
                        </select>

                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="localidad">Localidad</label>
                        <select id="localidad" name="localidad"
                                class="form-control"
                                v-bind:disabled="readonly"
                                v-model="dataActividad.idLocalidad"
                        >
                            <option disabled value="">Seleccione</option>
                            <option v-for="loc in localidades" v-bind:value="loc.id">{{ loc.localidad }}</option>
                        </select>
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

            <div class="row" v-for="punto in dataActividad.puntos_encuentro">
                <div class="col-md-4">
                    <div class="form-group">
                        <p>{{ punto.punto }}</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <p>{{ punto.horario }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <p>{{ punto.responsable.nombres }} {{ punto.responsable.apellidoPaterno }}</p>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group"><br>
                        <p style="color: red">Borrar</p>
                    </div>
                </div>
            </div>
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
                <label for="estadoInscripcion">Estado De La Inscripción</label>
                <select id="estadoInscripcion" name="estadoInscripcion"
                        class="form-control"
                        :disabled="readonly"
                        v-model="dataActividad.idProvincia"
                >
                    <option disabled value="">Seleccione</option>
                    <option v-for="prov in provincias" v-bind:value="prov.id">{{ prov.provincia }}</option>
                </select>
            </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="fechaInicioInscripciones">Fecha de Inicio De La Inscripción</label>
                        <input
                            type="datetime-local"
                            class="form-control"
                            id="fechaInicioInscripciones"
                            name="fechaInicioInscripciones"
                            :disabled="readonly"
                            v-model="dataActividad.fechaInicioInscripciones"
                        >
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="fechaFinInscripcion">Fecha de Fin De La Inscripción</label>
                        <input
                                type="datetime-local"
                                class="form-control"
                                id="fechaFinInscripcion"
                                name="fechaFinInscripcion"
                                :disabled="readonly"
                                v-model="dataActividad.fechaFinInscripciones"
                        >
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
                                <label for="descripcion">Estado</label>
                                <br>
                                <input type="radio" name="estadoInscripciones" > Abiertas
                                <input type="radio" name="estadoInscripciones"> Cerradas
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

    export default {
        name: "actividades-show",
        props: ['actividad'],
        data() {
            return {
                dataActividad: {},
                readonly: true,
                paises: [],
                // paisSeleccionado: '',
                provincias: [],
                // provinciaSeleccionada: '',
                localidades: [],
                // localidadSeleccionada: '',
                categorias: [],
                // categoriaSeleccionada: ''
                tiposDeActividad: [],
                unidadesOrganizacionales: [],
                // esConstruccion: false
            }
        },
        created() {
            this.dataActividad = JSON.parse(this.actividad);
            this.getPaises();
            this.getProvincias();
            this.getLocalidades();
            this.getCategorias();
            this.getTiposDeActividad();
            this.getUnidadesOrganizacionales();
        },
        computed: {
            esConstruccion: function() {
                return this.dataActividad.tipo.flujo === 'CONSTRUCCION';
            }
        },
        filters: {
            estado: function (value) {
                let etiqueta;
                switch (value) {
                    case 'Cerrada':
                        etiqueta = '<span class="label label-danger pull-right">Cerrada</span>';
                        break;
                    case 'Abierta':
                        etiqueta = '<span class="label label-success pull-right">Abierta</span>';
                        break;
                    case 'Cancelada':
                        etiqueta = '<span class="label label-warning pull-right">Cancelada</span>';
                        break;
                    case 'En planificación':
                        etiqueta = '<span class="label label-primary pull-right">En Planificación</span>';
                        break;
                    // default
                }
                return etiqueta;
            },

            visibilidad: function (value) {
                if (value===1) {
                    return '<span class="label label-info pull-right">Publico</span>'
                }
                return '<span class="label label-dark pull-right">Privado</span>'
            }
        },
        methods: {
            getPaises() {
                this.axiosGet('/ajax/paises', function (data, self) {
                    self.paises = data;
                    // self.paisSeleccionado = self.dataActividad.idPais
                });
            },
            getProvincias() {
                this.axiosGet('/ajax/paises/' + this.dataActividad.idPais + '/provincias',
                    function (data, self) {
                        console.log(data);
                        self.provincias = data;
                        // self.provinciaSeleccionada = self.dataProvincias.idProvincia;
                    });
            },
            getLocalidades() {
                this.axiosGet('/ajax/paises/' + this.dataActividad.idPais + '/provincia/' + this.dataActividad.idProvincia + '/localidades',
                    function (data, self) {
                        self.localidades = data;
                        // self.localidadSeleccionada = self.dataActividad.idLocalidad;
                    });
            },

            getCategorias() {
                this.axiosGet('/ajax/categorias', function (data, self) {
                    self.categorias = data;
                });
            },

            getTiposDeActividad() {
                this.axiosGet('/ajax/categorias/' + this.dataActividad.tipo.categoria.id + '/tipos',
                    function (data, self) {
                        self.tiposDeActividad = data;
                    });
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
                        if (error.response) {
                            // The request was made and the server responded with a status code
                            // that falls out of the range of 2xx
                            console.log(error.response.data);
                            console.log(error.response.status);
                            console.log(error.response.headers);
                        } else if (error.request) {
                            // The request was made but no response was received
                            // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
                            // http.ClientRequest in node.js
                            console.log(error.request);
                        } else {
                            // Something happened in setting up the request that triggered an Error
                            console.log('Error', error.message);
                        }
                        console.log(error.config);
                    });

            }
        }
    }
</script>

<style scoped>

</style>