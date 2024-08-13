<template>
    <div class="carousel-container">
        <div class="carousel">
            <tarjeta
                v-for="act in actividades"
                v-bind:actividad="act"
                v-bind:key="Math.random() + '_' + act.idActividad"
            >
            </tarjeta>
        </div>
    </div>
</template>

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
    }
</script>


<style scoped>
.carousel-container {
overflow-x: scroll;
display: flex;
justify-content: flex-start;
padding: 20px;
scrollbar-width: none; /* Firefox */
}
.carousel-container::-webkit-scrollbar {
display: none; /* Chrome, Safari, and Opera */
}
.carousel {
display: flex;
flex-wrap: nowrap;
}
.carousel-item {
flex: 0 0 auto;
margin-right: 20px; /* Espaciado entre tarjetas */
}
</style>