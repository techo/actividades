<template>
    <div>
        <input type="checkbox" v-model="selectAll">
        <strong>Marcar Todos</strong>

        <div v-for="t in this.datos">
            <input type="checkbox" :value="t.idTipo" v-model="selected">
            {{ t.nombre }}
        </div>
        <hr>
    </div>
</template>

<script>
    export default {
        name: "check-tipo-actividades",
        props: ['propdatos'],
        data() {
            return {
                selected: [],
                datos: this.propdatos,
            }
        },
        mounted() {
            this.datos = this.propdatos;

        },
        computed: {
            selectAll: {
                get: function () {
                    return this.datos ? this.selected.length === this.datos.length : false;
                },
                set: function (value) {
                    let selected = [];

                    if (value) {

                        this.datos.forEach(function (tipo) {
                            selected.push(tipo.idTipo);
                        });
                    }
                    this.selected = selected;
                }
            },
        },
    }
</script>

<style scoped>

</style>