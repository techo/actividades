<template>
    <div class="tarjetas-agrupadas">
    <div class="contenedor-titulo-controles">
        <h4>{{ title }}</h4>
        <div class="indicadores"></div>
    </div>

    <div class="contenedor-principal">
        <button ref="flechaIzquierda" role="button" class="flecha-izquierda">
            <
        </button>

        <div ref="contenedorCarousel" class="contenedor-carousel">
        <div class="carousel">
            <tarjeta
            class="tarjeta"
            v-for="act in actividades"
            :key="act.idActividad"
            :actividad="act"
            />
        </div>
        </div>

        <button ref="flechaDerecha" role="button" class="flecha-derecha">
            >
        </button>
    </div>
    </div>
</template>

<script>
import Tarjeta from './tarjeta';

export default {
    components: { Tarjeta },
    props: {
        actividades: {
            type: Array,
            required: true,
        },
        title: {
            type: String,
            required: true,
        }
    },
    mounted() {
        const fila = this.$refs.contenedorCarousel;
        const flechaIzquierda = this.$refs.flechaIzquierda;
        const flechaDerecha = this.$refs.flechaDerecha;

        const actualizarFlechas = () => {
            if (fila.scrollLeft === 0) {
            flechaIzquierda.classList.add('text-muted');
            flechaIzquierda.style.cursor = 'default';
            } else {
            flechaIzquierda.classList.remove('text-muted');
            flechaIzquierda.style.cursor = 'pointer';
            }

            const maxScrollLeft = fila.scrollWidth - fila.clientWidth;
            if (fila.scrollLeft >= maxScrollLeft && maxScrollLeft != 0) {
            flechaDerecha.classList.add('text-muted');
            flechaDerecha.style.cursor = 'default';
            } else {
            flechaDerecha.classList.remove('text-muted');
            flechaDerecha.style.cursor = 'pointer';
            }
    };

    this.actualizarNumeroDeActividades();

    actualizarFlechas();

    fila.addEventListener('scroll', actualizarFlechas);

    flechaDerecha.addEventListener('click', () => {
        fila.scrollLeft += fila.offsetWidth;
    });

    flechaIzquierda.addEventListener('click', () => {
        fila.scrollLeft -= fila.offsetWidth;
    });
    },
    methods: {
    actualizarNumeroDeActividades() {
        const numeroDeActividades = this.actividades.length;
    },
    },
    watch: {
        actividades() {
            this.actualizarNumeroDeActividades();

            const desktop = window.matchMedia('(min-width: 801px)').matches;
            const tablet = window.matchMedia('(max-width: 800px)').matches;
            const mobile = window.matchMedia('(max-width: 425px)').matches;
            const flechaIzquierda = this.$refs.flechaIzquierda;
            const flechaDerecha = this.$refs.flechaDerecha;

            if (desktop) {
                if (this.actividades.length <= 3) {
                    flechaIzquierda.style.display = 'none';
                    flechaDerecha.style.display = 'none';
                } else {
                    flechaIzquierda.style.display = 'block';
                    flechaDerecha.style.display = 'block';
                }
            }
            if (tablet) {
                if (this.actividades.length <= 2) {
                    flechaIzquierda.style.display = 'none';
                    flechaDerecha.style.display = 'none';
                } else {
                    flechaIzquierda.style.display = 'block';
                    flechaDerecha.style.display = 'block';
                }
            }
            if (mobile) {
                if (this.actividades.length <= 1) {
                    flechaIzquierda.style.display = 'none';
                    flechaDerecha.style.display = 'none';
                } else {
                    flechaIzquierda.style.display = 'block';
                    flechaDerecha.style.display = 'block';
                }
            }
        },
    },
};
</script>

<style scoped>
/*-------------- Contenedor Título y Controles -----------------*/
.contenedor-titulo-controles {
    display: flex;
    justify-content: space-between;
    align-items: end;
}

.contenedor-titulo-controles h3 {
    color: black;
    font-size: 30px;
}

.contenedor-titulo-controles .indicadores button {
    background: black;
    height: 3px;
    width: 10px;
    cursor: pointer;
    border: none;
    margin-right: 2px;
}

.contenedor-titulo-controles .indicadores button:hover,
.contenedor-titulo-controles .indicadores button.activo {
    background: red;
}

/*-------------- Contenedor principal -----------------*/
.tarjetas-agrupadas {
    margin-bottom: 70px;
}

.tarjetas-agrupadas .contenedor-principal {
    display: flex;
    align-items: center;
    position: relative;
}

.tarjetas-agrupadas .contenedor-principal .flecha-izquierda,
.tarjetas-agrupadas .contenedor-principal .flecha-derecha {
    position: absolute;
    border: none;
    background: rgba(0, 0, 0, 0);
    font-size: 45px;
    height: 50%;
    top: calc(50% - 25%);
    line-height: 40px;
    width: 50px;
    color: black;
    cursor: pointer;
    z-index: 500;
    transition: 0.2s ease all;
    outline: none;
}

.tarjetas-agrupadas .contenedor-principal .flecha-izquierda {
    left: -26px;
}

.tarjetas-agrupadas .contenedor-principal .flecha-derecha {
    right: -26px;
}

/*-------------- Carousel -----------------*/
.tarjetas-agrupadas .contenedor-carousel {
    width: 100%;
    padding: 20px 0px;
    overflow: hidden;
    scroll-behavior: smooth;
}

.tarjetas-agrupadas .contenedor-carousel .carousel {
    display: flex;
    flex-wrap: nowrap;
}

/* Ajusta la cantidad de tarjetas */
.tarjetas-agrupadas .contenedor-carousel .carousel .tarjeta {
    min-width: 25%;
    transition: 0.3 ease all;
}

.tarjetas-agrupadas .contenedor-carousel .carousel .tarjeta img {
    width: 100%;
    vertical-align: top;
}

/*-------------- Media Queries -----------------*/
@media screen and (max-width: 991px) {
    .tarjetas-agrupadas .contenedor-carousel .carousel .tarjeta {
        min-width: 50%;
    }
}

@media screen and (max-width: 767px) {
    .tarjetas-agrupadas .contenedor-carousel .carousel .tarjeta {
        min-width: 100%;
    }
}
</style>