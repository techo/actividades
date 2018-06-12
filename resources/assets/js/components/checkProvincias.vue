<template>
    <div>
        <input type="checkbox" v-model="selectAll">
        <strong>{{ this.datos.provincia }}</strong>

        <div v-for="l in this.datos.localidades">
            <input type="checkbox" :value="l.id_localidad" v-model="selected">
            {{ l.localidad }}
        </div>
        <hr>
    </div>
</template>

<script>
    export default {
        name: "check-provincias",
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
                    return this.datos.localidades ? this.selected.length === this.datos.localidades.length : false;
                },
                set: function (value) {
                    let selected = [];

                    if (value) {
                        this.datos.localidades.forEach(function (localidad) {
                            selected.push(localidad.id_localidad);
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