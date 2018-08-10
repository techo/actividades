<template>
    <div>
        <div class="form-group">
            <button
                    type="button"
                    class="btn btn-primary"
                    @click="verFormulario=true"
                    v-show="!readonly && !verFormulario"
            >
                <i class="fa fa-map-marker"></i>  Agregar Punto de Encuentro
            </button>
        </div>
        <div class="row" v-show="!readonly && verFormulario">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="punto">Punto de Encuentro</label>
                    <input
                            type="text"
                            id="punto"
                            class="form-control"
                            required
                            v-model="punto"
                    >
                    <p class="text-danger" v-show="errorPunto"><small>Este campo es requerido</small></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="horario">Horario</label> <br>
                    <vue-timepicker v-model="objHora" id="horario"></vue-timepicker>
                    <p class="text-danger" v-show="errorHorario"><small>Este campo es requerido</small></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="coordinador">Responsable del punto de encuentro</label>
                    <v-select
                            :options="dataCoordinadores"
                            label="nombre"
                            placeholder="Escribe el nombre o apellido"
                            name="coordinador"
                            id="coordinador"
                            v-model="coordinador"
                            v-bind:disabled="this.readonly"
                            :filterable=false
                            @search="onSearch"
                    >
                    </v-select>
                    <p class="text-danger" v-show="errorCoordinador"><small>Este campo es requerido</small></p>
                </div>
            </div>
        </div>
        <div class="row" v-show="!readonly && verFormulario">
            <div class="col-md-4">
                <div class="form-group">
                    <label>País</label>
                    <p style="font-size: larger">{{ paisValidado }}</p>
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
                    >
                    </v-select>
                    <p class="text-danger" v-show="errorProvincia"><small>Este campo es requerido</small></p>
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
                    <p class="text-danger" v-show="errorLocalidad"><small>Este campo es requerido</small></p>
                </div>
            </div>
        </div>
        <div class="row" v-show="!readonly && verFormulario">
            <div class="col-md-12">
                <div class="form-group">
                    <button
                            type="button"
                            class="btn btn-default pull-right"
                            @click="verFormulario=false"
                    >
                        <i class="fa fa-ban"></i>  Cancelar
                    </button>
                    <button
                            type="button"
                            class="btn btn-primary pull-right"
                            @click="incluirPunto"
                    >
                        <i class="fa fa-plus"></i>  Incluir
                    </button>
                </div>
            </div>
        </div>
        <table class="table table-striped table-hover table-condensed" v-if="puntosEncuentro.length > 0">
            <thead class="thead-light">
                <th scope="col">Punto</th>
                <th scope="col">Lugar</th>
                <th scope="col">Horario</th>
                <th scope="col">Coordinador</th>
                <th scope="col"><span v-show="!readonly">Borrar</span></th>
            </thead>
            <tbody>
                <tr v-for="punto in dataPuntos" :key="punto.idPuntoEncuentro">
                    <td>
                        <p>{{ punto.punto }}</p>
                    </td>
                    <td>
                        <p v-if="punto.localidad">{{ punto.localidad.localidad }}, {{ punto.provincia.provincia }}, {{ punto.pais.nombre}}</p>
                        <p v-else>No definido</p>
                    </td>
                    <td>
                        <p v-html="$options.filters.hora(punto.horario)"></p>
                    </td>
                    <td>
                        <p>{{ punto.responsable.nombres }} {{ punto.responsable.apellidoPaterno}}</p>
                    </td>
                    <td>
                        <div class="form-group" v-if="!readonly">
                            <button
                                    type="button"
                                    class="btn btn-light"
                                    @click="borrar(punto.idPuntoEncuentro)"
                            >
                                    <i class="fa fa-trash text-danger"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <p v-else class="text-muted text-center">No hay puntos de encuentro en esta actividad</p>
    </div>
</template>
<script>
    import VueTimepicker from 'vue2-timepicker'
    import axios from 'axios';
    import _ from 'lodash';

    export default {
        name: "punto-encuentro",
        props: ['readonly', 'puntos-encuentro', 'paises', 'pais'],
        components: {VueTimepicker},
        data: function () {
            return {
                verFormulario: false,
                coordinador: '',
                punto: '',
                horario: '',
                objHora: {
                    HH: "",
                    mm: "",
                    ss: ""
                },
                dataCoordinadores: [],
                puntoSeleccionado: {},
                dataPaises: this.paises,
                paisSeleccionado: this.pais,
                dataProvincias: [],
                provinciaSeleccionada: '',
                dataLocalidades: [],
                localidadSeleccionada: '',
                validationErrors: {
                    punto: false,
                    horario: false,
                    coordinador: false,
                    pais: false,
                    provincia: false,
                    localidad: false,
                },
                dataPuntos: this.puntosEncuentro
            }
        },
        created: function () {
            if (this.paisSeleccionado === undefined || this.paisSeleccionado === null) {
                this.paisSeleccionado = {id:1, nombre: "Argentina"}
            }

            Event.$on('cancelar', this.cancelar);
        },
        watch: {
            paisSeleccionado: function (nuevo, viejo) {
                if (nuevo !== null) {
                    this.getProvincias();
                }
            },
            provinciaSeleccionada: function () {
                this.getLocalidades();
            },
            objHora: function () {
                this.horario = this.objHora.HH + ':' + this.objHora.mm + ':' + this.objHora.ss
            },
            pais: function() {
                this.paisSeleccionado = this.pais
            },
            readonly: function (nuevo, viejo) {
                if (nuevo === false && this.dataProvincias.length === 0) {
                    this.getProvincias();
                }
            },
            puntosEncuentro: function (nuevo, viejo) {
                this.dataPuntos = nuevo;
            }
        },
        computed: {
            errorPunto: function () {
                return (this.validationErrors.punto && this.punto === '')
            },

            errorHorario: function () {
                return (this.validationErrors.horario || this.horario === '::00')
            },

            errorCoordinador: function () {
                return (this.validationErrors.coordinador && this.coordinador === '')
            },

            errorPais: function () {
                return (this.validationErrors.pais && this.paisSeleccionado === '')
            },

            errorProvincia: function () {
                return (this.validationErrors.provincia && this.provinciaSeleccionada === '')
            },

            errorLocalidad: function () {
                return (this.validationErrors.localidad && this.localidadSeleccionada === '')
            },
            paisValidado: function () {
                if (this.paisSeleccionado === null) {
                    return '';
                }
                return this.paisSeleccionado.nombre;
            }

        },
        filters: {
            hora: function (value) {
                return value.substr(0, 5);
            }
        },
        methods: {
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
            incluirPunto: function (e) {
                let valid = this.validate();
                // genero un id provisional para identificar el punto
                let id = this.getRandomInt(100000, 1000000);
                if (valid) {
                    this.puntosEncuentro.push({
                        'responsable': {
                            'dni': this.coordinador.dni,
                            'idPersona': this.coordinador.idPersona,
                            'nombres': this.coordinador.nombre
                        },
                        'horario': this.horario,
                        'punto': this.punto,
                        'idPais': this.paisSeleccionado.id,
                        'idProvincia': this.provinciaSeleccionada.id,
                        'idLocalidad': this.localidadSeleccionada.id,
                        'idPuntoEncuentro': id,
                        'nuevo': true,
                        'pais': this.paisSeleccionado,
                        'provincia': this.provinciaSeleccionada,
                        'localidad': this.localidadSeleccionada
                    });

                    this.punto = '';
                    this.coordinador = '';
                    this.horario = '';
                    this.provinciaSeleccionada = '';
                    this.localidadSeleccionada = '';

                    this.validationErrors = {
                        punto: false,
                        horario: false,
                        coordinador: false,
                        pais: false,
                        provincia: false,
                        localidad: false,
                    };
                    this.objHora = {
                        HH: "",
                        mm: "",
                        ss: ""
                    };
                    this.verFormulario = false;
                }
            },
            borrar: function (id) {
                let data = this.findObjectByKey(this.puntosEncuentro, 'idPuntoEncuentro', id);
                Event.$emit('borrar-punto', data);
            },
            getProvincias() {
                if (this.paisSeleccionado !== null && this.paisSeleccionado.id !== undefined) {
                    this.axiosGet('/ajax/paises/' + this.paisSeleccionado.id + '/provincias',
                        function (data, self) {
                            self.dataProvincias = data;
                            self.provinciaSeleccionada = '';
                            self.localidadSeleccionada = ''
                        });
                }
            },
            getLocalidades() {
                if (this.paisSeleccionado !== null && this.provinciaSeleccionada.id !== undefined && this.provinciaSeleccionada.id !== null) {
                    let url = '/ajax/paises/' + this.paisSeleccionado.id +
                        '/provincias/' + this.provinciaSeleccionada.id + '/localidades';
                    this.axiosGet(url,
                        function (data, self) {
                            self.dataLocalidades = data;
                            self.localidadSeleccionada = '';
                        });
                }
            },
            validate() {
                let result = true;
                if (this.punto === '') {
                    this.validationErrors.punto = true;
                    result = false;
                }

                if (this.objHora.HH === '' || this.objHora.mm === '') {
                    this.validationErrors.horario = true;
                    result = false;
                }
                if (this.coordinador === '') {
                    this.validationErrors.coordinador = true;
                    result = false;
                }
                if (this.paisSeleccionado === '') {
                    this.validationErrors.pais = true;
                    result = false;
                }
                if (this.provinciaSeleccionada === '') {
                    this.validationErrors.provincia = true;
                    result = false;
                }
                if (this.localidadSeleccionada === '') {
                    this.validationErrors.localidad = true;
                    result = false;
                }
                return result;
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
                axios.post(url, params)
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
            cancelar: function () {
                this.coordinador = '';
                this.punto = '';
                this.horario = '';
                this.dataCoordinadores = [];
                this.puntoSeleccionado = {};
                this.dataPaises = this.paises;
                this.paisSeleccionado = '';
                this.dataProvincias = [];
                this.provinciaSeleccionada = '';
                this.dataLocalidades = [];
                this.localidadSeleccionada = '';
                this.validationErrors = {
                    punto: false,
                    horario: false,
                    coordinador: false,
                    pais: false,
                    provincia: false,
                    localidad: false,
                }
            },
            getRandomInt: function (min, max) {
                return Math.floor(Math.random() * (max - min + 1)) + min;
            }
        }
    }
</script>


<style scoped>
    table {
        margin-top: 2em;
    }

    button.pull-right {
        margin-left: 5px;
    }
</style>