<template>
    <!-- <div class="carousel-container">
        <div class="carousel">
            <tarjeta
                v-for="act in actividades"
                v-bind:actividad="act"
                v-bind:key="Math.random() + '_' + act.idActividad"
            >
            </tarjeta>
        </div>
    </div> -->

    <div class="peliculas-recomendadas">
        <div class="contenedor-titulo-controles">
            <h3>peliculas</h3>
            <div class="indicadores"></div>
        </div>

        <div class="contenedor-principal">
            <button role="button" id="flecha-izquierda" class="flecha-izquierda">
                <i class="fa-solid fa-chevron-left"></i>
            </button>

            <div class="contenedor-carousel">
                <div class="carousel">
                    <div class="pelicula">
                        <a href="#"><img src="https://www.cronista.com/files/image/390/390945/617737dd5a01a.jpg" alt=""></a>
                    </div>
                    <div class="pelicula">
                        <a href="#"><img src="https://www.cronista.com/files/image/390/390945/617737dd5a01a.jpg" alt=""></a>
                    </div>
                    <div class="pelicula">
                        <a href="#"><img src="https://www.cronista.com/files/image/390/390945/617737dd5a01a.jpg" alt=""></a>
                    </div>
                    <div class="pelicula">
                        <a href="#"><img src="https://www.cronista.com/files/image/390/390945/617737dd5a01a.jpg" alt=""></a>
                    </div>
                    <div class="pelicula">
                        <a href="#"><img src="https://www.cronista.com/files/image/390/390945/617737dd5a01a.jpg" alt=""></a>
                    </div>
                    <div class="pelicula">
                        <a href="#"><img src="https://www.cronista.com/files/image/390/390945/617737dd5a01a.jpg" alt=""></a>
                    </div>
                    <div class="pelicula">
                        <a href="#"><img src="https://www.cronista.com/files/image/390/390945/617737dd5a01a.jpg" alt=""></a>
                    </div>
                    <div class="pelicula">
                        <a href="#"><img src="https://www.cronista.com/files/image/390/390945/617737dd5a01a.jpg" alt=""></a>
                    </div>
                    <div class="pelicula">
                        <a href="#"><img src="https://www.cronista.com/files/image/390/390945/617737dd5a01a.jpg" alt=""></a>
                    </div>
                    <div class="pelicula">
                        <a href="#"><img src="https://www.cronista.com/files/image/390/390945/617737dd5a01a.jpg" alt=""></a>
                    </div>
                    <div class="pelicula">
                        <a href="#"><img src="https://www.cronista.com/files/image/390/390945/617737dd5a01a.jpg" alt=""></a>
                    </div>
                    <div class="pelicula">
                        <a href="#"><img src="https://www.cronista.com/files/image/390/390945/617737dd5a01a.jpg" alt=""></a>
                    </div>
                    <div class="pelicula">
                        <a href="#"><img src="https://www.cronista.com/files/image/390/390945/617737dd5a01a.jpg" alt=""></a>
                    </div>
                    <div class="pelicula">
                        <a href="#"><img src="https://www.cronista.com/files/image/390/390945/617737dd5a01a.jpg" alt=""></a>
                    </div>
                    <div class="pelicula">
                        <a href="#"><img src="https://www.cronista.com/files/image/390/390945/617737dd5a01a.jpg" alt=""></a>
                    </div>
                    <div class="pelicula">
                        <a href="#"><img src="https://www.cronista.com/files/image/390/390945/617737dd5a01a.jpg" alt=""></a>
                    </div>
                    <div class="pelicula">
                        <a href="#"><img src="https://www.cronista.com/files/image/390/390945/617737dd5a01a.jpg" alt=""></a>
                    </div>
                    <div class="pelicula">
                        <a href="#"><img src="https://www.cronista.com/files/image/390/390945/617737dd5a01a.jpg" alt=""></a>
                    </div>
                    <div class="pelicula">
                        <a href="#"><img src="https://www.cronista.com/files/image/390/390945/617737dd5a01a.jpg" alt=""></a>
                    </div>
                    <div class="pelicula">
                        <a href="#"><img src="https://www.cronista.com/files/image/390/390945/617737dd5a01a.jpg" alt=""></a>
                    </div>
                    <div class="pelicula">
                        <a href="#"><img src="https://www.cronista.com/files/image/390/390945/617737dd5a01a.jpg" alt=""></a>
                    </div>
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
.peliculas-recomendadas {
    margin-bottom: 70px;
}

.peliculas-recomendadas .contenedor-principal {
    display: flex;
    align-items: center;
    position: relative;
}

.peliculas-recomendadas .contenedor-principal .flecha-izquierda,
.peliculas-recomendadas .contenedor-principal .flecha-derecha {
    position: absolute;
    border: none;
    background: rgba(0,0,0,0.3);
    font-size: 40px;
    height: 50%;
    top: calc(50% - 25%);
    line-height: 40px;
    width: 50px;
    color: #fff;
    cursor: pointer;
    z-index: 500;
    transition: .2s ease all;
}

.peliculas-recomendadas .contenedor-principal .flecha-izquierda:hover,
.peliculas-recomendadas .contenedor-principal .flecha-derecha:hover {
    background: rgba(0,0,0, .9);
}

.peliculas-recomendadas .contenedor-principal .flecha-izquierda {
    left: 0;
}

.peliculas-recomendadas .contenedor-principal .flecha-derecha {
    right: 0;
}

/*-------------- Carousel -----------------*/
.peliculas-recomendadas .contenedor-carousel {
    width: 100%;
    padding: 20px 0px;
    overflow: hidden;
    scroll-behavior: smooth;
}

.peliculas-recomendadas .contenedor-carousel .carousel {
    display: flex;
    flex-wrap: nowrap;
}

/* Ajusta la cantidad de tarjetas */
.peliculas-recomendadas .contenedor-carousel .carousel .pelicula {
    min-width: 25%;
    transition: .3 ease all;
}

.peliculas-recomendadas .contenedor-carousel .carousel .pelicula img {
    width: 100%;
    vertical-align: top;
}

/*-------------- Media Queries -----------------*/
@media screen and (max-width: 800px) {
    .peliculas-recomendadas .contenedor-carousel {
        overflow: visible;
    }

    .peliculas-recomendadas .contenedor-carousel .carousel {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }

    .peliculas-recomendadas .indicadores,
    .peliculas-recomendadas .flecha-izquierda,
    .peliculas-recomendadas .flecha-derecha {
        display: none;
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
            }
        },
        mounted() {
            const fila = document.querySelector(".contenedor-carousel");
            const peliculas = document.querySelector(".pelicula");

            const flechaIzquierda = document.getElementById("flecha-izquierda");
            const flechaDerecha = document.getElementById("flecha-derecha");

            /*-------------- Event listener para flecha derecha -----------------*/
            flechaDerecha.addEventListener("click", () => {
                fila.scrollLeft += fila.offsetWidth;
            });

            /*-------------- Event listener para flecha izquierda -----------------*/
            flechaIzquierda.addEventListener("click", () => {
            fila.scrollLeft -= fila.offsetWidth;
            });
        },
    }
</script>


