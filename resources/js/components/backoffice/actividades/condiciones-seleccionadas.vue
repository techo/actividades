<template>
    <div class="row">
        <div class="contenedor">
            <chip v-for="(condicion, index) in condiciones"
                  :valor="condicion.campoLabel + ' ' + condicion.condicionLabel + ' ' + condicion.valor"
                  :key="condicion.id"
                  :index="index"
                  class="flex"
            ></chip>
        </div>
    </div>
</template>

<script>

    import chip from '../../plugins/chip'

    export default {
        name: "condiciones-seleccionadas",
        components: { chip },
        data() {
            return {
                condiciones: []
            }
        },
        created() {
            //Eventos
            Event.$on('agregar-condicion', this.agregar);
            Event.$on('removerme', this.removerCondicion);
        },
        methods: {
            agregar: function (condicion) {
                this.condiciones.push(condicion);
            },
            removerCondicion: function (index) {
                let condicion = this.condiciones.splice(index,1);
                condicion.index = index;
                Event.$emit('remover-condicion', condicion);
            }
        }
    }
</script>

<style scoped>
    .contenedor {
        display: flex;
        flex-wrap: wrap;
        padding-left: 5px;
        padding-right: 5px;
    }
</style>