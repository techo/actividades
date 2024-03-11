<template>
    <div class="row mb-4 pl-xs-4 pl-md-0" id="filtro">

           <!--  <select id="filtro-categoria" class="dropdown boton-filtro col-xs-12 col-md-5 col-lg-4 col-xl-3 mr-md-3 mb-md-2"
                title="Categorías"
                name="categorias"
                v-on:change="cambiarCategoria"
                v-model="idCategoria"
            >
                <option v-for="categoria in dataCategorias" v-bind:value="categoria.id">
                    {{ $t('frontend.' + categoria.nombre) }}
                </option>
            </select> -->
        <!-- <div id="filtro-lugar" class="btn-group btn-group-toggle botones-rad col-xs-12 col-md-4 col-lg-4 col-xl-2 mr-md-3 mr-lg-2 mb-md-2 mb-lg-2">
            <label class="btn boton-filtro" v-bind:class="{active: dataBusqueda == 'punto'}" >
               <input type="radio" name="busqueda" value="punto" v-model="dataBusqueda" >Punto de encuentro
            </label>
            <label class="btn boton-filtro" v-bind:class="{active: dataBusqueda == 'lugar'}" >
               <input type="radio" name="busqueda" value="lugar" v-model="dataBusqueda">Lugar de actividad
            </label>
        </div> -->
        <div id="filtro-provincias" class="boton-filtro cont-check col-xs-12 col-md-3 col-lg-2 mr-md-3">

            <contenedor-check-provincias
                    v-bind:provincias="this.lista_provincias"
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
                {{ $t('frontend.delete_filter') }}
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
                 idCategoria:       (this.categoria_seleccionada)?this.categoria_seleccionada:null,
                 dataCategorias:    this.categorias,
                 dataProvincias:    [],
                 dataLocalidades:   [],
                 dataTiposActividad: [],
                 dataBusqueda: 'punto',
                 lista_provincias: [],
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
                    provincias: this.dataProvincias,
                    localidades: this.dataLocalidades,
                    tipos: this.dataTiposActividad,
                    busqueda: this.dataBusqueda
                };

                if(this.idCategoria) {
                    filtros.categoria = this.idCategoria;
                }

                let event = new CustomEvent('cargarTarjetas', {detail: filtros});
                window.dispatchEvent(event);
            },
            getTiposDeActividad: function () {
                let url = '/ajax/actividades/tipos';
                let filtros = {
                    provincias: this.dataProvincias,
                    localidades: this.dataLocalidades,
                    busqueda: this.dataBusqueda
                };

                if(this.idCategoria) {
                    filtros.categoria = this.idCategoria;
                }

                axios.get(url, { params: filtros })
                    .then(response => {
                        if(response.data.length) {
                            this.tiposDeActividad = [{ 
                                        id: 0,
                                        titulo: this._i18n.t('frontend.select_all'),
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
                    tipos: this.dataTiposActividad,
                    busqueda: this.dataBusqueda
                };

                if(this.idCategoria) {
                    formData.categoria = this.idCategoria;
                }

                axios.get(url, { params: formData })
                    .then(response => {
                        let datos = response.data;
                        this.lista_provincias = Object.keys(datos).map(i => datos[i]);
                        for (let i=0; i< this.$children.length; i++) {
                            this.$children[i].listaProvincias = this.lista_provincias;
                        }
                    })
                    .catch((error) => { debugger; });
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
            dataProvincias: function(viejo, nuevo) {
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
            this.idCategoria        = (this.idCategoria)?JSON.parse(this.idCategoria):null;
            this.dataCategorias     = JSON.parse(this.categorias);
            this.dataCategorias.unshift({'id': null, 'nombre': this._i18n.t('categories')})
            this.actualizarFiltros();
        },
        mounted() {
            this.filtrar();
        }
    }
</script>

<style scoped>
    * {
        font-family: Fredoka, Montserrat, sans-serif;
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
        border-radius: 30px;
        background-color: #009fe3;
    }
    .techo-btn-azul {
        box-shadow: 0 0 4px 0 rgba(0, 0, 0, 0.17);
        padding: 1em 1em;
        height: 40px;
    }

    #filtro {
        padding-bottom: 15px;
        border-bottom: solid thin #cecece;
        justify-content: center;
    }

    input[type="radio"] {
        margin: 0 10px;
    }

    .dropdown-container {
        padding-right: 0;
    }

    #filtro-categoria {
        background-color: #fff;
        color: #494848
    }

    #filtro-categoria option {
        background-color: #fff;
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