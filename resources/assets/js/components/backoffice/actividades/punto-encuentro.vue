<template>
    <span>
        <div class="row" v-show="!readonly">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="punto">Punto de Encuentro</label>
                    <input
                            type="text"
                            id="punto"
                            class="form-control is-invalid"
                            required
                            v-model="punto"
                    >
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
                dataPuntosEncuentro: this.puntosEncuentro,
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
                validationErrors: [
                    {punto: false},
                    {horario: false},
                    {coordinador: false},
                    {pais: false},
                    {provincia: false},
                    {localidad: false},
                ]
            }
        },
        created: function () {
        },
        watch: {
            paisSeleccionado: function () {
                this.getProvincias();
            },
            provinciaSeleccionada: function () {
                this.getLocalidades();
            }
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
                e.preventDefault();
                this.validate();
                if (this.validationErrors.length === 0) {
                    this.dataPuntosEncuentro.push({
                        'responsable': {
                            'dni': this.coordinador.dni,
                            'id': this.coordinador.id,
                            'nombres': this.coordinador.nombre
                        },
                        'horario': this.horario,
                        'punto': this.punto
                    });

                }
            },
            borrar: function (id) {
                for (let i = 0; i <= this.dataPuntosEncuentro.length; i++) {
                    if (this.dataPuntosEncuentro[i].idPuntoEncuentro === id) {
                        this.dataPuntosEncuentro.splice(i, 1);
                    }
                }
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
                this.validationErrors = [];
                if (this.punto === '') {
                    this.validationErrors.push({punto: true})
                }
                if (this.horario === '') {
                    this.validationErrors.push({horario: true})
                }
                if (this.coordinador === '') {
                    this.validationErrors.push({coordinador: true})
                }
                if (this.paisSeleccionado === '') {
                    this.validationErrors.push({pais: true})
                }
                if (this.provinciaSeleccionada === '') {
                    this.validationErrors.push({provincia: true})
                }
                if (this.localidadSeleccionada === '') {
                    this.validationErrors.push({localidad: true})
                }
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

        }
    }
</script>

<style scoped>

</style>