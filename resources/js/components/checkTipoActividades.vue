<template>
    <div>
        <input type="checkbox" v-model="selectAll">
        <strong>{{ this.datos.titulo }}</strong>


        <div v-for="t in datos.actividades">
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
            }
        },
        computed: {
            datos: function() {
                return this.propdatos;
            },
            selectAll: {
                get: function () {
                    return this.datos.actividades ? this.selected.length === this.datos.actividades.length : false;
                },
                set: function (value) {
                    let selected = [];

                    if (value) {
                        this.datos.actividades.forEach(function (tipo) {
                            selected.push(tipo.idTipo);
                        });
                    }

                    this.selected = selected;
                }
            }
        }
    }
</script>

<style scoped>

</style>