<template>
    <div class="row justify-content-center mt-4 mb-4 "id="filtro">
        <div class="col-md-3">
            <select class="dropdown"
                title="Categorías"
                name="categorias"
                v-on:change="cambiarCategoria"
                v-model="idCategoria"
            >
                <option v-for="categoria in dataCategorias" v-bind:value="categoria.id">
                    {{ categoria.nombre }}
                </option>
            </select>
        </div>
        <div class="col-lg-2">
            <div class="row">
               <input type="radio" name="busqueda" value="punto" v-model="dataBusqueda">Punto de encuentro
            </div>
            <div class="row">
               <input type="radio" name="busqueda" value="lugar" v-model="dataBusqueda">Lugar de actividad
            </div>
        </div>
        <div class="col-md-2 dropdown-container">

            <contenedor-check-provincias
                    v-bind:provincias="this.dataProvincias"
            >
            </contenedor-check-provincias>
        </div>
        <div class="col-md-2 dropdown-container">
            <contenedor-check-tipos
                v-bind:propdatos="this.tiposDeActividad"
            >
            </contenedor-check-tipos>

        </div>
        <div class="col-md-1">
            <button class="btn btn-primary" v-on:click="borrarFiltros">
                <i class="fas fa-sync"></i>
                Borra Filtros
            </button>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    import ContenedorCheckProvincias from './contenedorCheckProvincias';
    import ContenedorCheckTipoActividades from './contenedorCheckTipoActividades'

    export default {
        name: "filtro",
        props: ['categoria_seleccionada', 'categorias'],
        components: {
            'contenedor-check-provincias': ContenedorCheckProvincias,
            'contenedor-check-tipos': ContenedorCheckTipoActividades
        },
        data () {
             return {
                 tiposDeActividad:  [],
                 idCategoria:       this.categoria_seleccionada,
                 dataCategorias:    this.categorias,
                 dataProvincias:    [],
                 dataLocalidades:   [],
                 dataTiposActividad: [],
                 dataBusqueda: 'punto'
             }
        },
        methods: {
            cambiarCategoria() {
                this.dataTiposActividad = [];
                this.dataLocalidades = [];
            },
            actualizarFiltros() {
                this.getTiposDeActividad();
                this.getProvinciasYLocalidades();
            },
            filtrar: function (){
                let filtros = {
                    categoria: this.idCategoria,
                    localidades: this.dataLocalidades,
                    tipos: this.dataTiposActividad,
                    busqueda: this.dataBusqueda
                };

                let event = new CustomEvent('cargarTarjetas', {detail: filtros});
                window.dispatchEvent(event);
            },
            getTiposDeActividad: function () {
                let url = '/ajax/actividades/tipos';
                let filtros = {
                    localidades: this.dataLocalidades,
                    categoria: this.idCategoria,
                    busqueda: this.dataBusqueda
                };
                axios.post(url, filtros)
                    .then(response => {
                        this.tiposDeActividad = response.data;
                        for (let i=0; i< this.$children.length; i++) {
                            this.$children[i].listaTipos = this.tiposDeActividad;
                        }

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

            getProvinciasYLocalidades: function () {
                let url = '/ajax/actividades/provincias/';
                let formData = {
                    categoria: this.idCategoria,
                    tipos: this.dataTiposActividad,
                    busqueda: this.dataBusqueda
                };
                axios.post(url, formData)
                    .then(response => {
                        this.dataProvincias = Object.keys(response.data).map(i => response.data[i]);
                        for (let i=0; i< this.$children.length; i++) {
                            this.$children[i].listaProvincias = this.dataProvincias;
                        }
                    })
                    .catch((error) => {
                        // Error
                        console.log('error en getTiposDeActividad. url: ' + url);
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
            borrarFiltros: function () {
                this.dataLocalidades = [];
                this.dataTiposActividad = [];
                for (let i=0; i< this.$children.length; i++) {
                    this.$children[i].borrar();
                }
            },
        },
        watch: {
            dataLocalidades: function(viejo, nuevo) {
                this.getTiposDeActividad();
                this.filtrar();
            },
            dataTiposActividad () {
                this.getProvinciasYLocalidades();
                this.filtrar();

            },
            dataBusqueda: function() {
                this.borrarFiltros();
            }
        },
        created: function() {
            this.idCategoria        = JSON.parse(this.idCategoria);
            this.dataCategorias     = JSON.parse(this.categorias);
            this.actualizarFiltros();
        },
        mounted() {
            this.filtrar();
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

    input[type="radio"] {
        margin: 0 10px;
    }

    .dropdown-container {
        padding-right: 0;
    }
</style>