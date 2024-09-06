<template>
    <span>
        <div v-show="loading" class="loading" style="text-align: center">
            <i class="fas fa-sync fa-spin fa-3x"></i>
        </div>
        <div v-show="!loading">
            <div v-for="categoria in categorias" :key="categoria">
                <carousel-de-tarjetas
                    v-show="actividadesPorCategoria[categoria] && actividadesPorCategoria[categoria].length > 0"
                    :actividades="actividadesPorCategoria[categoria] || []"
                    :title="'Actividades para la categoría ' + categoria"
                />
            </div>
        </div>
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
                categorias: [1,2,3,4],
                actividadesPorCategoria: {},
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
        components: { Suscribe, tarjeta: Tarjeta, CarouselDeTarjetas },

        created () {
            this.inicializarActividadesPorCategoria();
            window.addEventListener('scroll', () => {
                this.bottom = this.bottomVisible();
            });
            window.addEventListener('cargarTarjetas', async (event) => {
                this.filtros = event.detail;
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
            inicializarActividadesPorCategoria() {
                this.categorias.forEach(categoria => {
                    this.$set(this.actividadesPorCategoria, categoria, []);
                });
            },
            async agregarTarjetas(url, filtros, refresh, categoria) {
                this.vacio = false;
                filtros.categoria = categoria;

                try {
                    const response = await axios.get(url, { params: filtros });

                    if (refresh) {
                        this.actividadesPorCategoria[categoria] = [];
                    }

                    this.actividadesPorCategoria[categoria].push(...response.data.data);

                    this.next_page = response.data.next_page_url;
                    this.ultimaTarjeta = response.data.to;
                    this.totalTarjetas = response.data.total;
                } catch (error) {
                    console.error('Error en contenedor de tarjetas', error);
                }
            },
            async cargarTarjetas() {
                this.loading = true;
                for (let categoria of this.categorias) {
                    await this.agregarTarjetas(this.url, this.filtros, true, categoria);
                }
                this.loading = false;
                this.vacio = !Object.values(this.actividadesPorCategoria).some(actividades => actividades.length > 0);
            }
        },

        watch: {
            bottom(bottom) {
                if (bottom && this.ultimaTarjeta && this.totalTarjetas) {
                    if (this.ultimaTarjeta < this.totalTarjetas) {
                        this.agregarTarjetas(this.next_page, this.filtros, false);
                    }
                }
            }
        },
    }
</script>

<style scoped>
</style>