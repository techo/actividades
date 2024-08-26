<template>
    <div class="tarjetas-agrupadas">
        <div class="contenedor-titulo-controles">
            <h3>{{title}}</h3>
            <div class="indicadores"></div>
        </div>

        <div class="contenedor-principal">
            <button role="button" id="flecha-izquierda" class="flecha-izquierda">
                <i class="fa-solid fa-chevron-left"></i>
            </button>

            <div class="contenedor-carousel">
                <div class="carousel">
                    <tarjeta
                        class="tarjeta"
                        v-for="act in actividades"
                        v-bind:actividad="act"
                        v-bind:key="Math.random() + '_' + act.idActividad"
                    >
                    </tarjeta>
                </div>
            </div>

            <button role="button" id="flecha-derecha" class="flecha-derecha">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </div>

</template>

<style scoped>
/*-------------- Contenedor Título y Controles -----------------*/
.contenedor-titulo-controles {
    display: flex;
    justify-content: space-between;
    align-items: end;
}

.contenedor-titulo-controles h3{
    color: black;
    font-size: 30px;
}

.contenedor-titulo-controles .indicadores button{
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
    background: rgba(0,0,0,0);
    font-size: 45px;
    height: 50%;
    top: calc(50% - 25%);
    line-height: 40px;
    width: 50px;
    color: black;
    cursor: pointer;
    z-index: 500;
    transition: .2s ease all;
    outline: none;
}

/* .tarjetas-agrupadas .contenedor-principal .flecha-izquierda:hover,
.tarjetas-agrupadas .contenedor-principal .flecha-derecha:hover {
    background: rgba(0,0,0, .9);
} */

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
    transition: .3 ease all;
}

.tarjetas-agrupadas .contenedor-carousel .carousel .tarjeta img {
    width: 100%;
    vertical-align: top;
}

/*-------------- Media Queries -----------------*/
@media screen and (max-width: 800px) {
    .tarjetas-agrupadas .contenedor-carousel .carousel .tarjeta {
        min-width: 50%;
    }


    /* .tarjetas-agrupadas .contenedor-carousel {
        overflow: visible;
    }

    .tarjetas-agrupadas .contenedor-carousel .carousel {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }

    .tarjetas-agrupadas .indicadores,
    .tarjetas-agrupadas .flecha-izquierda,
    .tarjetas-agrupadas .flecha-derecha {
        display: none;
    } */
}

@media screen and (max-width: 425px) {
    .tarjetas-agrupadas .contenedor-carousel .carousel .tarjeta {
        min-width: 100%;
    }
}

</style>

<script>
    import Tarjeta from './tarjeta';

    export default {
        components: {Tarjeta},
        props:{
            actividades: {
                type: Array,
                required: true
            },
            title: {
                type: String,
                required: true
            },
        },
        mounted() {
            const fila = document.querySelector(".contenedor-carousel");
            const flechaIzquierda = document.getElementById("flecha-izquierda");
            const flechaDerecha = document.getElementById("flecha-derecha");

            // Función para actualizar la visibilidad de las flechas
            const actualizarFlechas = () => {
                // Verificar si el scroll está al inicio
                if (fila.scrollLeft === 0) {
                    flechaIzquierda.classList.add('text-muted');
                    flechaIzquierda.style.cursor = 'default';
                } else {
                    flechaIzquierda.classList.remove('text-muted');
                    flechaIzquierda.style.cursor = 'pointer';
                }

                // Verificar si el scroll está al final
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

            // Inicializar la visibilidad de las flechas
            actualizarFlechas();

            // Listener para el scroll horizontal
            fila.addEventListener('scroll', actualizarFlechas);

            /*-------------- Event listener para flecha derecha -----------------*/
            flechaDerecha.addEventListener("click", () => {
                fila.scrollLeft += fila.offsetWidth;
            });

            /*-------------- Event listener para flecha izquierda -----------------*/
            flechaIzquierda.addEventListener("click", () => {
                fila.scrollLeft -= fila.offsetWidth;
            });
        },
        methods: {
            actualizarNumeroDeActividades() {
                // Contar el número de elementos en el array actividades
                const numeroDeActividades = this.actividades.length;
            }
        },
        watch: {
            actividades: function(newVal) {
                // Reaccionar a los cambios en `actividades`
                this.actualizarNumeroDeActividades();

                const desktop = window.matchMedia('(min-width: 801px)').matches;
                const tablet = window.matchMedia('(max-width: 800px)').matches;
                const mobile = window.matchMedia('(max-width: 425px)').matches;
                const flechaIzquierda = document.getElementById("flecha-izquierda");
                const flechaDerecha = document.getElementById("flecha-derecha");

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
            }
        }
    }
</script>


