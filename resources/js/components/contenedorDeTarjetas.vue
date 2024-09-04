<template>
    <span>
        <!-- <div class="row py-2"> -->
            <carousel-de-tarjetas v-show="!vacio && !loading.cat1"  :actividades="actividades" :title="'Con el pie en territorio, todas las actividades en comunidad.'"/>
            <div v-show="loading.cat1" class="loading" style="text-align: center">
                <i class="fas fa-sync fa-spin fa-3x"></i>
            </div>

            <carousel-de-tarjetas v-show="!vacio && !loading.cat2"  :actividades="actividades2" :title="'Fortalece tus capacidades Capacitaciones, Espacios formativos y Talleres'"/>
            <div v-show="loading.cat2" class="loading" style="text-align: center">
                <i class="fas fa-sync fa-spin fa-3x"></i>
            </div>
        <!-- </div> -->

        <!-- <div v-show="loading" class="loading" style="text-align: center">
            <i class="fas fa-sync fa-spin fa-3x"></i>
        </div> -->
        <div v-show="vacio" class="loading card card-box" style="text-align: center">
            <Suscribe :filtros="filtros" />
        </div>
    </span>
</template>

<script>
    import axios from 'axios';
    import Tarjeta from './tarjeta';
    import Suscribe from './suscribe';
    import CarouselDeTarjetas from './carouselDeTarjetas.vue';

    export default {
        name: "contenedor-de-tarjetas",

        data () {
            return {
                actividades: [],
                actividades2: [],
                loading: { cat1: false, cat2: false },
                next_page: '',
                bottom: false,
                url: '/ajax/actividades',
                ultimaTarjeta: 0,
                totalTarjetas: 0,
                vacio: false,
                filtros: {}
            }
        },
        components: {Suscribe, tarjeta: Tarjeta},
        created () {
            window.addEventListener('scroll', () => {
                this.bottom = this.bottomVisible()
            });
            window.addEventListener('cargarTarjetas', async (event) => {
                // event.detail.categoria = 2;
                this.filtros = event.detail;
                this.actividades = [];
                this.actividades2 = [];
                
                // this.agregarTarjetas(this.url, this.filtros, true, 1);
                // this.agregarTarjetas(this.url, this.filtros, true, 2);
                await this.cargarTarjetas();
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
            async agregarTarjetas(url, filtros, refresh, categoria) {

                //forma cabeza de evitar concurrencia
                if (this.loading[`cat${categoria}`]) { return; };

                let self = this;
                this.loading[`cat${categoria}`] = true;
                this.vacio = false;
                filtros.categoria = categoria;

                axios.get(url, { params:  filtros })
                    .then(response => {
                        console.log(response.data);

                        if(typeof response.data.data == "undefined" || response.data.data.length == 0) {
                            this.loading[`cat${categoria}`] = false;
                            this.vacio = true;
                        }
                        
                        if (refresh) {
                            if (categoria === 1) {
                                this.actividades = [];
                            } else if (categoria === 2) {
                                this.actividades2 = [];
                            }
                        }

                        for (let element in response.data.data) {
                            if (categoria == 1) {
                                self.actividades.push(response.data.data[element]);
                            }
                            if (categoria == 2) {
                                self.actividades2.push(response.data.data[element]);
                            }
                        }
                        
                        this.next_page = response.data.next_page_url;
                        this.ultimaTarjeta = response.data.to;
                        this.totalTarjetas = response.data.total;

                        this.loading[`cat${categoria}`] = false;

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
            async cargarTarjetas() {
                // Primero cargar la categoría 2
                await this.agregarTarjetas(this.url, this.filtros, true, 1);
                // Luego cargar la categoría 1
                await this.agregarTarjetas(this.url, this.filtros, true, 2);
            }
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