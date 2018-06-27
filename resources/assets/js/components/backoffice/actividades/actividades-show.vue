<template>
<span>
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
                                :onChange=this.actualizarTipoDeActividad()
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
                        <label for="fechaActividad">Fecha de Inicio y Fin de la actividad</label>
                        <br>
                        <p v-if="readonly">{{ this.fechasActividad }}</p>
                        <daterange-picker v-else @applyfechaActividad="cambioFechaActividad"
                                           :start-date=this.dataActividad.fechaInicio
                                           :end-date=this.dataActividad.fechaFin
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
                        <label for="fechaEvaluaciones">Fecha de Inicio y Fin de las Evaluaciones</label>
                        <br>
                        <p v-if="readonly">{{ this.fechasEvaluaciones }}</p>
                        <daterange-picker v-else @applyfechaEvaluaciones="cambioFechaEvaluaciones"
                                           :start-date=this.dataActividad.fechaInicioEvaluaciones
                                           :end-date=this.dataActividad.fechaFinEvaluaciones
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
            <div class="col-md-3">
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
            <div class="col-md-3">
                <div class="form-group">
                    <label>Estado de las inscripciones</label>
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
            <div class="col-md-6">
                <div class="form-group">
                    <label for="fechaInscripciones">Fecha de Inicio y Fin De La Inscripción</label>
                    <br>
                    <p v-if="readonly">{{ this.fechasInscripcion }}</p>
                    <daterange-picker v-else @applyfechaInscripciones="cambioFechaInscripciones"
                                       :start-date=this.dataActividad.fechaInicioInscripciones
                                       :end-date=this.dataActividad.fechaFinInscripciones
                                       :max-date="20350101"
                                       min-date="01-01-2018"
                                       opens="left"
                                       drops="up"
                                       :input="'fechaInscripciones'"
                                       name="fechaInscripciones"
                                       id="fechaInscripciones"
                    ></daterange-picker>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="mensajeInscripcion">Mensaje De Inscripción</label>
                    <textarea
                            name="mensajeInscripcion"
                            id="mensajeInscripcion"
                            cols="30"
                            rows="3"
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
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="costo">Costo (ARS)</label>
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
        </div>
            <!-- /.box-body -->
    </div>
    </span>

</template>

<script>
    import PuntoEncuentro from './punto-encuentro';
    import _ from 'lodash';
    import VueTimepicker from 'vue2-timepicker'; // https://github.com/phoenixwong/vue2-timepicker
    import moment from 'moment';
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
            }
        },
        created() {
            this.dataActividad = JSON.parse(this.actividad);
            this.dataLocalidades = this.localidades === '' ? [] : JSON.parse(this.localidades);
            this.dataPaises = JSON.parse(this.paises);
            this.dataProvincias = this.provincias === '' ? [] : JSON.parse(this.provincias);
            this.dataCategorias = JSON.parse(this.categorias);
            this.tiposDeActividad = (this.tipos !== '') ? JSON.parse(this.tipos) :  [];

            this.inicializar();
            this.getTiposDeActividad();
            this.getOficinas();

            //Eventos
            Event.$on('editar', this.editar);
            Event.$on('cancelar', this.cancelar);
            Event.$on('guardar', this.guardar);
            Event.$on('borrar-punto', this.borrarPunto);
            Event.$on('eliminar', this.eliminar);
        },
        computed: {
            tieneErrores:  function () {
                return (this.validationErrors.length > 0);
            },
            fechasActividad: function () {
                return window.moment(this.dataActividad.fechaInicio).locale("es").format("LL LT") + " - " + window.moment(this.dataActividad.fechaFin).locale("es").format("LL LT")
            },
            fechasEvaluaciones: function () {
                return window.moment(this.dataActividad.fechaInicioEvaluaciones).locale("es").format("LL LT")
                    + " - " + window.moment(this.dataActividad.fechaFinEvaluaciones).locale("es").format("LL LT")
            },
            fechasInscripcion: function () {
                return window.moment(this.dataActividad.fechaInicioInscripciones).locale("es").format("LL LT") + " - " + window.moment(this.dataActividad.fechaFinInscripciones).locale("es").format("LL LT")
            }
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
                this.dataActividad.limiteInscripciones = this.dataActividad.limiteInscripciones !== null ?  this.dataActividad.limiteInscripciones : 0;
                if (this.dataActividad.coordinador !== undefined) {
                    this.coordinadorSeleccionado =  this.dataActividad.coordinador;
                } else {
                    this.dataActividad.coordinador = null;
                    this.coordinadorSeleccionado = null;
                }

                if (this.dataActividad.fechaInicio == null){
                    this.dataActividad.fechaInicio = moment().format('YYYY-MM-DD');
                    this.dataActividad.fechaFin = moment().format('YYYY-MM-DD 23:59');
                    this.dataActividad.fechaInicioInscripciones = moment().format('YYYY-MM-DD');
                    this.dataActividad.fechaFinInscripciones = moment().format('YYYY-MM-DD 23:59');
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
            actualizarTipoDeActividad() {
                if (this.tipoSeleccionado !== null) {
                    this.dataActividad.idTipo = this.tipoSeleccionado.idTipo;
                    if (this.dataActividad.tipo === undefined) {
                        this.dataActividad.tipo = {};
                    }

                    if (!this.dataActividad.descripcion) {
                        this.dataActividad.descripcion = this.tipoSeleccionado.descripcion;
                    }

                    this.dataActividad.tipo.idTipo = this.tipoSeleccionado.idTipo;
                    this.dataActividad.tipo.flujo = this.tipoSeleccionado.flujo;
                    this.dataActividad.tipo.nombre = this.tipoSeleccionado.nombre;
                    this.esConstruccion = this.dataActividad.tipo !== undefined && this.dataActividad.tipo.flujo === 'CONSTRUCCION';
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

                this.axiosPost(url, //endpoint
                    function (data, self) { //handler de success
                        if (self.dataActividad.idActividad === null) {
                            window.location.replace('/admin/actividades');
                        }
                        self.mensajeGuardado = data;
                        self.guardado = true;
                        self.validationErrors = [];
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
                this.dataActividad.fechaInicio = start.format("YYYY-MM-DD HH:mm:ss");
                this.dataActividad.fechaFin = end.format("YYYY-MM-DD HH:mm:ss");
            },
            cambioFechaEvaluaciones: function (start, end) {
                this.dataActividad.fechaInicioEvaluaciones = start.format("YYYY-MM-DD HH:mm:ss");
                this.dataActividad.fechaFinEvaluaciones = end.format("YYYY-MM-DD HH:mm:ss");
            },
            cambioFechaInscripciones: function (start, end) {
                this.dataActividad.fechaInicioInscripciones = start.format("YYYY-MM-DD HH:mm:ss");
                this.dataActividad.fechaFinInscripciones = end.format("YYYY-MM-DD HH:mm:ss");
            },
            findObjectByKey(array, key, value) {
                for (var i = 0; i < array.length; i++) {
                    if (array[i][key] === value) {
                        return {
                            'obj': array[i],
                            'index': i
                        };
                    }
                }
                return null;
            },

        }
    }
</script>

<style scoped>
    .grey {
        color: grey;
    }
</style>