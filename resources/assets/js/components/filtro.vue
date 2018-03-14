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
                    id="dropdown_provincias"
                v-model="idProvincia"
                    multiple="multiple"
                v-on:change="filtrar"
            >
                <option value="">Todas las provincias</option>
                <optgroup v-for="provincia in dataProvincias" v-bind:label="provincia.provincia">
                    <option v-for="localidad in provincia.localidades" v-bind:value="localidad.id_localidad">{{ localidad.localidad }}</option>
                </optgroup>
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
    import multipleSelect from 'multiple-select';

    export default {
        name: "filtro",
        props: ['categoria_seleccionada', 'categorias', 'localidades'],
        data () {
             return {
                 tiposDeActividad:  [],
                 idCategoria:       this.categoria_seleccionada,
                 dataCategorias:    this.categorias,
                 idProvincia:       '',
                 dataProvincias:    [],
                 idLocalidad:       '',
                 dataLocalidades:   [],
                 idTipoDeActividad: '',
                 mensajeProvincias: "Seleccione una provincia...",
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
            // getLocalidades: function() {
            //     let url = '/ajax/provincias/' + this.idProvincia;
            //     // console.log(url);
            //     axios.get(url)
            //         .then(response => {
            //             // console.log(response.data);
            //             this.mensajeProvincias = "Todas las localidades";
            //             this.dataLocalidades = response.data.localidades;
            //             this.filtrar();
            //         })
            //         .catch((error) => {
            //             // Error
            //             this.hasError = true;
            //             if (error.response) {
            //                 // The request was made and the server responded with a status code
            //                 // that falls out of the range of 2xx
            //                 console.log(error.response.data);
            //                 console.log(error.response.status);
            //                 console.log(error.response.headers);
            //             } else if (error.request) {
            //                 // The request was made but no response was received
            //                 // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
            //                 // http.ClientRequest in node.js
            //                 console.log(error.request);
            //             } else {
            //                 // Something happened in setting up the request that triggered an Error
            //                 console.log('Error', error.message);
            //             }
            //             console.log(error.config);
            //         });
            // },
            getProvinciasYLocalidades: function () {
                let url = '/ajax/actividades/provincias/';
                axios.get(url)
                    .then(response => {
                        // console.log(response.data);
                        this.mensajeProvincias = "Todas las provincias";
                        this.dataProvincias = response.data;
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
                this.mensajeProvincias = "Seleccione una provincia...";
                this.filtrar();
            },
        },
        created: function() {
            this.idCategoria        = JSON.parse(this.idCategoria);
            this.dataProvincias     = JSON.parse(this.dataProvincias);
            this.dataCategorias     = JSON.parse(this.dataCategorias);
            this.getTiposDeActividad();
            this.getProvinciasYLocalidades();
            this.filtrar();
            // console.log('filtrar created');
        }
    }


    $('#dropdown_provincias').multipleSelect();

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

    /* Custom Multiple-select*/
    .ms-parent {
        display: inline-block;
        position: relative;
        vertical-align: middle;
    }

    .ms-choice {
        display: block;
        width: 100%;
        height: 26px;
        padding: 0;
        overflow: hidden;
        cursor: pointer;
        border: 1px solid #aaa;
        text-align: left;
        white-space: nowrap;
        line-height: 26px;
        color: #444;
        text-decoration: none;
        -webkit-border-radius: 4px;
        -moz-border-radius: 4px;
        border-radius: 4px;
        background-color: #fff;
    }

    .ms-choice.disabled {
        background-color: #f4f4f4;
        background-image: none;
        border: 1px solid #ddd;
        cursor: default;
    }

    .ms-choice > span {
        position: absolute;
        top: 0;
        left: 0;
        right: 20px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
        padding-left: 8px;
    }

    .ms-choice > span.placeholder {
        color: #999;
    }

    .ms-choice > div {
        position: absolute;
        top: 0;
        right: 0;
        width: 20px;
        height: 25px;
        background: url('/img/multiple-select.png') left top no-repeat;
    }

    .ms-choice > div.open {
        background: url('/img/multiple-select.png') right top no-repeat;
    }

    .ms-drop {
        width: 100%;
        overflow: hidden;
        display: none;
        margin-top: -1px;
        padding: 0;
        position: absolute;
        z-index: 1000;
        background: #fff;
        color: #000;
        border: 1px solid #aaa;
        -webkit-border-radius: 4px;
        -moz-border-radius: 4px;
        border-radius: 4px;
    }

    .ms-drop.bottom {
        top: 100%;
        -webkit-box-shadow: 0 4px 5px rgba(0, 0, 0, .15);
        -moz-box-shadow: 0 4px 5px rgba(0, 0, 0, .15);
        box-shadow: 0 4px 5px rgba(0, 0, 0, .15);
    }

    .ms-drop.top {
        bottom: 100%;
        -webkit-box-shadow: 0 -4px 5px rgba(0, 0, 0, .15);
        -moz-box-shadow: 0 -4px 5px rgba(0, 0, 0, .15);
        box-shadow: 0 -4px 5px rgba(0, 0, 0, .15);
    }

    .ms-search {
        display: inline-block;
        margin: 0;
        min-height: 26px;
        padding: 4px;
        position: relative;
        white-space: nowrap;
        width: 100%;
        z-index: 10000;
    }

    .ms-search input {
        width: 100%;
        height: auto !important;
        min-height: 24px;
        padding: 0 20px 0 5px;
        margin: 0;
        outline: 0;
        font-family: sans-serif;
        font-size: 1em;
        border: 1px solid #aaa;
        -webkit-border-radius: 0;
        -moz-border-radius: 0;
        border-radius: 0;
        -webkit-box-shadow: none;
        -moz-box-shadow: none;
        box-shadow: none;
        background: #fff url('/img/multiple-select.png') no-repeat 100% -22px;
        background: url('/img/multiple-select.png') no-repeat 100% -22px, -webkit-gradient(linear, left bottom, left top, color-stop(0.85, white), color-stop(0.99, #eeeeee));
        background: url('/img/multiple-select.png') no-repeat 100% -22px, -webkit-linear-gradient(center bottom, white 85%, #eeeeee 99%);
        background: url('/img/multiple-select.png') no-repeat 100% -22px, -moz-linear-gradient(center bottom, white 85%, #eeeeee 99%);
        background: url('/img/multiple-select.png') no-repeat 100% -22px, -o-linear-gradient(bottom, white 85%, #eeeeee 99%);
        background: url('/img/multiple-select.png') no-repeat 100% -22px, -ms-linear-gradient(top, #ffffff 85%, #eeeeee 99%);
        background: url('/img/multiple-select.png') no-repeat 100% -22px, linear-gradient(top, #ffffff 85%, #eeeeee 99%);
    }

    .ms-search, .ms-search input {
        -webkit-box-sizing: border-box;
        -khtml-box-sizing: border-box;
        -moz-box-sizing: border-box;
        -ms-box-sizing: border-box;
        box-sizing: border-box;
    }

    .ms-drop ul {
        overflow: auto;
        margin: 0;
        padding: 5px 8px;
    }

    .ms-drop ul > li {
        list-style: none;
        display: list-item;
        background-image: none;
        position: static;
    }

    .ms-drop ul > li .disabled {
        opacity: .35;
        filter: Alpha(Opacity=35);
    }

    .ms-drop ul > li.multiple {
        display: block;
        float: left;
    }

    .ms-drop ul > li.group {
        clear: both;
    }

    .ms-drop ul > li.multiple label {
        width: 100%;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .ms-drop ul > li label {
        font-weight: normal;
        display: block;
        white-space: nowrap;
    }

    .ms-drop ul > li label.optgroup {
        font-weight: bold;
    }

    .ms-drop input[type="checkbox"] {
        vertical-align: middle;
    }

    .ms-drop .ms-no-results {
        display: none;
    }
    /* Fin Custom Multiple-select*/
</style>