<template>
    <div class="row mt-4 mb-4 pl-xs-4 pl-md-0" id="filtro">
            <select id="filtro-categoria" class="dropdown boton-filtro col-xs-12 col-md-5 col-lg-4 col-xl-3 mr-md-3 mr-lg-2 mb-md-2 mb-lg-2"
                title="Categorías"
                name="categorias"
                v-on:change="cambiarCategoria"
                v-model="idCategoria"
            >
                <option v-for="categoria in dataCategorias" v-bind:value="categoria.id">
                    {{ categoria.nombre }}
                </option>
            </select>
        <div id="filtro-lugar" class="btn-group btn-group-toggle botones-rad col-xs-12 col-md-4 col-lg-4 col-xl-2 mr-md-3 mr-lg-2 mb-md-2 mb-lg-2">
            <label class="btn boton-filtro" v-bind:class="{active: dataBusqueda == 'punto'}" >
               <input type="radio" name="busqueda" value="punto" v-model="dataBusqueda" >Punto de encuentro
            </label>
            <label class="btn boton-filtro" v-bind:class="{active: dataBusqueda == 'lugar'}" >
               <input type="radio" name="busqueda" value="lugar" v-model="dataBusqueda">Lugar de actividad
            </label>
        </div>
        <div id="filtro-provincias" class="boton-filtro cont-check col-xs-12 col-md-3 col-lg-2 mr-md-3">

            <contenedor-check-provincias
                    v-bind:provincias="this.dataProvincias"
            >
            </contenedor-check-provincias>
        </div>
        <div id="filtro-tipos" class="boton-filtro cont-check col-xs-12 col-md-3 col-lg-2 mr-md-3">
            <contenedor-check-tipos
                v-bind:propdatos="this.tiposDeActividad"
            >
            </contenedor-check-tipos>

        </div>
        <div class="borrar-filtros col-xs-12 col-md-2 col-lg-1 mr-md-3 pl-lg-0">
            <span class="btn btn-default boton-filtro text-center" v-on:click="borrarFiltros">
                <i class="fas fa-sync"></i>
                Borra Filtros
            </span>
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
                        if(response.data.length) {
                            this.tiposDeActividad = [{ 
                                        id: 0,
                                        titulo: 'Marcar todas',
                                        actividades: response.data
                            }];
                        }
                        //this.tiposDeActividad[0].todas = 'Marcar todas';
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
                
                this.axiosPost(url, function (response, self) { //implemenatación de axiosPost global
                    self.dataProvincias = Object.keys(response).map(i => response[i]);
                            for (let i=0; i< self.$children.length; i++) {
                                self.$children[i].listaProvincias = self.dataProvincias;
                            }
                        },formData,
                function (error) {
                    console.log('error en getProvinciasYLocalidades. url: ' + url);
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
                console.log(this.dataBusqueda);
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
    * {
        font-family: Montserrat, sans-serif;
        font-size: 12px;
        font-weight: bold;
        font-style: normal;
        font-stretch: normal;
    }
    #filtro {
        margin: 0 -6%;
        padding: 0;
    }
    .botones-rad {
        display: table;
        padding: 0;
        vertical-align: middle;
    }
    .botones-rad label {
        display: table-cell;
        vertical-align: middle;
    }
    .borrar-filtros .boton-filtro {
        border: none;
        padding-top: 12px;
    }
    .boton-filtro {
        border: 1px solid #a9a9a9;
        color: #0092dd;
        height: 40px;
        line-height: normal;
        letter-spacing: normal;
        text-align: left;
        text-transform: uppercase;
        border-radius: 5px;
    }
    .botones-rad label:first-child {
        border-right: none;
    }
    .boton-filtro:focus {outline:0;}
    select.boton-filtro option {
        color: #494848;
    }
    label.boton-filtro {
        color: #a9a9a9;
    }
    label.boton-filtro.active {
        background-color: #0092dd;
        color: #fff;
    }
    .contenedor-dropdown {
        padding: 0;
        margin-right: 15px;
    }

    .cont-check {
        padding-left: 0;
        padding-right: 0;
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

    @media (max-width: 768px) {
        #filtro-categoria,
        #filtro-lugar,
        #filtro-provincias,
        #filtro-tipos,
        .borrar-filtros {
            margin: 5px 15px;
        }
    }
</style>