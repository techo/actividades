<template>
    <span>
        <div class="row" v-show="!readonly">
            <div class="col-md-3">
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
            <div class="col-md-3">
                <div class="form-group">
                    <label for="horario">Horario</label>
                    <input
                            type="text"
                            id="horario"
                            class="form-control"
                            v-model="horario"
                    >
                    <p class="text-danger" v-show="errorHorario"><small>Este campo es requerido</small></p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="coordinador">Coordinador</label>
                    <v-select
                            :options="dataCoordinadores"
                            label="nombre"
                            placeholder="Seleccione"
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
            <div class="col-md-3">
                <div class="form-group">
                    <button
                            type="button"
                            class="btn btn-light btn-lg"
                            @click="incluirPunto"
                            style="margin-top: 1em"
                    >
                        <i class="fa fa-plus text-primary"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="row" v-show="!readonly">
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

                    >
                    </v-select>
                    <p class="text-danger" v-show="errorPais"><small>Este campo es requerido</small></p>
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

        <div class="row" v-for="punto in dataPuntosEncuentro">
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
                <p>{{ punto.responsable.nombres }} {{ punto.responsable.apellidosPaterno}}</p>
            </div>
            <div class="col-md-1">
                <div class="form-group" v-if="!readonly">
                    <button
                            type="button"
                            class="btn btn-light"
                            @click="borrar(punto.idPuntoEncuentro)"
                    >
                            <i class="fa fa-trash text-danger"></i>
                    </button>
                </div>
            </div>
        </div>
    </span>
</template>

<script>
    import axios from 'axios';
    import _ from 'lodash';

    export default {
        name: "punto-encuentro",
        props: ['readonly', 'puntos-encuentro', 'paises'],
        data: function () {
            return {
                dataReadonly: this.readonly,
                dataPuntosEncuentro: this.$parent.dataActividad.puntos_encuentro,
                coordinador: '',
                punto: '',
                horario: '',
                dataCoordinadores: [],
                puntoSeleccionado: {},
                dataPaises: this.paises,
                paisSeleccionado: '',
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
                }
            }
        },
        created: function () {
            Event.$on('cancelar', this.cancelar);
        },
        watch: {
            paisSeleccionado: function () {
                this.getProvincias();
            },
            provinciaSeleccionada: function () {
                this.getLocalidades();
            }
        },
        computed: {
            errorPunto: function () {
                return (this.validationErrors.punto && this.punto === '')
            },

            errorHorario: function () {
                return (this.validationErrors.horario && this.horario === '')
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

        },
        methods: {
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
            }, 350),
            incluirPunto: function (e) {
                let valid = this.validate();
                if (valid) {
                    this.dataPuntosEncuentro.push({
                        'responsable': {
                            'dni': this.coordinador.dni,
                            'id': this.coordinador.id,
                            'nombres': this.coordinador.nombre
                        },
                        'horario': this.horario,
                        'punto': this.punto,
                        'idPais': this.paisSeleccionado.id,
                        'idProvincia': this.provinciaSeleccionada.id,
                        'idLocalidad': this.localidadSeleccionada.id
                    });

                    this.punto = '';
                    this.coordinador = '';
                    this.horario = '';
                    this.paisSeleccionado = '';
                    this.provinciaSeleccionada = '';
                    this.localidadSeleccionada = '';

                    this.validationErrors = {
                        punto: false,
                        horario: false,
                        coordinador: false,
                        pais: false,
                        provincia: false,
                        localidad: false,
                    }
                }
            },
            borrar: function (id) {
                let data = this.findObjectByKey(this.dataPuntosEncuentro, 'idPuntoEncuentro', id);

                this.dataPuntosEncuentro.splice(data.index, 1);
                Event.$emit('borrar-punto', data.obj);
            },
            getProvincias() {
                if (this.paisSeleccionado.id !== undefined) {
                    this.axiosGet('/ajax/paises/' + this.paisSeleccionado.id + '/provincias',
                        function (data, self) {
                            self.dataProvincias = data;
                            self.provinciaSeleccionada = '';
                            self.localidadSeleccionada = ''
                        });
                }
            },
            getLocalidades() {
                if (this.provinciaSeleccionada !== '' && this.provinciaSeleccionada.id !== undefined) {
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

                if (this.horario === '') {
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
            }
        }
    }
</script>

<style scoped>

</style>