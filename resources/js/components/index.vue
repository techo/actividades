<template>
    <span>

        <imgButtonGroup />
        <div v-show="loading" class="loading" style="text-align: center">
            <i class="fas fa-sync fa-spin fa-3x"></i>
        </div>
        <div v-show="!loading">
            <carousel-de-tarjetas
                v-show="actividadesUltimosCupos && actividadesUltimosCupos.length > 0"
                :actividades="actividadesUltimosCupos || []"
                :title="$t('frontend.home_last_spots')"
            />
            <carousel-de-tarjetas
                v-show="actividadesPorCategoria[1] && actividadesPorCategoria[1].length > 0"
                :actividades="actividadesPorCategoria[1] || []"
                :title="$t('frontend.home_community')"
            />
            <carousel-de-tarjetas
                v-show="actividadesPorCategoria[2] && actividadesPorCategoria[2].length > 0"
                :actividades="actividadesPorCategoria[2] || []"
                :title="$t('frontend.home_formation')"
            />
            <carousel-de-tarjetas
                v-show="actividadesNuevas && actividadesNuevas.length > 0"
                :actividades="actividadesNuevas || []"
                :title="$t('frontend.home_new')"
            />
            <carousel-de-tarjetas
                v-show="actividadesNuevosVoluntarios && actividadesNuevosVoluntarios.length > 0"
                :actividades="actividadesNuevosVoluntarios || []"
                :title="$t('frontend.home_for_new_volunteers')"
            />
            <carousel-de-tarjetas
                v-show="actividadesHitoAnual && actividadesHitoAnual.length > 0"
                :actividades="actividadesHitoAnual || []"
                :title="$t('frontend.home_specials')"
            />
            <carousel-de-tarjetas
                v-show="actividadesEquipos && actividadesEquipos.length > 0"
                :actividades="actividadesEquipos || []"
                :title="$t('frontend.home_teams')"
            /> 
                
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
                actividadesPorCategoria: {},
                actividadesNuevosVoluntarios: [],
                actividadesHitoAnual: [],
                actividadesUltimosCupos:[],
                actividadesEquipos: [],
                actividadesNuevas: [],
                loading: false,
                next_page: '',
                bottom: false,
                url: '/ajax/actividades',
                ultimaTarjeta: 0,
                totalTarjetas: 0,
                vacio: false,
                filtros: {},
            }
        },
        props: {
            categorias: {
                type: Array
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
            scrollLeft() {
                const container = this.$el.querySelector('.scroll-container');
                container.scrollBy({ left: -200, behavior: 'smooth' });
            },
            scrollRight() {
                const container = this.$el.querySelector('.scroll-container');
                container.scrollBy({ left: 200, behavior: 'smooth' });
            },
            async agregarTarjetas(url, filtros, refresh, categoria) {
                this.vacio = false;
                filtros.categoria = categoria;

                try {
                    const response = await axios.get(url, { params: filtros });

                    if (refresh) {
                        this.actividadesPorCategoria[categoria] = [];
                    }
                    if (response.data) {
                        response.data.data.forEach(actividad => {
                            console.log('tags:', actividad.fechaCreacion);
                            if(actividad.actividades_tags){
                                actividad.actividades_tags.forEach(item => {
                                    if (item.text === "Equipos") {
                                        // Aquí puedes hacer lo que necesites si es "Equipos"
                                        this.actividadesEquipos.push(actividad);
                                    } else if (item.text === "Nuevos Voluntarios")  {
                                        console.log(`Este es ${item.text}`);
                                        this.actividadesNuevosVoluntarios.push(actividad);
                                    } else if (item.text === "Hito Anual")  {
                                        console.log(`Este es ${item.text}`);
                                        this.actividadesHitoAnual.push(actividad);
                                    } else if (item.text === "Últimos Cupos")  {
                                        console.log(`Este es ${item.text}`);
                                        this.actividadesUltimosCupos.push(actividad);
                                    }
                                });
                            }

                            const hoy = new Date();
                            const hace14Dias = new Date();
                            hace14Dias.setDate(hoy.getDate() - 14);
                            const [day, month, year] = actividad.fechaCreacion.split('-');
                            if(new Date(`${year}-${month}-${day}`) >= hace14Dias){
                                this.actividadesNuevas.push(actividad);
                            }

                        });
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