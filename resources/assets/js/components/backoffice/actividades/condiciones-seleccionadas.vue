<template>
    <div class="list-group">
        <li v-for="condicion in condiciones" :key="condicion.id" class="list-group-item">
            {{ condicion.label }} {{ condicion.condicion }} {{ condicion.valor}}
        </li>
    </div>
</template>

<script>
    export default {
        name: "condiciones-seleccionadas",
        data() {
            return {
                condiciones: []
            }
        },
        created() {
            //Eventos
            Event.$on('agregar-condicion', this.agregar);
        },
        methods: {
            agregar: function (condicion) {
                this.condiciones.push(condicion);
            }
        }
    }
</script>

<style scoped>

</style>