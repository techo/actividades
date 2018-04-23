<template>
    <span>
    <div v-show="guardado" class="callout callout-success">
        <h4>{{ mensajeGuardado }}</h4>
    </div>
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
                                placeholder="Seleccione"
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
                    :puntos-encuentro="dataActividad.puntosEncuentro"
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
            <div class="col-md-1">
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
            <div class="col-md-2"><p style="margin-top: 3em"> voluntarios permitidos</p></div>
            <div class="col-md-3">
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
            <div class="col-md-3">
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
            <div class="col-md-3">
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
    import axios from 'axios';
    import PuntoEncuentro from './punto-encuentro';
    import _ from 'lodash';


    export default {
        name: "actividades-show",
        props: ['actividad', 'tipos', 'categorias', 'paises', 'provincias', 'localidades', 'edicion'],
        components: {'punto-encuentro': PuntoEncuentro},
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
            tieneErrores: function () {
                return (this.validationErrors.length > 0);
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
                    return '<span class="label label-warning pull-right">Privado</span>';
                }

                return '<span class="label label-info pull-right">Público</span>';
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
                if (this.dataActividad.coordinador !== undefined) {
                    this.coordinadorSeleccionado =  this.dataActividad.coordinador;
                } else {
                    this.dataActividad.coordinador = null;
                    this.coordinadorSeleccionado = null;
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
                    `/ajax/coordinadores?coordinador=${escape(search)}`
                ).then(res => {
                    res.json().then(json => (vm.dataCoordinadores = json.data));
                    loading(false);
                });
            }, 1000),
            actualizarCoordinador() {
                this.dataActividad.coordinador = this.coordinadorSeleccionado;
            },
            getProvincias() {
                if (this.paisSeleccionado !== undefined && this.dataActividad.pais !== this.paisSeleccionado) {
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
                if (this.provinciaSeleccionada !== '' && this.dataActividad.provincia !== this.provinciaSeleccionada) {
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
            axiosPost(url, fCallback, params = []) {
                axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                axios.post(url, params)
                    .then(response => {
                        fCallback(response.data, this);
                        Event.$emit('success');
                        this.readonly = true;
                    })
                    .catch((error) => {
                        Event.$emit('error');
                        // Error
                        console.info('Error en: ' + url);
                        console.error(error.response.status);
                        if (error.response) {
                            // The request was made and the server responded with a status code
                            // that falls out of the range of 2xx
                            // console.error(error.response.data);
                            // console.error(error.response.status);
                            // console.error(error.response.headers);
                            if (error.response.status === 422) {
                                // debugger;
                                this.validationErrors = Object.values(error.response.data);
                            }
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
            axiosDelete(url, fCallback, params = []) {
                axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                axios.delete(url, params)
                    .then(response => {
                        fCallback(response.data, this)
                    })
                    .catch((error) => {
                        // Error
                        console.error('Error en: ' + url);
                        console.error(error.response.status);
                        if (error.response) {
                            // The request was made and the server responded with a status code
                            // that falls out of the range of 2xx
                            // console.error(error.response.data);
                            // console.error(error.response.status);
                            // console.error(error.response.headers);
                            this.validationErrors = ['No se pudo eliminar la actividad'];
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
            setLocalidad(){
                this.dataActividad.localidad = this.localidadSeleccionada;
            },
            editar() {
                this.readonly = false;
            },
            cancelar: function () {

            },
            guardar(){
                let url;

                if (this.dataActividad.idActividad === undefined || this.dataActividad.idActividad === null) {
                    url = `/admin/actividades/crear`;
                } else {
                    url = `/admin/actividades/${escape(this.dataActividad.idActividad)}/editar`;
                }
                window.scrollTo(0, 0);
                this.axiosPost(url, function (data, self) {
                    if (self.dataActividad.idActividad === null) {
                        window.location.replace('/admin/actividades');
                    }
                    self.mensajeGuardado = data;
                    self.guardado = true;
                    self.validationErrors = [];
                }, this.dataActividad);
            },
            borrarPunto: function (obj) {
                this.dataActividad.puntosEncuentroBorrados.push(obj);
            },
            eliminar: function () {

                var form = document.getElementById('formDelete');
                form.submit();
            },
        }
    }
</script>

<style scoped>

</style>