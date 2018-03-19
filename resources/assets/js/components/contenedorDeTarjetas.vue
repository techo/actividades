<template>
    <span>
        <div class="row">
            <div class="card-deck mb-3 text-center">
                <tarjeta
                    v-for="act in actividades"
                    v-bind:actividad="act"
                    v-bind:key="Math.random() + '_' + act.idActividad"
                >
                </tarjeta>
            </div>
        </div>

        <div v-show="loading" class="loading" style="text-align: center">
            <i class="fas fa-sync fa-spin fa-5x"></i>
        </div>
        <div v-show="vacio" class="loading" style="text-align: center">
            La búsqueda no tiene resultados
        </div>
    </span>
</template>

<script>
    import axios from 'axios';
    import Tarjeta from './tarjeta';

    export default {
        name: "contenedor-de-tarjetas",

        data () {
            return {
                actividades: [],
                loading: false,
                next_page: '',
                bottom: false,
                url: '/ajax/actividades',
                ultimaTarjeta: 0,
                totalTarjetas: 0,
                vacio: false,
                filtros: {}
            }
        },
        components: {tarjeta: Tarjeta},
        created () {
            window.addEventListener('scroll', () => {
                this.bottom = this.bottomVisible()
            });
            window.addEventListener('cargarTarjetas', (event) => {
                this.filtros = event.detail;
                this.actividades = [];
                this.agregarTarjetas(this.url, this.filtros);
            });
        },
        methods: {

            bottomVisible() {
                const scrollY = window.scrollY;
                const visible = document.documentElement.clientHeight;
                const pageHeight = document.documentElement.scrollHeight;
                const bottomOfPage = visible + scrollY >= pageHeight;
                return bottomOfPage || pageHeight < visible;
            },
            agregarTarjetas(url, filtros) {
                let self = this;
                this.loading = true;
                this.vacio = false;
                axios.post(url, filtros)
                    .then(response => {
                        // console.log(response.data.data);
                        if(typeof response.data.data == "undefined" || response.data.data.length == 0) {
                            this.loading = false;
                            this.vacio = true;
                        }
                        for (let element in response.data.data) {
                            self.actividades.push(response.data.data[element]);
                        }
                        this.next_page = response.data.next_page_url;
                        this.ultimaTarjeta = response.data.to;
                        this.totalTarjetas = response.data.total;

                        if (this.ultimaTarjeta === this.totalTarjetas){
                            this.loading = false;
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

        },

        watch: {
            bottom(bottom) {
                if (bottom) {
                    if (this.ultimaTarjeta < this.totalTarjetas ) {
                        this.agregarTarjetas(this.next_page, this.filtros);
                    }
                }
            }
        },
    }
</script>

<style scoped>

</style>