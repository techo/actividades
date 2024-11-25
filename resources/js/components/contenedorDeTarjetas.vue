<template>
    <span >
        <div class="row py-2" v-if="horizontal">
            <TarjetaHori 
                v-for="(act, index) in actividades" 
                :key="'tarjeta_' + act.idActividad" 
                v-bind:actividad="act"
                v-bind:key="Math.random() + '_' + act.idActividad"
                :color="colores[index % colores.length]"
            >
            </TarjetaHori>
        </div>
        <div class="row py-2" v-else>
            <tarjeta 
                v-for="act in actividades"
                v-bind:actividad="act"
                v-bind:key="Math.random() + '_' + act.idActividad"
            >
            </tarjeta>
        </div>

        <div v-show="loading" class="loading" style="text-align: center">
            <i class="fas fa-sync fa-spin fa-3x"></i>
        </div>
        <div v-show="vacio" class="loading card card-box" style="text-align: center">
            <Suscribe :filtros="filtros" />
        </div>
    </span>
</template>

<script>
    import axios from 'axios';
    import Tarjeta from './tarjeta';
    import TarjetaHori from './tarjetaHorizontal';
    import Suscribe from './suscribe';

    export default {
        name: "contenedor-de-tarjetas",
        props: ['horizontal'],

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
                filtros: {},
                colores: ['#e94362', '#fdc533', '#f088b6', '#954B97', '#009fe3'],
            }
        },
        components: {Suscribe, tarjeta: Tarjeta, TarjetaHori },
        created () {
            window.addEventListener('scroll', () => {
                this.bottom = this.bottomVisible()
            });
            window.addEventListener('cargarTarjetas', (event) => {
                this.filtros = event.detail;
                this.actividades = [];
                this.agregarTarjetas(this.url, this.filtros, true);
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
            agregarTarjetas(url, filtros, refresh) {

                //forma cabeza de evitar concurrencia
                if(this.loading) {return;};

                let self = this;
                this.loading = true;
                this.vacio = false;
                axios.get(url, { params:  filtros })
                    .then(response => {
                        if(typeof response.data.data == "undefined" || response.data.data.length == 0) {
                            this.loading = false;
                            this.vacio = true;
                        }
                        
                        if (refresh) {
                            self.actividades = [];
                        }

                        for (let element in response.data.data) {
                            self.actividades.push(response.data.data[element]);
                        }
                        this.next_page = response.data.next_page_url;
                        this.ultimaTarjeta = response.data.to;
                        this.totalTarjetas = response.data.total;

                        this.loading = false;

                    })
                    .catch((error) => {
                        // Error
                        console.error('error en contenedor de tarjetas');
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
                if (bottom && this.ultimaTarjeta && this.totalTarjetas) {
                    if (this.ultimaTarjeta < this.totalTarjetas ) {
                        this.agregarTarjetas(this.next_page, this.filtros, false);
                    }
                }
            }
        },
    }
</script>

<style scoped>

</style>