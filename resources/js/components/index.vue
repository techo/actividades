<template>
    <span>

        <imgButtonGroup />
        <div v-show="loading" class="loading" style="text-align: center">
            <i class="fas fa-sync fa-spin fa-3x"></i>
        </div>
        <div v-show="!loading">
            <carousel-destacadas
                v-show="actividadesDestacadas && actividadesDestacadas.length > 0"
                :actividades="actividadesDestacadas || []"
            />
            <carousel-de-tarjetas
                v-show="actividadesUltimosCupos && actividadesUltimosCupos.length > 0"
                :actividades="actividadesUltimosCupos || []"
                :title="$t('frontend.home_last_spots')"
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
                v-for="categoria in listaCategorias"
                :key="'categoria_' + categoria.id"
                v-show="actividadesPorCategoria[categoria.id] && actividadesPorCategoria[categoria.id].length > 0"
                :actividades="actividadesPorCategoria[categoria.id] || []"
                :title="tituloCategoria(categoria)"
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
                // Estado de paginación independiente por categoría:
                // { [categoriaId]: { nextPage, to, total } }. Antes había una sola
                // terna global (next_page/ultimaTarjeta/totalTarjetas) que el loop de
                // categorías pisaba en cada vuelta, así que solo la última categoría
                // podía paginar y el scroll infinito apuntaba a la categoría equivocada.
                paginacionPorCategoria: {},
                actividadesNuevosVoluntarios: [],
                actividadesHitoAnual: [],
                actividadesUltimosCupos:[],
                actividadesEquipos: [],
                actividadesNuevas: [],
                actividadesDestacadas: [],
                loading: false,
                // Evita disparar múltiples cargas de "página siguiente" en simultáneo
                // mientras el usuario sigue en el fondo de la página.
                cargandoMas: false,
                bottom: false,
                url: '/ajax/actividades',
                vacio: false,
                filtros: {},
                // Se completa en created() parseando el prop `categorias` (JSON).
                listaCategorias: [],
            }
        },
        props: {
            // JSON de las categorías (CategoriaActividad::all()) inyectado por el
            // Blade como `categorias="{{ $categorias }}"`, igual que <filtro>.
            categorias: {
                type: String,
                default: '[]',
            }
        },
        components: { Suscribe, tarjeta: Tarjeta, CarouselDeTarjetas},

        created () {
            this.listaCategorias = JSON.parse(this.categorias);
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
                this.listaCategorias.forEach(categoria => {
                    this.$set(this.actividadesPorCategoria, categoria.id, []);
                    this.$set(this.paginacionPorCategoria, categoria.id, {
                        nextPage: null,
                        to: 0,
                        total: 0,
                    });
                });
            },
            // Vacía todas las colecciones antes de una carga completa (ej. al aplicar
            // filtros nuevos). Sin esto, las secciones curadas (Destacadas, Nuevas,
            // Equipos, etc.) acumulaban tarjetas duplicadas en cada re-filtrado.
            reiniciarColecciones() {
                this.actividadesNuevosVoluntarios = [];
                this.actividadesHitoAnual = [];
                this.actividadesUltimosCupos = [];
                this.actividadesEquipos = [];
                this.actividadesNuevas = [];
                this.actividadesDestacadas = [];
                this.inicializarActividadesPorCategoria();
            },
            // Reparte una actividad en las secciones curadas (por tags, imagen
            // destacada y antigüedad). Es acumulativa: se llama tanto en la carga
            // inicial como al traer páginas siguientes.
            clasificarActividad(actividad) {
                if (actividad.actividades_tags) {
                    actividad.actividades_tags.forEach(item => {
                        if (item.text === "Equipos") {
                            this.actividadesEquipos.push(actividad);
                        } else if (item.text === "Nuevos Voluntarios") {
                            this.actividadesNuevosVoluntarios.push(actividad);
                        } else if (item.text === "Hito Anual") {
                            this.actividadesHitoAnual.push(actividad);
                        } else if (item.text === "Últimos Cupos") {
                            this.actividadesUltimosCupos.push(actividad);
                        }
                    });
                }
                if (actividad.imagen_destacada) {
                    this.actividadesDestacadas.push(actividad);
                }
                if (actividad.fechaCreacion) {
                    const hoy = new Date();
                    const hace14Dias = new Date();
                    hace14Dias.setDate(hoy.getDate() - 14);
                    const [day, month, year] = actividad.fechaCreacion.split('-');
                    if (new Date(`${year}-${month}-${day}`) >= hace14Dias) {
                        this.actividadesNuevas.push(actividad);
                    }
                }
            },
            // Títulos curados por i18n para las categorías conocidas; el resto
            // (incluyendo categorías nuevas) cae al nombre de la categoría en la DB.
            tituloCategoria(categoria) {
                const claves = {
                    1: 'frontend.home_community',
                    2: 'frontend.home_formation',
                    5: 'frontend.home_campaign',
                };
                return claves[categoria.id]
                    ? this.$t(claves[categoria.id])
                    : categoria.nombre;
            },
            scrollLeft() {
                const container = this.$el.querySelector('.scroll-container');
                container.scrollBy({ left: -200, behavior: 'smooth' });
            },
            scrollRight() {
                const container = this.$el.querySelector('.scroll-container');
                container.scrollBy({ left: 200, behavior: 'smooth' });
            },
            // Trae una página de una categoría y la agrega a su carrusel.
            // - Sin `url`: primera página → usa this.url + filtros (con la categoría).
            // - Con `url`: paginación → el next_page_url ya trae los filtros embebidos,
            //   así que se pide tal cual (no se re-agregan params para no duplicarlos).
            async traerPaginaCategoria(categoriaId, url = null) {
                const esInicial = !url;
                const requestUrl = url || this.url;
                const config = esInicial
                    ? { params: { ...this.filtros, categoria: categoriaId } }
                    : {};

                try {
                    const response = await axios.get(requestUrl, config);
                    const paginador = response.data || {};
                    const actividades = Array.isArray(paginador.data) ? paginador.data : [];

                    actividades.forEach(actividad => this.clasificarActividad(actividad));

                    if (this.actividadesPorCategoria[categoriaId]) {
                        this.actividadesPorCategoria[categoriaId].push(...actividades);
                    }

                    this.$set(this.paginacionPorCategoria, categoriaId, {
                        nextPage: paginador.next_page_url || null,
                        to: paginador.to || 0,
                        total: paginador.total || 0,
                    });
                } catch (error) {
                    console.error('Error en contenedor de tarjetas', error);
                }
            },
            async cargarTarjetas() {
                this.loading = true;
                this.vacio = false;
                this.reiniciarColecciones();

                for (const categoria of this.listaCategorias) {
                    await this.traerPaginaCategoria(categoria.id);
                }

                this.loading = false;
                this.vacio = !Object.values(this.actividadesPorCategoria)
                    .some(actividades => actividades.length > 0);
            },
            // Scroll infinito: por cada categoría que aún tenga páginas pendientes,
            // trae la siguiente y la agrega a su propio carrusel.
            async cargarMas() {
                if (this.cargandoMas) {
                    return;
                }

                const pendientes = this.listaCategorias.filter(categoria => {
                    const pag = this.paginacionPorCategoria[categoria.id];
                    return pag && pag.nextPage;
                });

                if (!pendientes.length) {
                    return;
                }

                this.cargandoMas = true;
                try {
                    for (const categoria of pendientes) {
                        const pag = this.paginacionPorCategoria[categoria.id];
                        await this.traerPaginaCategoria(categoria.id, pag.nextPage);
                    }
                } finally {
                    this.cargandoMas = false;
                }
            }
        },

        watch: {
            bottom(bottom) {
                if (bottom) {
                    this.cargarMas();
                }
            }
        },
    }
</script>

<style scoped>
</style>
