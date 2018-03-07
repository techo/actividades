<template>
    <div class="row" id="filtro">
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-4">
                    <select title="Categorías" name="categorias" v-on:change="verActividades" v-model="idCategoria">
                        <option value="1">Actividades en Asentamientos</option>
                        <option value="2">Eventos Especiales</option>
                        <option value="3">Actividades en Oficina</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select title="Tipos" name="tipos">
                        <option value="">Todas las actividades</option>
                        <option v-for="actividad in actividades" v-bind:value="actividad.idTipo">
                            {{ actividad.nombre }}
                        </option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select title="Zonas" name="zonas">
                        <option value="">Todas las regiones</option>
                        <option value="1">Buenos Aires</option>
                        <option value="2">Capital Federal</option>
                        <option value="3">Otro</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="row">
                <div class="col-md-6">
                    <button class="btn btn-danger btn-sm">Borra Filtros</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        name: "filtro",
        props: ['categoria'],
        data () {
             return {
                 actividades: [],
                 idCategoria: this.categoria
             }
        },
        methods: {
            verActividades: function () {
                console.log('/ajax/tipos/' + this.idCategoria);
                axios.get(
                    '/ajax/tipos/' + this.idCategoria,
                    )
                    .then(response => {
                        console.log(response.data);
                        this.actividades = response.data;
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


            }
        },
        created: function() {
            this.verActividades();
        }
    }
</script>