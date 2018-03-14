<template>
    <div class="row mt-4 mb-4 "id="filtro">
        <div class="col-md-3">
            <select class="dropdown"
                title="Categorías"
                name="categorias"
                v-on:change="getTiposDeActividad"
                v-model="idCategoria"
            >
                <option v-for="categoria in dataCategorias" v-bind:value="categoria.id">
                    {{ categoria.nombre }}
                </option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="dropdown"
                title="Tipo de Actividad"
                name="tipoActividad"
                v-model="idTipoDeActividad"
                v-on:change="filtrar"
            >
                <option value="">Todas las actividades</option>
                 <option v-for="actividad in tiposDeActividad" v-bind:value="actividad.idTipo">
                    {{ actividad.nombre }}
                </option>
            </select>
        </div>
        <div class="col-md-2">
            <select class="dropdown"
                title="Provincias"
                name="provincias"
                v-model="idProvincia"
                v-on:change="getLocalidades"
            >
                <option value="">Todas las provincias</option>
                <option v-for="provincia in dataProvincias" v-bind:value="provincia.id">
                    {{ provincia['provincia'] }}
                </option>
            </select>
        </div>
        <div class="col-md-2">
            <select class="dropdown"
                title="Localidades"
                name="localidades"
                v-model="idLocalidad"
                v-on:change="filtrar"
            >
                <option value="">{{ mensajeLocalidades }}</option>
                <option v-for="localidad in dataLocalidades" v-bind:value="localidad.id">
                    {{ localidad.localidad }}
                </option>
            </select>
        </div>
        <div class="col-md-2 pull-right">
            <button class="btn techo-btn-azul btn-sm pull-right" v-on:click="resetFiltros">
                <i class="fas fa-sync"></i>
                Borra Filtros
            </button>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        name: "filtro",
        props: ['categoria_seleccionada', 'categorias', 'provincias', 'localidades'],
        data () {
             return {
                 tiposDeActividad:  [],
                 idCategoria:       this.categoria_seleccionada,
                 dataCategorias:    this.categorias,
                 idProvincia:       '',
                 dataProvincias:    this.provincias,
                 idLocalidad:       '',
                 dataLocalidades:   [],
                 idTipoDeActividad: '',
                 mensajeLocalidades: "Seleccione una provincia...",
             }
        },
        methods: {
            filtrar: function (){

                let paramCategoria = 'categoria=' + this.idCategoria;
                let paramProvincia = '&provincia=' + this.idProvincia;
                let paramLocalidad = '&localidades=' + this.idLocalidad;
                let paramTipoDeActividad = '&tipo=' + this.idTipoDeActividad;
                let parametros = paramCategoria +
                    paramProvincia +
                    paramLocalidad +
                    paramTipoDeActividad;
                let url = '/ajax/actividades?' + parametros;
                // console.log(url);

                var event = new CustomEvent('cargarTarjetas', {detail: url});
                window.dispatchEvent(event);
            },
            getTiposDeActividad: function () {
                let url = '/ajax/categorias/' + this.idCategoria;
                // console.log(url);
                axios.get(url)
                    .then(response => {
                        // console.log(response.data);
                        this.tiposDeActividad = response.data.tipos;
                        this.filtrar();
                    })
                    .catch((error) => {
                        // Error
                        this.hasError = true;
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


            },
            getLocalidades: function() {
                let url = '/ajax/provincias/' + this.idProvincia;
                // console.log(url);
                axios.get(url)
                    .then(response => {
                        // console.log(response.data);
                        this.mensajeLocalidades = "Todas las localidades";
                        this.dataLocalidades = response.data.localidades;
                        this.filtrar();
                    })
                    .catch((error) => {
                        // Error
                        this.hasError = true;
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
            },
            resetFiltros: function () {
                this.dataLocalidades = [];
                this.idProvincia = "";
                this.idTipoDeActividad = "";
                this.idLocalidad = "";
                this.mensajeLocalidades = "Seleccione una provincia...";
                this.filtrar();
            },
        },
        created: function() {
            this.idCategoria        = JSON.parse(this.idCategoria);
            this.dataProvincias     = JSON.parse(this.dataProvincias);
            this.dataCategorias     = JSON.parse(this.dataCategorias);
            this.getTiposDeActividad();
            this.filtrar();
            // console.log('filtrar created');
        }
    }
</script>

<style scoped>
    .dropdown {
        font-family: Montserrat, sans-serif;
        font-size: 12px;
        font-weight: bold;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: normal;
        text-align: left;
        text-transform: uppercase;
        color: #0092dd;
        height: 40px;
        box-shadow: 0 0 4px 0 rgba(0, 0, 0, 0.17);
    }

    .techo-btn-azul {
        box-shadow: 0 0 4px 0 rgba(0, 0, 0, 0.17);
        padding: 1em 1em;
        height: 40px;
    }

    #filtro {
        padding-bottom: 15px;
        border-bottom: solid thin #cecece
    }
</style>