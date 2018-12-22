<template>
<div>
    


    <div v-show="guardado" class="callout callout-success">
        <h4>{{ mensajeGuardado }}</h4>
    </div>
    <simplert ref="loading"></simplert>

    <div v-show="tieneErrores" class="callout callout-danger">
        <h4>Errores:</h4>
        <ul>
           <li v-for="error in validationErrors">{{ error }}</li>
        </ul>
    </div>
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">Información General</h3>
            <span v-html="$options.filters.visibilidad(this.inscripcionInterna)"></span>
            <span v-html="$options.filters.estado(this.estadoConstruccion)"></span>&nbsp;
        </div>
        <!-- /.box-header -->
        <div class="box-body">
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
                        <label for="coordinador">Coordinador</label>
                        <v-select
                                :options="dataCoordinadores"
                                label="nombre"
                                placeholder="Escribe el nombre o apellido"
                                name="coordinador"
                                id="coordinador"
                                v-model="coordinadorSeleccionado"
                                v-bind:disabled="this.readonly"
                                :filterable=false
                                @search="onSearch"
                                :onChange=this.actualizarCoordinador()
                        >
                        </v-select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Estado de la Actividad</label><br>
                        <div class="btn-group" role="group" aria-label="Estado de la Construcción">
                            <button
                                    type="button"
                                    class="btn"
                                    :disabled="readonly"
                                    :class="{'btn-danger': !dataActividad.estadoConstruccion, 'grey': dataActividad.estadoConstruccion}"
                                    @click="dataActividad.estadoConstruccion = false">
                              <i class="fa fa-times-circle"></i> Cerrada
                            </button>
                            <button
                                    type="button"
                                    class="btn"
                                    :disabled="readonly"
                                    :class="{'btn-success': dataActividad.estadoConstruccion, 'grey': !dataActividad.estadoConstruccion}"
                                    @click="dataActividad.estadoConstruccion = true">
                              <i class="fa fa-check-circle"></i> Abierta
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="categoria">Categoría</label>
                        <v-select
                                :options="dataCategorias"
                                label="nombre"
                                placeholder="Seleccione"
                                name="categoria"
                                id="categoria"
                                v-model="categoriaSeleccionada"
                                v-bind:disabled="this.readonly"
                                :filterable=false
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
                        <label for="oficina">Oficina</label>
                        <v-select
                                :options="dataOficinas"
                                label="nombre"
                                placeholder="Seleccione"
                                name="oficina"
                                id="oficina"
                                v-model="oficinaSeleccionada"
                                v-bind:disabled="this.readonly"
                                :onChange=this.actualizarOficina()
                        >
                        </v-select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="fechaInscripciones">Fecha de Inicio y Fin Inscripción</label>
                        <br>
                        <p v-if="readonly || fechasAutomaticas">{{ this.fechasInscripcion_etiqueta }}</p>
                        <daterange-picker v-else @applyfechaInscripciones="cambioFechaInscripciones"
                                          v-bind:fechas="this.fechasInscripcion"
                                          v-on:input="this.fechasInscripcion = $event"
                                          :max-date="20350101"
                                          min-date="01-01-2018"
                                          opens="right"
                                          drops="down"
                                          :input="'fechaInscripciones'"
                                          name="fechaInscripciones"
                                          id="fechaInscripciones"
                                          ref="fechaInscripciones"
                        ></daterange-picker>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="fechaActividad">Fecha de Inicio y Fin Actividad</label>
                        <br>
                        <p v-if="readonly">{{ this.fechasActividad_etiqueta }}</p>
                        <daterange-picker v-else @applyfechaActividad="cambioFechaActividad"
                                           v-bind:fechas="this.fechasActividad"
                                           v-on:input="this.fechasActividad = $event"
                                           :max-date="20350101"
                                           min-date="01-01-2018"
                                           opens="right"
                                           drops="down"
                                           :input="'fechaActividad'"
                                           name="fechaActividad"
                                           id="fechaActividad"
                        ></daterange-picker>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="fechaEvaluaciones">Fecha de Inicio y Fin Evaluaciones</label>
                        <br>
                        <p v-if="readonly || fechasAutomaticas">{{ this.fechasEvaluaciones_etiqueta }}</p>
                        <daterange-picker v-else @applyfechaEvaluaciones="cambioFechaEvaluaciones"
                                           v-bind:fechas="this.fechasEvaluacion"
                                           v-on:input="this.fechasEvaluacion = $event"
                                           :max-date="20350101"
                                           min-date="01-01-2018"
                                           opens="right"
                                           drops="down"
                                           :input="'fechaEvaluaciones'"
                                           name="fechaEvaluaciones"
                                           id="fechaEvaluaciones"
                        ></daterange-picker>
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
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <p class="text-muted">
                        Última modificación: {{ dataActividad.fechaModificacion }}
                        {{ dataActividad.modificado_por ? ' por ' + dataActividad.modificado_por.nombres + ' ' + dataActividad.modificado_por.apellidoPaterno : '' }}
                        &nbsp;<a class="btn btn-primary btn-sm" @click="cargarAuditoria(dataActividad.idActividad)">Ver auditoría</a>
                    </p>
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
                                :onChange="this.setLocalidad()"
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
                    :pais="paisSeleccionado"
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
            <div class="col-md-2">
                <div class="form-group">
                    <label for="limiteInscripciones">Límite de voluntarios (0 = Sin Límite)</label>
                    <input id="limiteInscripciones"
                           type="number"
                           min="0"
                           class="form-control"
                           v-bind:disabled="readonly"
                           v-model="dataActividad.limiteInscripciones"
                    >

                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Estado de las inscripciones</label><br>
                    <div class="btn-group" role="group" aria-label="Estado de la Inscripción">
                        <button
                                type="button"
                                class="btn"
                                :disabled="readonly"
                                :class="{'btn-warning': dataActividad.inscripcionInterna, 'grey': !dataActividad.inscripcionInterna}"
                                @click="dataActividad.inscripcionInterna = true">
                          <i class="fa fa-times-circle"></i> Internas
                        </button>
                        <button
                                type="button"
                                class="btn"
                                :disabled="readonly"
                                :class="{'btn-info': !dataActividad.inscripcionInterna, 'grey': dataActividad.inscripcionInterna}"
                                @click="dataActividad.inscripcionInterna = false">
                          <i class="fa fa-check-circle"></i> Públicas
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="form-group">
                    <label for="mensajeInscripcion">Mensaje De Inscripción</label>
                    <span class="text-muted pull-right">Este texto se incluirá en el correo de bienvenida a la actividad.</span>
                    <textarea
                            name="mensajeInscripcion"
                            id="mensajeInscripcion"
                            cols="30"
                            rows="4"
                            class="form-control"
                            :disabled="readonly"
                            v-model="dataActividad.mensajeInscripcion"
                    >
                        {{ dataActividad.mensajeInscripcion }}
                    </textarea>
                </div>
            </div>
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
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="montoMin">Monto Mínimo (ARS)</label>
                        <input id="montoMin" name="montoMin"
                               type="number"
                               class="form-control"
                               v-bind:disabled="readonly"
                               v-model="dataActividad.montoMin"
                               min="1"
                               required
                        >
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="montoMax">Monto máximo </label>
                        <input id="montoMax" name="montoMax"
                               type="number"
                               class="form-control"
                               v-bind:disabled="readonly"
                               v-model="dataActividad.montoMax"
                               min="0"
                        >
                        <span class="text-muted">Opcional</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="beca">Formulario de solicitud de beca</label>
                        <input id="beca" name="beca"
                               type="url"
                               class="form-control"
                               v-bind:disabled="readonly"
                               v-model="dataActividad.beca"
                        >
                        <span class="text-muted">Opcional</span>
                    </div>
                </div>
            </div>
        </div>
            <!-- /.box-body -->
    </div>
    </div>

</template>

<script>
    import PuntoEncuentro from './punto-encuentro';

    import _ from 'lodash';
    import VueTimepicker from 'vue2-timepicker'; // https://github.com/phoenixwong/vue2-timepicker
    import moment from 'moment';
    import store from '../stores/store';
    import daterangepicker from '../../../components/plugins/daterangepicker';

    window.moment = moment;

    window.moment.locale('es');

    export default {
        name: "actividades-show",
        props: ['actividad', 'tipos', 'categorias', 'paises', 'provincias', 'localidades', 'edicion'],
        components: {'punto-encuentro': PuntoEncuentro, VueTimepicker, 'daterange-picker': daterangepicker},
        data() {
            return {
                dataCategorias: [],
                categoriaSeleccionada: {},
                coordinadorSeleccionado: {},
                dataActividad: {},
                dataCoordinadores: [],
                dataLocalidades: [],
                dataPaises: [],
                dataProvincias: [],
                estadoConstruccion: false,
                guardado: false,
                inscripcionInterna: false,
                localidadSeleccionada: {},
                mensajeGuardado: "",
                paisSeleccionado: {},
                provinciaSeleccionada: {},
                readonly: !this.edicion,
                tiposDeActividad: [],
                tipoSeleccionado: {},
                dataOficinas: [],
                oficinaSeleccionada: {},
                validationErrors: {},
                esConstruccion: false,
                fechasAutomaticas: true,
                fechasActividad: {
                    'inicio': moment({hour: 0, minute: 0}).format('YYYY-MM-DD hh:mm'),
                    'fin': moment({hour: 23, minute: 59}).format('YYYY-MM-DD hh:mm')
                },
                fechasInscripcion: {
                    'inicio': moment({hour: 0, minute: 0}).subtract(1,'d').format('YYYY-MM-DD hh:mm'),
                    'fin': moment({hour: 0, minute: 0}).subtract(1,'m').format('YYYY-MM-DD hh:mm')
                },
                fechasEvaluacion: {
                    'inicio': moment({hour: 23, minute: 59}).add(1,'m').format('YYYY-MM-DD hh:mm'),
                    'fin': moment({hour: 23, minute: 59}).add(1,'d').format('YYYY-MM-DD hh:mm')
                }
            }
        },
        created() {
            this.dataActividad = JSON.parse(this.actividad);
            //console.log(this.dataActividad);
            this.dataLocalidades = this.localidades === '' ? [] : JSON.parse(this.localidades);
            this.dataPaises = JSON.parse(this.paises);
            this.dataProvincias = this.provincias === '' ? [] : JSON.parse(this.provincias);
            this.dataCategorias = JSON.parse(this.categorias);
            this.tiposDeActividad = (this.tipos !== '') ? JSON.parse(this.tipos) :  [];
            store.commit('initIdActividad', this.dataActividad.idActividad); // Id de Actividad visible para otros componentes mediante Vuex
            this.inicializar();
            this.getTiposDeActividad();
            this.getOficinas();

            //Eventos
            Event.$on('editar', this.editar);
            Event.$on('cancelar', this.cancelar);
            Event.$on('guardar', this.guardar);
            Event.$on('borrar-punto', this.borrarPunto);
            Event.$on('eliminar', this.eliminar);
            Event.$on('clonar', this.clonar);
        },
        computed: {
            tieneErrores:  function () {
                return (this.validationErrors.length > 0);
            },
            fechasActividad_etiqueta: function () {
                return this.mostrarFechas(this.fechasActividad.inicio,this.fechasActividad.fin);
            },
            fechasEvaluaciones_etiqueta: function () {
                return this.mostrarFechas(this.fechasEvaluacion.inicio,this.fechasEvaluacion.fin);
            },
            fechasInscripcion_etiqueta: function () {
                return this.mostrarFechas(this.fechasInscripcion.inicio,this.fechasInscripcion.fin);
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
                    return '<span class="label label-warning pull-right">Interna</span>';
                }
                return '<span class="label label-info pull-right">Pública</span>';
            }
        },
        watch: {
            tipoSeleccionado: function (nuevoTipo, tipoAnterior) {
                if (nuevoTipo !== null) {
                    this.dataActividad.idTipo = nuevoTipo.idTipo;
                    if (this.dataActividad.tipo === undefined) {
                        this.dataActividad.tipo = {};
                    }
                    if (typeof tinymce !== 'undefined'
                        && tinymce.get('descripcion') !== null
                        && nuevoTipo.descripcion !== null
                        && (this.dataActividad.descripcion === null || tinymce.get('descripcion').getContent() == "")) {
                        tinymce.get('descripcion').setContent(nuevoTipo.descripcion);
                        this.dataActividad.descripcion = nuevoTipo.descripcion;
                    }

                    this.dataActividad.tipo.idTipo = nuevoTipo.idTipo;
                    this.dataActividad.tipo.flujo = nuevoTipo.flujo;
                    this.dataActividad.tipo.nombre = nuevoTipo.nombre;
                    this.esConstruccion = (this.dataActividad.tipo !== undefined && this.dataActividad.tipo.flujo === 'CONSTRUCCION');
                    store.commit('updateEsConstruccion', this.esConstruccion);
                }
            }
        },
        methods: {
            inicializar: function () {
                this.dataActividad.estadoConstruccion = (this.dataActividad.estadoConstruccion === "Abierta");
                this.dataActividad.inscripcionInterna = (this.dataActividad.inscripcionInterna == 1);
                this.dataActividad.puntos_encuentro = this.dataActividad.puntos_encuentro || [] ;
                this.dataActividad.puntosEncuentroBorrados = [];
                this.categoriaSeleccionada = this.dataActividad.tipo !== undefined ? this.dataActividad.tipo.categoria : null;
                this.estadoConstruccion = this.dataActividad.estadoConstruccion;
                this.inscripcionInterna = this.dataActividad.inscripcionInterna;
                this.localidadSeleccionada = this.dataActividad.localidad;
                this.paisSeleccionado = this.dataActividad.pais;
                this.provinciaSeleccionada = this.dataActividad.provincia;
                this.tipoSeleccionado = this.dataActividad.tipo !== undefined  ? this.dataActividad.tipo : null;
                this.oficinaSeleccionada = this.dataActividad.oficina !== undefined  ? this.dataActividad.oficina : null;
                this.esConstruccion = this.dataActividad.tipo !== undefined && this.dataActividad.tipo.flujo === 'CONSTRUCCION';
                store.commit('updateEsConstruccion', this.esConstruccion);
                this.dataActividad.limiteInscripciones = this.dataActividad.limiteInscripciones !== null ?  this.dataActividad.limiteInscripciones : 0;
                if (this.dataActividad.coordinador !== undefined) {
                    this.coordinadorSeleccionado =  this.dataActividad.coordinador;
                } else {
                    this.dataActividad.coordinador = null;
                    this.coordinadorSeleccionado = null;
                }

                if (this.dataActividad.fechaInicio != null) {
                    this.fechasActividad.inicio = moment(this.dataActividad.fechaInicio).format('YYYY-MM-DD hh:mm');
                    this.fechasActividad.fin = moment(this.dataActividad.fechaFin).format('YYYY-MM-DD hh:mm');
                    this.fechasInscripcion.inicio = moment(this.dataActividad.fechaInicioInscripciones).format('YYYY-MM-DD hh:mm');
                    this.fechasInscripcion.fin = moment(this.dataActividad.fechaFinInscripciones).format('YYYY-MM-DD hh:mm');
                    this.fechasEvaluacion.inicio = moment(this.dataActividad.fechaInicioEvaluaciones).format('YYYY-MM-DD hh:mm');
                    this.fechasEvaluacion.fin = moment(this.dataActividad.fechaFinEvaluaciones).format('YYYY-MM-DD hh:mm');
                }
            },
            actualizarOficina() {
                this.dataActividad.oficina = this.oficinaSeleccionada;
            },
            onSearch: function (text, loading) {
                loading(true);
                this.search(loading, text, this);
            },
            search: _.debounce((loading, search, vm) => {
                fetch(
                    `/ajax/coordinadores?coordinador=${encodeURI(search)}`
                ).then(res => {
                    res.json().then(json => (vm.dataCoordinadores = json.data));
                    loading(false);
                });
            }, 1000),
            actualizarCoordinador() {
                this.dataActividad.coordinador = this.coordinadorSeleccionado;
            },
            getProvincias() {
                if (this.paisSeleccionado !== undefined && this.dataActividad.pais !== this.paisSeleccionado && this.paisSeleccionado !== null) {
                    this.dataActividad.pais = this.paisSeleccionado;
                    this.axiosGet('/ajax/paises/' + this.paisSeleccionado.id + '/provincias',
                        function (data, self) {
                            self.dataProvincias = data;
                            self.provinciaSeleccionada = '';
                            self.localidadSeleccionada = '';
                            self.dataActividad.provincia = {};
                            self.dataActividad.localidad = {};
                        });
                }
            },
            getLocalidades() {
                if (this.paisSeleccionado !== null && this.provinciaSeleccionada !== '' && this.dataActividad.provincia !== this.provinciaSeleccionada) {
                    this.dataActividad.provincia = this.provinciaSeleccionada;
                    this.axiosGet('/ajax/paises/' + this.paisSeleccionado.id + '/provincias/' + this.provinciaSeleccionada.id + '/localidades',
                        function (data, self) {
                            self.dataLocalidades = data;
                            self.localidadSeleccionada = '';
                        });
                }
            },
            getTiposDeActividad() {
                if (this.dataActividad.tipo !== undefined && this.dataActividad.tipo.categoria !== undefined) {
                    if (this.dataActividad.tipo.categoria !== this.categoriaSeleccionada) {
                        this.axiosGet('/ajax/categorias/' + this.categoriaSeleccionada.id + '/tipos',
                            function (data, self) {
                                self.tiposDeActividad = data;
                                self.tipoSeleccionado = null;
                                self.dataActividad.idTipo = null;
                            }
                        );
                        this.dataActividad.tipo.categoria = this.categoriaSeleccionada;
                    }
                } else {
                    this.dataActividad.tipo = {
                        'categoria': this.categoriaSeleccionada
                    };
                }
            },
            getOficinas() {
                this.axiosGet('/admin/ajax/oficinas/',
                    function (data, self) {
                        self.dataOficinas = data;
                    });
            },
            setLocalidad(){
                this.dataActividad.localidad = this.localidadSeleccionada;
            },
            editar() {
                this.readonly = false;
                this.enableTinymce();

            },
            guardar(){
                let url;
                this.mostrarLoadingAlert();
                this.validationErrors = [];
                window.scrollTo(0, 0);

                if (this.dataActividad.idActividad === undefined || this.dataActividad.idActividad === null) {
                    url = `/admin/actividades/crear`;
                } else {
                    url = `/admin/actividades/${encodeURI(this.dataActividad.idActividad)}/editar`;
                }

                this.dataActividad.fechaInicio = this.fechasActividad.inicio;
                this.dataActividad.fechaFin = this.fechasActividad.fin;
                this.dataActividad.fechaInicioInscripciones = this.fechasInscripcion.inicio;
                this.dataActividad.fechaFinInscripciones = this.fechasInscripcion.fin;
                this.dataActividad.fechaInicioEvaluaciones = this.fechasEvaluacion.inicio;
                this.dataActividad.fechaFinEvaluaciones = this.fechasEvaluacion.fin;

                this.dataActividad.descripcion = tinymce.get('descripcion').getContent();
                this.axiosPost(url, //endpoint
                    function (data, self) { //handler de success
                        if (self.dataActividad.idActividad === null) {
                            window.location.replace('/admin/actividades/usuario');
                        }
                        self.mensajeGuardado = data;
                        self.guardado = true;
                        self.validationErrors = [];
                        self.disableTinymce();
                        self.dataActividad.puntosEncuentroBorrados = [];
                        for (let i = 1; i < self.dataActividad.puntos_encuentro.length; i++) {
                            if (self.dataActividad.puntos_encuentro[i].nuevo) {
                                self.dataActividad.puntos_encuentro[i].nuevo = false;
                            }

                        }
                        self.$refs.loading.justCloseSimplert();
                    },
                    this.dataActividad, // request data
                    function (error, self) { //handler de error
                    self.ocultarLoadingAlert();
                    // Error
                    if (error.response) {
                        if (error.response.status === 422) {
                            self.validationErrors = Object.values(error.response.data);
                            if (self.dataActividad.puntos_encuentro.length === 0) {
                                self.dataActividad.puntos_encuentro = self.dataActividad.puntosEncuentroBorrados;
                                self.dataActividad.puntosEncuentroBorrados = [];
                            }
                        }
                    }
                });

            },
            borrarPunto: function (obj) {
                this.dataActividad.puntosEncuentroBorrados.push(obj.obj);
                this.dataActividad.puntos_encuentro.splice(obj.index, 1);
            },
            eliminar: function () {
                let form = document.getElementById('formDelete');
                form.submit();
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
            cambioFechaActividad: function (start, end) {
                this.fechasActividad.inicio = start.format("YYYY-MM-DD HH:mm:ss");
                this.fechasActividad.fin = end.format("YYYY-MM-DD HH:mm:ss");

                if(this.fechasAutomaticas) {
                    this.cambioFechaInscripciones(start.clone().subtract(10, 'd'),start.clone().subtract(1, 'm'));
                    this.cambioFechaEvaluaciones(end.clone().add(1, 'm'),end.clone().add(10, 'd'));
                }
            },
            cambioFechaEvaluaciones: function (start, end) {
                console.log("Evaluaciones: " + start.format("YYYY-MM-DD HH:mm:ss") + " " + end.format("YYYY-MM-DD HH:mm:ss"));
                this.fechasEvaluacion.inicio = start.format("YYYY-MM-DD HH:mm:ss");
                this.fechasEvaluacion.fin = end.format("YYYY-MM-DD HH:mm:ss");
            },
            cambioFechaInscripciones: function (start, end) {
                console.log("Inscripciones: " + start.format("YYYY-MM-DD HH:mm:ss") + " " + end.format("YYYY-MM-DD HH:mm:ss"));
                this.fechasInscripcion.inicio = start.format("YYYY-MM-DD HH:mm:ss");
                this.fechasInscripcion.fin = end.format("YYYY-MM-DD HH:mm:ss");
            },
            enableTinymce: function () {
                tinymce.get('descripcion').remove();
                let editor_config = {
                    path_absolute : "/",
                    selector: "textarea#descripcion",
                    menubar: false,
                    statusbar: true,
                    resize: true,
                    toolbar: "undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
                    plugins: [
                        "advlist autolink lists link image charmap print preview hr anchor pagebreak",
                        "searchreplace wordcount visualblocks visualchars code fullscreen",
                        "insertdatetime nonbreaking save table contextmenu directionality",
                        "emoticons template paste textcolor colorpicker textpattern"
                    ],
                    relative_urls: false,
                    file_browser_callback : function(field_name, url, type, win) {
                        let x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
                        let y = window.innerHeight|| document.documentElement.clientHeight|| document.getElementsByTagName('body')[0].clientHeight;
                        let cmsURL = editor_config.path_absolute + 'laravel-filemanager?field_name=' + field_name;
                        if (type == 'image') {
                            cmsURL = cmsURL + "&type=Images";
                        } else {
                            cmsURL = cmsURL + "&type=Files";
                        }

                        tinyMCE.activeEditor.windowManager.open({
                            file : cmsURL,
                            title : 'Administrador de archivos',
                            width : x * 0.8,
                            height : y * 0.8,
                            resizable : "yes",
                            close_previous : "no"
                        });
                    }
                };

                tinymce.init(editor_config);
            },
            disableTinymce: function () {
                tinymce.get('descripcion').remove();
                let editor_config = {
                    path_absolute : "/",
                    selector: "textarea#descripcion",
                    menubar: false,
                    statusbar: true,
                    resize: true,
                    readonly: 1,
                    plugins: [
                        "advlist autolink lists link image charmap print preview hr anchor pagebreak",
                        "searchreplace wordcount visualblocks visualchars code fullscreen",
                        "insertdatetime nonbreaking save table contextmenu directionality",
                        "emoticons template paste textcolor colorpicker textpattern"
                    ],
                    toolbar: false,
                    relative_urls: false,
                    file_browser_callback : function(field_name, url, type, win) {
                        let x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
                        let y = window.innerHeight|| document.documentElement.clientHeight|| document.getElementsByTagName('body')[0].clientHeight;
                        let cmsURL = editor_config.path_absolute + 'laravel-filemanager?field_name=' + field_name;
                        if (type == 'image') {
                            cmsURL = cmsURL + "&type=Images";
                        } else {
                            cmsURL = cmsURL + "&type=Files";
                        }

                        tinyMCE.activeEditor.windowManager.open({
                            file : cmsURL,
                            title : 'Administrador de archivos',
                            width : x * 0.8,
                            height : y * 0.8,
                            resizable : "yes",
                            close_previous : "no"
                        });
                    }
                };

                tinymce.init(editor_config);
            },
            clonar: function() {
                this.mostrarLoadingAlert();
                let url = '/admin/ajax/actividades/'+ this.dataActividad.idActividad +'/clonar';
                let params = { idActividad: this.dataActividad.idActividad };
                this.axiosPost(url, function(response, self) {
                    if (response.idActividad) {
                        window.location = '/admin/actividades/' + response.idActividad
                    }
                }, params,
                    function (response, self) {
                    // Si hay error
                        self.ocultarLoadingAlert();
                        self.$refs.loading.openSimplert({
                            title: 'Algo salió mal',
                            message: "<i class=\"fa fa-exclamation-triangle fa-4x\"></i> <br>" +
                            "<p>Ocurrió un error al clonar la actividad.  Recarga la página e intentalo de nuevo o " +
                            "repórtalo al administrador del sistema.</p>",
                            isShown: true,
                            disableOverlayClick: true,
                            type: ''
                        })
                    })
            },
            findObjectByKey(array, key, value) {
                for (let i = 0; i < array.length; i++) {
                    if (array[i][key] === value) {
                        return {
                            'obj': array[i],
                            'index': i
                        };
                    }
                }
                return null;
            },
            cargarAuditoria: function(id) {
                Event.$emit('cargarAuditoria', {tabla: 'actividad', id: id});
            },
            mostrarFechas: function(inicio,fin) {
                var i = moment(inicio);
                var f = moment(fin);
                console.log(inicio);
                console.log(fin);

                if (i.format('MM-DD-YYYY') === f.format('MM-DD-YYYY')) {
                    return i.format('DD/MM/YYYY (hh:mm') + ' - ' + f.format('hh:mm)');
                }
                return i.format("DD/MM/YYYY hh:mm") + " - " + f.format("DD/MM/YYYY hh:mm")
            }
        }
    }
</script>

<style scoped>
    .grey {
        color: grey;
    }
</style>