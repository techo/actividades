<template>
    <span>
        <div class="row" v-show="!readonly">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="punto">Punto de Encuentro</label>
                    <input type="text" id="punto" class="form-control">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="horario">Horario</label>
                    <input type="text" id="horario" class="form-control">
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
                            filterable=false
                            :onSearch=this.buscarCoordinadores()
                    >
                    </v-select>
                </div>
            </div>
            <div class="col-md-3">
                <a href="#">Guardar</a>
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
                <div class="form-group"><br>
                    <p style="color: red">Borrar</p>
                </div>
            </div>
        </div>
    </span>
</template>

<script>
    export default {
        name: "punto-encuentro",
        props: ['readonly', 'puntos-encuentro'],
        data: function() {
            return {
                dataReadonly: this.readonly,
                dataPuntosEncuentro: this.puntosEncuentro,
                coordinador: [],
                dataCoordinadores: []
            }
        },
        methods: {
            buscarCoordinadores: function(text, fLoading) {
                let url = '/ajax/coordinadores';
                fLoading(true);
                this.axiosGet(url, getResultados, {'coordinador': text})
            },
            getResultados: function (data, self) {
                self.dataCoordinadores = data;
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

        }
    }
</script>

<style scoped>

</style>